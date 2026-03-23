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
 *
 * 設計方針：
 * - インスタンスメソッドのみ使用（static禁止）
 * - View には変数のみ渡す（DBアクセス禁止）
 * - 想定外例外（PDOException / Throwable）は bootstrap.php の
 *   グローバルハンドラ（set_exception_handler）に委譲する
 * - Controller では try/catch を書かない（GET表示系）
 * - POST処理（add/edit/delete）では想定内エラーのみ Controller で扱う
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

        // GETパラメータで指定されたpageが総ページ数を超えた場合、最終ページに補正する
        $page = min($page, $totalPages);

        // 取得開始位置を計算
        $offset = ($page - 1) * $perPage;

        // カレントページの作品一覧を取得
        $items = $this->model->getAll($perPage, $offset);

        // View 固有JS（scripts.php で読み込む）
        $pageScripts = ['item_list.js', 'item_delete.js'];

        // View を呼び出す（$items, $page, $totalPages を渡す）
        require __DIR__ . '/../view/item/list.php';
    }

    /**
     * 作品削除処理
     *
     * 処理の流れ：
     * 1. POSTチェック・CSRFチェック・item_idバリデーション
     * 2. findById() で作品情報取得（画像ファイル名を確保）
     * 3. トランザクション開始
     * 4. DB削除
     * 5. commit()
     * 6. DB削除成功後に画像ファイル削除（失敗はログのみ）
     * 7. 成功リダイレクト
     *
     * 【注意】
     * - redirectWithSuccess() / redirectWithError() は内部でexitするため
     *   commit() と画像削除は必ずリダイレクトより前に実行すること
     * - ファイル削除はトランザクションで巻き戻せないため、DB削除成功後に実行する
     * - rollBack() は handleAdminError() より前に実行すること
     *
     * @return void
     */
    public function delete(): void
    {
        $messages = require __DIR__ . '/../lang/messages.php';

        // catch内で参照するため事前に初期化
        $pdo    = null;
        $itemId = 0;

        try {
            // POST確認（失敗時は内部でリダイレクト・exit）
            requirePost();

            // CSRF確認
            if (!validateCSRFTokenOnce()) {
                redirectWithError($messages['common']['csrf_error'], '/admin/item_list.php');
            }

            // item_id バリデーション
            $itemId = (int) ($_POST['item_id'] ?? 0);
            if ($itemId <= 0) {
                redirectWithError($messages['common']['invalid_value'], '/admin/item_list.php');
            }

            // 作品情報取得（画像ファイル名を確保）
            $item = $this->model->findById($itemId);
            if ($item === null) {
                redirectWithError($messages['item']['not_found'], '/admin/item_list.php');
            }
            $imageName = $item['image'];

            // PDO取得・トランザクション開始
            $pdo = $this->model->getPdo();
            $pdo->beginTransaction();

            // DB削除
            $deleted = $this->model->deleteById($itemId);

            if (!$deleted) {
                $pdo->rollBack();
                redirectWithError($messages['item']['delete_failed'], '/admin/item_list.php');
            }

            // DB削除成功 → commit
            $pdo->commit();

            // commit後にサムネイル画像を削除
            // getBaseUrl() はURL用のためunlink()には使えない → サーバーパスを使う
            // __DIR__ = admin/controller/ → ../../ でプロジェクトルートへ
            $protectedImages = ['no_image.png'];
            if ($imageName !== null && !in_array($imageName, $protectedImages, true)) {
                $imagePath = dirname(dirname(__DIR__)) . '/images/thumbnail/' . basename($imageName);
                if (is_file($imagePath)) {
                    if (!@unlink($imagePath)) {
                        error_log('[Admin] 画像削除失敗: ' . $imagePath);
                    }
                }
            }

            // 成功リダイレクト（exit）
            redirectWithSuccess($messages['item']['delete_success'], '/admin/item_list.php');

        } catch (\PDOException $e) {
            // rollBack() を handleAdminError() より前に実行すること
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
}
