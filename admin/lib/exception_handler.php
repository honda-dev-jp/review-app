<?php

declare(strict_types=1);

require_once __DIR__ . '/../guards/redirect_guard.php';

/**
 * 管理画面：グローバル例外ハンドラ
 *
 * 責務：
 * - set_exception_handler() に登録するグローバルハンドラ（handle_exception）
 * - POST処理の catch ブロックから呼び出す管理者用ハンドラ（handleAdminError）
 *
 * 設計方針：
 * - rollBack() はここでは行わない（呼び出し元の責務）
 * - ユーザーには詳細を見せず、ログに詳細を残す
 * - ログには getMessage() を含む詳細を出力する（原因調査のため）
 * - ただしパスワード・個人情報・CSRFトークンは意図的にログに渡さない
 * - PDOException のメッセージはテーブル名・DB名が含まれるため固定文言に差し替える
 * - RuntimeException など開発者が書いたメッセージは安全なためそのまま出力する
 */

/**
 * 例外の種類に応じてログに出すメッセージを返す
 *
 * PDOException のメッセージにはテーブル名・DB名・SQL情報が含まれることがあるため、
 * 固定文言に差し替える。それ以外は開発者が書いたメッセージなのでそのまま使用する。
 *
 * @param \Throwable $e
 * @return string ログ出力用メッセージ
 */
function resolveLogMessage(\Throwable $e): string
{
    if ($e instanceof \PDOException) {
        return 'データベースエラーが発生しました（詳細は非表示）';
    }

    return $e->getMessage();
}

/**
 * グローバル例外ハンドラ（bootstrap.php の set_exception_handler に登録）
 *
 * GET表示系を含む、Controller で catch されなかった例外の最後の受け皿。
 * 500エラー画面を表示して処理を終了する。
 *
 * @param \Throwable $e キャッチされなかった例外
 * @return never
 */
function handle_exception(\Throwable $e): never
{
    error_log(sprintf(
        '[Uncaught %s] %s in %s:%d',
        get_class($e),
        resolveLogMessage($e),
        $e->getFile(),
        $e->getLine(),
    ));

    http_response_code(500);

    require __DIR__ . '/../view/error/500.php';

    exit;
}

/**
 * 管理画面コントローラー用 例外ハンドラ
 *
 * POST処理（add/edit/delete）の catch ブロックから呼び出す共通ヘルパー。
 * 「誰が・何のIDで・何が起きたか」をログに残し、管理画面へリダイレクトする。
 *
 * 【呼び出し元での rollBack() について】
 * トランザクション中の場合は、この関数を呼ぶ前に呼び出し元で rollBack() を実行すること。
 *
 * 【$contextId の意味】
 * 操作対象のレコードID（item_id など）を渡す。
 * 新規追加など ID が未発行の場合は 0 を渡す。
 *
 * @param \Throwable $e        発生した例外
 * @param int        $userId    操作を行った管理者の user_id
 * @param int        $contextId 操作対象のレコードID（未採番の場合は 0）
 * @param string     $redirectTo リダイレクト先（例：'/admin/item_list.php'）
 * @return never
 */
function handleAdminError(
    \Throwable $e,
    int $userId,
    int $contextId,
    string $redirectTo,
): never {
    // contextId が 0 の場合は「未採番」と出力する
    $contextLabel = ($contextId === 0) ? '未採番' : (string) $contextId;

    error_log(sprintf(
        '[Admin %s] %s in %s:%d | user_id=%d | item_id=%s',
        get_class($e),
        resolveLogMessage($e),
        $e->getFile(),
        $e->getLine(),
        $userId,
        $contextLabel,
    ));

    redirectWithError('操作に失敗しました。', $redirectTo);
}
