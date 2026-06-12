<?php

declare(strict_types=1);

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
        throw new RuntimeException('設定ファイル名が不正です: ' . basename($filename));
    }

    if (!in_array($filename, ['env.php', 'database.php'], true)) {
        throw new RuntimeException('許可されていない設定ファイルです: ' . $filename);
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
            $config = require $path;

            if (!is_array($config)) {
                throw new RuntimeException('設定ファイルの形式が不正です: ' . $filename);
            }

            return $config;
        }
    }

    throw new RuntimeException('設定ファイルが見つかりません: ' . $filename);
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
