<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/guards/redirect_guard.php';

/**
 * 一般公開側のログ種別を、安全な固定文言へ変換する。
 */
function resolvePublicLogMessage(Throwable $e): string
{
    if ($e instanceof PDOException) {
        return 'database_error';
    }

    if ($e instanceof RuntimeException) {
        return 'application_error';
    }

    return 'unexpected_error';
}

/**
 * 公開ログ用イベントIDを生成する。
 */
function generatePublicErrorEventId(): string
{
    try {
        return bin2hex(random_bytes(8));
    } catch (Throwable $e) {
        return str_replace('.', '', uniqid('fallback_', true));
    }
}

/**
 * ログ1行に収める値から改行などを除去する。
 */
function normalizePublicLogValue(string $value): string
{
    return preg_replace('/[\r\n\t]+/', '_', $value) ?? 'unknown';
}

/**
 * 一般公開側の例外ログを、本番向けの最小情報で出力する。
 */
function writePublicErrorLog(Throwable $e, string $context = 'public'): void
{
    try {
        $eventId = generatePublicErrorEventId();
        $method = normalizePublicLogValue((string) ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: 'unknown';
        $path = normalizePublicLogValue($path);

        error_log(sprintf(
            '[PublicError] event_id=%s context=%s type=%s exception=%s method=%s path=%s',
            $eventId,
            normalizePublicLogValue($context),
            resolvePublicLogMessage($e),
            normalizePublicLogValue(get_class($e)),
            $method,
            $path,
        ));
    } catch (Throwable $loggingError) {
        // ログ出力失敗で本来のエラー処理を壊さない。
    }
}

/**
 * DB処理中に発生した例外を共通的に処理するハンドラ。
 *
 * - 例外内容を PHP の error_log に記録する（開発者向け）
 * - ユーザー向けの統一エラーメッセージを $_SESSION['error'] に追加する
 * - 指定URLへリダイレクトし、処理を終了する
 *
 * 注意：
 * - トランザクションの rollBack() は本関数では行わない。
 *   必要な場合は呼び出し側の catch 内で実施すること。
 * - 本関数はリダイレクトして exit するため、呼び出し元に制御は戻らない。
 *
 * @param Throwable $e 発生した例外（PDOException / Error 等を含む）
 * @param string $redirectTo リダイレクト先（アプリルート基準のパス推奨）
 * @param string|null $userMessage ユーザー表示用メッセージ（nullなら既定文言）
 * @return never
 */
function handleDbError(Throwable $e, string $redirectTo = '/index.php', ?string $userMessage = null): never
{
    writePublicErrorLog($e, 'handleDbError');

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $message = $userMessage ?? 'システムエラーが発生しました。しばらくしてから再度お試しください。';

    // redirect_guard 側で $_SESSION['error'][] に積んで exit まで完結させる
    redirectWithError($message, $redirectTo);
}
