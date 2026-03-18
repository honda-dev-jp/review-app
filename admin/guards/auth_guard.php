<?php

declare(strict_types=1);

/**
 * ログイン状態を確認する認証ガード関数。
 *
 * セッションに user_id が存在しない場合は未ログインと判断する。
 * 未ログイン時はエラーメッセージをセッションに格納し、
 * ログインページへリダイレクトして処理を中断する。
 *
 * 【仕様】
 * - ログイン済みの場合は何もせず処理を継続
 * - 未ログインの場合はログインページへリダイレクトして exit
 *
 * 【注意】
 * - header() を使用するため、事前に出力が行われていないこと
 *
 * @return void
 */

require_once __DIR__ . '/../lib/utils.php';

function checkLogin(): void
{
    // ログインしていない場合は、エラーメッセージを格納してログインページにリダイレクト
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $messages = require __DIR__ . '/../lang/messages.php';
        $_SESSION['error'][] = $messages['common']['login_required'];

        // ベースURLを取得して、リダイレクト先のURLを組み立て
        $baseUrl = getBaseUrl();
        $location = $baseUrl . '/index.php'; // ベースURLに `/index.php` を追加

        header('Location: ' . $location);
        exit;
    }
}
