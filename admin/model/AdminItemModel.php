<?php

declare(strict_types=1);

namespace Admin\Models;

use PDO;

/**
 * 作品（items）に関するデータアクセスを担当するモデル。
 *
 * 設計方針：
 * - コンストラクタインジェクションでPDOを受け取る
 * - インスタンスメソッドで統一（static は使わない）
 * - DB操作のみを責務とし、セッション・リダイレクト等を持たない
 * - 例外は呼び出し元（Controller）に委譲する
 *
 * @package Admin\Models
 */
class AdminItemModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    // =========================================================
    // 件数取得
    // =========================================================

    /**
     * 作品の総件数を返す。
     *
     * ページネーションの totalPages 計算に使用する。
     *
     * @return int
     */
    public function countAll(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM items');
        return (int) $stmt->fetchColumn();
    }

    // =========================================================
    // 一覧取得
    // =========================================================

    /**
     * 作品一覧をページネーション付きで取得する
     *
     * - reviews テーブルと LEFT JOIN して avg_rating / rating_count を付与
     * - デフォルトは item_id DESC (追加日が新しい順)
     *   ＊ユーザー側（avg_rating 順）とは意図的に分けている
     *
     * @param int $limit  1ページあたりの件数
     * @param int $offset 取得開始位置
     * @return array<int, array{
     * 	item_id: int,
     * 	title: string,
     * 	image: string|null,
     *  description: string|null,
     *  avg_rating: float|null,
     * 	rating_count: int
     * }>
     */
    public function getAll(int $limit, int $offset): array
    {
        $sql = '
            SELECT
                i.item_id,
                i.title,
                i.image,
                i.description,
                AVG(r.rating)	AS avg_rating,
                COUNT(r.rating)	AS rating_count
            FROM items i
            LEFT JOIN reviews r ON i.item_id = r.item_id
            GROUP BY i.item_id
            ORDER BY i.item_id DESC
            LIMIT :limit OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 型を明示的に正規化して渡す
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'item_id'       => (int) $row['item_id'],
                'title'         => (string) $row['title'],
                'image'         => isset($row['image']) ? (string) $row['image'] : null,
                'description'   => isset($row['description']) ? (string) $row['description'] : null,
                'avg_rating'    => $row['avg_rating'] !== null ? (float) $row['avg_rating'] : null,
                'rating_count'  => (int) $row['rating_count'],
            ];
        }

        return $result;
    }

    // =========================================================
    // 単件取得
    // =========================================================

    /**
     * item_id を指定して作品を1件取得する。
     *
     * 存在しない場合は null を返す（例外は投げない）。
     * 呼び出し元で null チェックを行う。
     *
     * @param int $itemId
     * @return array{
     * 		item_id: int,
     * 		title: string,
     * 		description: string|null,
     * 		image: string|null
     * }|null
     */
    public function findById(int $itemId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT item_id, title, description, image
            FROM items
            WHERE item_id = :item_id
        ');
        $stmt->bindValue(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'item_id'		=> (int) $row['item_id'],
            'title'			=> (string) $row['title'],
            'description'	=> isset($row['description']) ? (string) $row['description'] : null,
            'image'			=> isset($row['image']) ? (string) $row['image'] : null,
        ];
    }
}
