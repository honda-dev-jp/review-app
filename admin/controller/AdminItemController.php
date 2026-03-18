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

        // View を呼び出す（$items, $page, $totalPages を渡す）
        require __DIR__ . '/../view/item/list.php';
    }
}
