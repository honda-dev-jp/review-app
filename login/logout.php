<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../app/security/session.php';
require_once __DIR__ . '/../app/security/csrf.php';
require_once __DIR__ . '/../app/guards/request_guard.php';
require_once __DIR__ . '/../app/guards/redirect_guard.php';

// セッションを安全に開始
startSecureSession();

// POSTリクエストかチェック
requirePost();

// CSRFトークンの検証（成功時にワンタイム消費）
if (!validateCSRFTokenOnce()) {
    redirectWithError('不正な操作が検出されました。');
}

// 認証情報だけ破棄（フラッシュは残す）
unset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['role']);

// セッションID再生成（ログアウト後は別セッションへ）
session_regenerate_id(true);

redirectWithSuccess('ログアウトしました。');
