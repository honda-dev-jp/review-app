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
    // PDO取得
    // =========================================================

    /**
     * PDOインスタンスを返す。
     *
     * Controller がトランザクション制御を行う際に使用する。
     * DB操作自体はModelのメソッドを通じて行うこと。
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
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

    // =========================================================
    // 削除
    // =========================================================

    /**
     * item_id を指定して作品を1件削除する。
     *
     * - 削除に成功した場合（1件削除）は true を返す
     * - 対象が存在しない場合（0件削除）は false を返す
     * - 例外は呼び出し元（Controller）に委譲する
     *
     * 【呼び出し元での判定例】
     * if ($this->model->deleteById($itemId)) {
     *     // 成功処理
     * } else {
     *     // 失敗処理（対象が見つからなかった等）
     * }
     *
     * @param int $itemId 削除対象の作品ID
     * @return bool 1件削除できた場合 true、それ以外は false
     */
    public function deleteById(int $itemId): bool
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM items WHERE item_id = :item_id 
        ');
        $stmt->bindValue(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();

        // rowCount() で実際に削除された件数を確認する
        // 1件削除できていれば true、0件（対象なし）なら false
        return $stmt->rowCount() === 1;
    }

    // =========================================================
    // 新規作品登録
    // =========================================================
    /**
     * 作品を新規登録する。
     *
     * - 登録に成功した場合は新規登録された作品の item_id を返す
     * - 例外は呼び出し元（Controller）に委譲する
     *
     * @param string $title       作品タイトル
     * @param string|null $description 作品説明（任意）
     * @param string|null $image       画像ファイルパス（任意）
     * @return int 登録された作品の item_id
     */
    public function insert(string $title, ?string $description, ?string $image): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO items (title, description, image, category_id)
            VALUES (:title, :description, :image, :category_id)
        ');
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':image', $image, $image === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':category_id', 1, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    // =========================================================
    // 作品情報更新
    // =========================================================
    /**
     * 作品情報を更新する。
     *
     * - 更新に成功した場合（1件更新）は true を返す
     * - 対象が存在しない場合（0件更新）は false を返す
     * - 例外は呼び出し元（Controller）に委譲する
     *
     * @param int $itemId              作品ID
     * @param string $title            作品タイトル
     * @param string|null $description 作品説明（任意）
     * @param string|null $image       画像ファイルパス（任意）
     * @return bool 1件更新できた場合 true、それ以外は false
     */
    public function update(int $itemId, string $title, ?string $description, ?string $image): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE items
            SET title = :title,
                description = :description,
                image = :image
            WHERE item_id = :item_id
        ');
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, $description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':image', $image, $image === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() === 1;
    }
}
