<?php

declare(strict_types=1);

namespace Admin\Controller;

use Admin\Models\AdminItemModel;

/**
 * 管理画面：作品管理コントローラー
 *
 * 責務：
 * - 作品一覧のページネーション計算
 * - Model からのデータ取得と View への受け渡し
 * - 作品の追加・削除処理
 *
 * 設計方針：
 * - インスタンスメソッドのみ使用（static禁止）
 * - View には変数のみ渡す（DBアクセス禁止）
 * - 想定外例外（PDOException / Throwable）は bootstrap.php の
 *   グローバルハンドラ（set_exception_handler）に委譲する
 * - Controller では try/catch を書かない（GET表示系）
 * - POST処理（add/edit/delete）では try/catch を書く
 */
class AdminItemController
{
    /** @var AdminItemModel 作品モデルのインスタンス */
    private AdminItemModel $model;

    /**
     * コンストラクタ
     *
     * @param AdminItemModel $model 作品モデル（依存注入）
     */
    public function __construct(AdminItemModel $model)
    {
        $this->model = $model;
    }

    /**
     * 作品一覧画面を表示する
     *
     * - GETパラメータ `page` からページ番号を取得・補正
     * - Model から総件数と一覧データを取得
     * - View（view/item/list.php）に変数を渡して描画
     * - 例外は握りつぶさず bootstrap.php のグローバルハンドラへ委譲
     *
     * @return void
     */
    public function index(): void
    {
        // 現在のページ番号を取得（未指定・1未満は1に補正）
        $page = max(1, (int) ($_GET['page'] ?? 1));

        // 1ページに表示する件数
        $perPage = 10;

        // 作品の総件数を取得
        $total = $this->model->countAll();

        // 総ページ数を計算（最低1ページは確保）
        $totalPages = max(1, (int) ceil($total / $perPage));

        // 指定ページが総ページ数を超えた場合は最終ページに補正
        $page = min($page, $totalPages);

        // 取得開始位置を計算
        $offset = ($page - 1) * $perPage;

        // カレントページの作品一覧を取得
        $items = $this->model->getAll($perPage, $offset);

        // このページ固有のJSファイル（scripts.php で読み込む）
        $pageScripts = ['item_list.js', 'item_delete.js'];

        // View を描画（$items, $page, $totalPages, $pageScripts を渡す）
        require __DIR__ . '/../view/item/list.php';
    }

    /**
     * 作品削除処理
     *
     * 処理の流れ：
     * 1. POSTチェック・CSRFチェック・item_idバリデーション
     * 2. findById() で作品情報取得（削除前に画像ファイル名を確保）
     * 3. トランザクション開始
     * 4. DB削除
     * 5. commit()
     * 6. DB削除成功後に画像ファイルを削除（失敗はログのみ・孤児ファイルは後で掃除）
     * 7. 成功リダイレクト
     *
     * 【設計上の注意点】
     * - redirectWithSuccess() / redirectWithError() は内部で exit するため
     *   commit() と画像削除は必ずリダイレクトより前に実行すること
     * - ファイル削除はトランザクションで巻き戻せないため DB 削除成功後に実行する
     * - rollBack() は handleAdminError() より前に実行すること（handleAdminError が exit するため）
     *
     * @return void
     */
    public function delete(): void
    {
        $messages = require __DIR__ . '/../lang/messages.php';

        // catch 内で参照するため try の外で初期化する
        $pdo    = null;
        $itemId = 0;

        try {
            // POST 以外のリクエストを弾く
            requirePost();

            // CSRFトークンを検証（成功時にワンタイム消費）
            if (!validateCSRFTokenOnce()) {
                redirectWithError($messages['common']['csrf_error'], '/admin/item_list.php');
            }

            // item_id を取得・正の整数かチェック
            $itemId = (int) ($_POST['item_id'] ?? 0);
            if ($itemId <= 0) {
                redirectWithError($messages['common']['invalid_value'], '/admin/item_list.php');
            }

            // DB から作品情報を取得（削除前に画像ファイル名を確保するため）
            $item = $this->model->findById($itemId);
            if ($item === null) {
                redirectWithError($messages['item']['not_found'], '/admin/item_list.php');
            }
            $imageName = $item['image'];

            // トランザクション開始・DB削除
            $pdo = $this->model->getPdo();
            $pdo->beginTransaction();

            $deleted = $this->model->deleteById($itemId);

            if (!$deleted) {
                // 削除対象が見つからなかった場合（状態不整合）
                $pdo->rollBack();
                redirectWithError($messages['item']['delete_failed'], '/admin/item_list.php');
            }

            // DB削除成功 → コミット確定
            $pdo->commit();

            // コミット後に画像ファイルを削除する
            // ※ unlink() は DB トランザクションで巻き戻せないため、必ず commit 後に実行する
            // ※ getBaseUrl() はURL用なので unlink() には使えない → サーバーパスで指定する
            // ※ __DIR__ = admin/controller/ → dirname×2 でプロジェクトルートへ
            $protectedImages = ['no_image.png'];
            if ($imageName !== null && !in_array($imageName, $protectedImages, true)) {
                $imagePath = dirname(dirname(__DIR__)) . '/images/thumbnail/' . basename($imageName);
                if (is_file($imagePath)) {
                    if (!@unlink($imagePath)) {
                        // 削除失敗しても処理は続行（孤児ファイルは後で手動掃除）
                        error_log('[Admin] 画像削除失敗: ' . $imagePath);
                    }
                }
            }

            // 成功メッセージを表示して一覧へ戻す（内部で exit）
            redirectWithSuccess($messages['item']['delete_success'], '/admin/item_list.php');

        } catch (\PDOException $e) {
            // DB例外：トランザクション中なら rollBack してからエラーハンドラへ
            // ※ rollBack() を先に実行しないと handleAdminError の exit 後に実行されない
            if (isset($pdo) && $pdo instanceof \PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            handleAdminError(
                $e,
                (int) ($_SESSION['user_id'] ?? 0),
                $itemId,
                '/admin/item_list.php',
            );
        } catch (\Throwable $e) {
            // その他の例外（プログラムエラー等）
            if (isset($pdo) && $pdo instanceof \PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            handleAdminError(
                $e,
                (int) ($_SESSION['user_id'] ?? 0),
                $itemId,
                '/admin/item_list.php',
            );
        }
    }

    /**
     * 作品追加画面を表示する / 追加処理を行う
     *
     * GET：追加フォームを表示する
     *   - バリデーションエラー時はセッションから前回入力値とエラーを取り出して再表示
     *
     * POST：入力値を受け取り、バリデーション・画像保存・DB登録を行う
     *   処理の流れ：
     *   1. CSRFチェック
     *   2. バリデーション（エラー時は入力値を保持して追加画面へ戻す）
     *   3. 入力値を整形
     *   4. 画像が選択されていれば保存（未選択なら null のまま）
     *   5. DB登録
     *   6. 成功時は一覧へリダイレクト
     *
     * 【設計上の注意点】
     * - DB登録失敗時は保存済みの画像を削除して孤児ファイルを防ぐ
     * - 追加処理では item_id がまだ存在しないため handleAdminError の第3引数は 0 固定
     *
     * @return void
     */
    public function add(): void
    {
        $messages = require __DIR__ . '/../lang/messages.php';

        // catch 内で参照するため try の外で初期化する
        // 追加処理では item_id がまだ存在しないため 0 固定
        $itemId    = 0;
        $imageName = null;

        // -------------------------
        // GET: 追加画面を表示
        // -------------------------
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // バリデーションエラー時にセッションへ保存した値を取り出す
            // （初回表示時はどちらも空配列になる）
            $errors = $_SESSION['errors'] ?? [];
            $old    = $_SESSION['old'] ?? [];

            // 取り出したら即座に削除（リロード時の二重表示防止）
            unset($_SESSION['errors'], $_SESSION['old']);

            // 追加フォームを描画（$errors, $old を View へ渡す）
            require __DIR__ . '/../view/item/add.php';
            return;
        }

        // -------------------------
        // POST: 追加処理
        // -------------------------
        try {
            // CSRFトークンを検証（成功時にワンタイム消費）
            if (!validateCSRFTokenOnce()) {
                redirectWithError($messages['common']['csrf_error'], '/admin/item_add.php');
            }

            // フォームの入力値を取得
            $post  = $_POST;
            $files = $_FILES;

            // バリデーションを実行（エラーメッセージの配列が返る）
            require_once __DIR__ . '/../validator/AdminItemValidator.php';
            $errors = validateAdminItem($post, $files);

            // バリデーションエラーがある場合は入力値を保持して追加画面へ戻す
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old']    = $post;
                redirectWithError($messages['common']['invalid_value'], '/admin/item_add.php');
            }

            // タイトルの前後空白を除去
            $title = trim((string) ($post['title'] ?? ''));

            // 説明文：空文字の場合は null として DB に保存する（任意項目）
            $description = trim((string) ($post['description'] ?? ''));
            $description = $description === '' ? null : $description;

            // 画像：未選択なら null のまま DB に保存する（任意項目）
            // 選択されている場合のみ保存処理を行う
            if (
                isset($files['image']['error'])
                && $files['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $image = $files['image'];

                // MIMEタイプで画像の種別を判定する（拡張子は偽装できるため）
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($image['tmp_name']);

                // 許可するMIMEタイプと対応する拡張子の対応表
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/webp' => 'webp',
                ];

                // ランダムなファイル名を生成（予測不可能にするため）
                $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

                // 保存先ディレクトリのサーバーパスを組み立てる
                // __DIR__ = admin/controller/ → dirname×2 でプロジェクトルートへ
                $dir  = dirname(dirname(__DIR__)) . '/images/thumbnail';
                $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $filename;

                // 保存先ディレクトリの存在・書き込み可能チェック
                if (!is_dir($dir)) {
                    redirectWithError($messages['image']['dir_not_found'], '/admin/item_add.php');
                }
                if (!is_writable($dir)) {
                    redirectWithError($messages['image']['dir_not_writable'], '/admin/item_add.php');
                }

                // 一時ファイルを保存先へ移動する
                // 失敗した場合は DB 登録せずエラーで戻す（孤児ファイルも発生しない）
                if (!move_uploaded_file($image['tmp_name'], $path)) {
                    redirectWithError($messages['image']['upload_failed'], '/admin/item_add.php');
                }

                // 保存成功時のみ DB に登録するファイル名をセットする
                $imageName = $filename;
            }

            // DB に作品を登録する
            $this->model->insert($title, $description, $imageName);

            // 登録成功後は一覧へ戻す（内部で exit）
            redirectWithSuccess($messages['item']['create_success'], '/admin/item_list.php');

        } catch (\PDOException $e) {
            // DB登録失敗時：保存済み画像が残らないよう削除する（孤児ファイル防止）
            if ($imageName !== null) {
                $orphanPath = dirname(dirname(__DIR__)) . '/images/thumbnail/' . basename($imageName);
                if (is_file($orphanPath)) {
                    @unlink($orphanPath);
                }
            }
            handleAdminError(
                $e,
                (int) ($_SESSION['user_id'] ?? 0),
                $itemId,
                '/admin/item_add.php',
            );
        } catch (\Throwable $e) {
            // その他の例外でも同様に孤児ファイルを削除する
            if ($imageName !== null) {
                $orphanPath = dirname(dirname(__DIR__)) . '/images/thumbnail/' . basename($imageName);
                if (is_file($orphanPath)) {
                    @unlink($orphanPath);
                }
            }
            handleAdminError(
                $e,
                (int) ($_SESSION['user_id'] ?? 0),
                $itemId,
                '/admin/item_add.php',
            );
        }
    }

    /**
     * 作品編集画面を表示する / 更新処理を行う
     *
     * GET：編集フォームを表示する
     *   1. item_id を GETパラメータから取得・正の整数化チェック
     *   2. findById() で作品情報を取得（nullなら not_found エラー）
     *   3. セッションから $errors / $old を取り出す
     *   4. view/item/edit.php
     *
     * POST：入力値を受け取り、バリデーション・画像差し替え・DB更新を行う
     *   処理の流れ：
     *   1. CSRFチェック
     *   2. item_id・入力値のバリデーション
     *   3. findById() で既存の画像ファイル名を取得（差し替え時の旧画像削除用）
     *   4. 画像が選択されていれば保存
     *   5. DB更新（update()）
     *   6. DB更新成功後に旧画像を削除
     *   7. 成功リダイレクト → 一覧へ
     *
     * 【設計上の注意点】
     * - 旧画像の削除は DB更新成功後に実行すること（失敗時に画像だけ消えるのを防ぐ）
     * - DB更新失敗時は新しく保存した画像を削除して孤児ファイルを防ぐ
     * - 追加処理と異なり item_id が存在するため handleAdminError の第3引数に渡す
     *
     * @return void
     */
    public function edit(): void
    {
        $messages = require __DIR__ . '/../lang/messages.php';

        // catch 内で参照するため try の外で初期化する
        $itemId    = 0;
        $imageName = null;

        // -------------------------
        // GET: 編集画面を表示
        // -------------------------
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // バリデーションエラー時にセッションへ保存した値を取り出す
            // （初回表示時はどちらも空配列になる）
            $errors = $_SESSION['errors'] ?? [];
            $old    = $_SESSION['old'] ?? [];

            // 取り出したら即座に削除（リロード時の二重表示防止）
            unset($_SESSION['errors'], $_SESSION['old']);

            // GETから item_id を取得しバリデーション
            $itemId = (int) ($_GET['item_id'] ?? 0);
            if ($itemId <= 0) {
                redirectWithError($messages['common']['invalid_value'], '/admin/item_list.php');
            }

            // item_id 取得後に作品情報を取得する
            $item = $this->model->findById($itemId);
            if ($item === null) {
                redirectWithError($messages['item']['not_found'], '/admin/item_list.php');
            }

            // 編集フォームを描画（$item, $errors, $old を View へ渡す）
            require __DIR__ . '/../view/item/edit.php';
            return;
        }

        // -------------------------
        // POST: 更新処理
        // -------------------------
        try {
            // CSRFトークンを検証（成功時にワンタイム消費）
            if (!validateCSRFTokenOnce()) {
                redirectWithError($messages['common']['csrf_error'], '/admin/item_edit.php');
            }

            // item_idのバリデーション
            $itemId = (int) ($_POST['item_id'] ?? 0);
            if ($itemId <= 0) {
                redirectWithError($messages['common']['invalid_value'], '/admin/item_list.php');
            }

            // 旧画像の取得
            $currentItem = $this->model->findById($itemId);
            $oldImageName = $currentItem['image'] ?? null;

            // フォームの入力値を取得
            $post  = $_POST;
            $files = $_FILES;

            // バリデーションを実行（エラーメッセージの配列が返る）
            require_once __DIR__ . '/../validator/AdminItemValidator.php';
            $errors = validateAdminItem($post, $files);

            // バリデーションエラーがある場合は入力値を保持して編集画面へ戻す
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                $_SESSION['old']    = $post;
                redirectWithError($messages['common']['invalid_value'], '/admin/item_edit.php?item_id=' . $itemId);
            }

            // タイトルの前後空白を除去
            $title = trim((string) ($post['title'] ?? ''));

            // 説明文：空文字の場合は null として DB に保存する（任意項目）
            $description = trim((string) ($post['description'] ?? ''));
            $description = $description === '' ? null : $description;

            // 画像：未選択なら既存画像を維持する。選択されている場合のみ保存処理を行う
            if (
                isset($files['image']['error'])
                && $files['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $image = $files['image'];

                // MIMEタイプで画像の種別を判定する（拡張子は偽装できるため）
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($image['tmp_name']);

                // 許可するMIMEタイプと対応する拡張子の対応表
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/webp' => 'webp',
                ];

                // ランダムなファイル名を生成（予測不可能にするため）
                $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

                // 保存先ディレクトリのサーバーパスを組み立てる
                // __DIR__ = admin/controller/ → dirname×2 でプロジェクトルートへ
                $dir  = dirname(dirname(__DIR__)) . '/images/thumbnail';
                $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $filename;

                // 保存先ディレクトリの存在・書き込み可能チェック
                if (!is_dir($dir)) {
                    redirectWithError($messages['image']['dir_not_found'], '/admin/item_edit.php');
                }
                if (!is_writable($dir)) {
                    redirectWithError($messages['image']['dir_not_writable'], '/admin/item_edit.php');
                }

                // 一時ファイルを保存先へ移動する
                // 失敗した場合は DB 登録せずエラーで戻す（孤児ファイルも発生しない）
                if (!move_uploaded_file($image['tmp_name'], $path)) {
                    redirectWithError($messages['image']['upload_failed'], '/admin/item_edit.php');
                }

                // 保存成功時のみ DB に登録するファイル名をセットする
                $imageName = $filename;
            }

            // DB に作品を更新する（未選択時は既存画像を維持する）
            $imageToSave = $imageName ?? $oldImageName;
            $this->model->update($itemId, $title, $description, $imageToSave);

            // 旧画像の削除
            $protectedImages = ['no_image.png'];
            if ($oldImageName !== null && $imageName !== null && !in_array($oldImageName, $protectedImages, true)) {
                $oldPath = dirname(dirname(__DIR__)) . '/images/thumbnail/' . basename($oldImageName);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // 更新成功後は一覧へ戻す（内部で exit）
            redirectWithSuccess($messages['item']['update_success'], '/admin/item_list.php');

        } catch (\PDOException $e) {
            // DB更新失敗時：保存済み画像が残らないよう削除する（孤児ファイル防止）
            if ($imageName !== null) {
                $orphanPath = dirname(dirname(__DIR__)) . '/images/thumbnail/' . basename($imageName);
                if (is_file($orphanPath)) {
                    @unlink($orphanPath);
                }
            }
            handleAdminError(
                $e,
                (int) ($_SESSION['user_id'] ?? 0),
                $itemId,
                '/admin/item_edit.php',
            );
        } catch (\Throwable $e) {
            // その他の例外でも同様に孤児ファイルを削除する
            if ($imageName !== null) {
                $orphanPath = dirname(dirname(__DIR__)) . '/images/thumbnail/' . basename($imageName);
                if (is_file($orphanPath)) {
                    @unlink($orphanPath);
                }
            }
            handleAdminError(
                $e,
                (int) ($_SESSION['user_id'] ?? 0),
                $itemId,
                '/admin/item_edit.php',
            );
        }
    }
}
