<?php

declare(strict_types=1);

namespace Admin\Controller;

use AdminItemModel;

require_once __DIR__ . '/../lib/exception_handler.php';

/**
 * 管理画面：作品管理コントローラー
 *
 * 責務：
 * - 作品一覧のページネーション計算
 * - Model からのデータ取得と View への受け渡し
 * - DB例外の補足と exception_handler への委譲
 *
 * 設計方針：
 * - インスタンスメソッドのみ使用（static禁止）
 * - View には変数のみ渡す（DBアクセス禁止）
 * - PDOException は Controller で catch → handleDbError() に委譲
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
     *
     * @return void
     */
    public function index(): void
    {
        // 現在のページ番号を取得（未指定・1未満は1に補正）
        $page = max(1, (int)($_GET['page'] ?? 1));

        // 1ページに表示する件数
        $perPage = 10;

        // 取得開始位置を計算
        $offset = ($page - 1) * $perPage;

        try {
            // 作品の総件数を取得
            $total = $this->model->countAll();

            // 総ページ数を計算（最低1ページは確保）
            $totalPages = max(1, (int)ceil($total / $perPage));

            // カレントページの作品一覧を取得
            $items = $this->model->getAll($perPage, $offset);

        } catch (\PDOException $e) {
            // DBエラー発生時は共通ハンドラに委譲してリダイレクト
            handleDbError($e, '/admin/item_list.php');
        }

        // View を呼び出す（$items, $page, $totalPages を渡す）
        require __DIR__ . '/../view/item/list.php';
    }
}
