<?php

declare(strict_types=1);

// 1. 環境設定の読み込み（最優先：他の全てが依存する）
require_once __DIR__ . '/../config/env.php';

// 2.定数定義（env.phpに依存する為2番目）
require_once __DIR__ . '/lib/utils.php';
define('ADMIN_BASE_PATH', getBaseUrl());

// 3.セッションを安全に開始
require_once __DIR__ . '/lib/security/session.php';
startSecureSession();

// 4.共通ライブラリの読み込み
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/sanitize.php';
require_once __DIR__ . '/lib/flash.php';
require_once __DIR__ . '/lib/exception_handler.php';

// 5.セキュリティの読み込み
require_once __DIR__ . '/lib/security/csrf.php';

// 6.ガードの読み込み
require_once __DIR__ . '/guards/redirect_guard.php';
require_once __DIR__ . '/guards/request_guard.php';

// 7.例外処理
set_exception_handler('handle_exception');
