<?php
declare(strict_types=1);

// 1.定数定義
require_once __DIR__ . '/lib/utils.php';
define('ADMIN_BASE_PATH', getBaseUrl());

// 2.環境設定の読み込み
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';

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

