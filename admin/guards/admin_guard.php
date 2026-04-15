<?php

declare(strict_types=1);

/**
 * 管理者権限を確認する認可ガード関数。
 *
 * セッションに設定されたユーザー権限（role）が 'admin' であるかを確認する。
 * 管理者以外のユーザーがアクセスした場合は、
 * エラーメッセージをセッションに格納した上でトップページへリダイレクトし、
 * 処理を中断する。
 *
 * 【仕様】
 * - $_SESSION['role'] が 'admin' の場合のみ通過
 * - 管理者以外の場合はリダイレクトして exit
 *
 * 【前提】
 * - ログイン処理により $_SESSION['role'] が設定されていること
 * - role の値は 'admin' または 'member' を想定
 *
 * 【注意】
 * - header() を使用するため、事前に出力が行われていないこと
 * - checkLogin() 実行後に呼び出すことを推奨
 *
 * @return void
 */

require_once __DIR__ . '/../lib/utils.php'; // 共通化したutils.phpをインクルード

function checkAdmin(): void
{
    // 管理者以外はアクセス不可
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $messages = require __DIR__ . '/../lang/messages.php';
        $_SESSION['error'][] = $messages['common']['admin_only'];

        // ベースURLを取得して、リダイレクト先のURLを組み立て
        $baseUrl = getBaseUrl();
        $location = $baseUrl . '/index.php'; // ベースURLに `/index.php` を追加

        header('Location: ' . $location);
        exit;
    }
}
