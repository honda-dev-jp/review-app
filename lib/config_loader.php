<?php

declare(strict_types=1);

/**
 * CLI実行かどうかを判定する。
 */
function isPrivateConfigCli(): bool
{
    return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
}

/**
 * 設定読み込み失敗を、画面に詳細を出さない形で処理する。
 *
 * @return never
 */
function failPrivateConfigLoad(string $filename, string $reason, ?Throwable $previous = null): never
{
    $safeFilename = basename($filename);
    $exceptionClass = $previous !== null ? get_class($previous) : 'none';

    error_log(sprintf(
        '[ConfigLoadError] reason=%s file=%s exception=%s',
        $reason,
        $safeFilename,
        $exceptionClass,
    ));

    $message = sprintf('設定ファイルの読み込みに失敗しました: file=%s reason=%s', $safeFilename, $reason);

    if (isPrivateConfigCli()) {
        throw new RuntimeException($message);
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    echo 'システムエラーが発生しました。しばらくしてから再度お試しください。';
    exit;
}

/**
 * Web公開ディレクトリ外に配置した設定ファイルを読み込む。
 *
 * @param string $filename 読み込む設定ファイル名
 * @return array<string, mixed>
 * @throws RuntimeException
 */
function loadPrivateConfig(string $filename): array
{
    if ($filename !== basename($filename)) {
        failPrivateConfigLoad($filename, 'invalid_filename');
    }

    if (!in_array($filename, ['env.php', 'database.php'], true)) {
        failPrivateConfigLoad($filename, 'disallowed_filename');
    }

    $candidates = [];

    // 任意指定用。必要になった場合のみサーバー側で環境変数として指定する。
    $envName = match ($filename) {
        'env.php' => 'REVIEW_APP_ENV_CONFIG',
        'database.php' => 'REVIEW_APP_DATABASE_CONFIG',
        default => null,
    };

    if ($envName !== null) {
        $envPath = getenv($envName);
        if ($envPath !== false && $envPath !== '') {
            $candidates[] = $envPath;
        }
    }

    /*
     * Docker LAMP想定:
     * /var/www/
     * ├── html/              ← アプリ本体
     * └── private_config/portfolio/    ← 設定ファイル
     */
    $candidates[] = dirname(__DIR__, 2) . '/private_config/portfolio/' . $filename;

    /*
     * XServer想定:
     * ドメイン用ディレクトリ/
     * ├── private_config/portfolio/    ← 設定ファイル
     * └── public_html/
     *     └── 公開ディレクトリ/ ← アプリ本体
     */
    $candidates[] = dirname(__DIR__, 3) . '/private_config/portfolio/' . $filename;

    foreach ($candidates as $path) {
        if (is_readable($path)) {
            try {
                $config = require $path;
            } catch (Throwable $e) {
                failPrivateConfigLoad($filename, 'require_failed', $e);
            }

            if (!is_array($config)) {
                failPrivateConfigLoad($filename, 'invalid_format');
            }

            return $config;
        }
    }

    failPrivateConfigLoad($filename, 'not_found');
}

/**
 * 環境設定を読み込み、既存コードが参照する APP_ENV 定数を補完する。
 *
 * @return array<string, mixed>
 * @throws RuntimeException
 */
function loadPrivateEnvConfig(): array
{
    $envConfig = loadPrivateConfig('env.php');

    if (!defined('APP_ENV') && isset($envConfig['APP_ENV']) && is_string($envConfig['APP_ENV'])) {
        define('APP_ENV', $envConfig['APP_ENV']);
    }

    return $envConfig;
}
