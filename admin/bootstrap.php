<?php

declare(strict_types=1);

// 1. 環境設定
require_once __DIR__ . '/../config/env.php';

// 2. 共通関数・定数
require_once __DIR__ . '/lib/utils.php';
define('ADMIN_BASE_PATH', getBaseUrl());

// 3. 例外ハンドラを最優先で読み込み・登録
require_once __DIR__ . '/lib/exception_handler.php';
set_exception_handler('handle_exception');

// 4. セッション開始
require_once __DIR__ . '/lib/security/session.php';
startSecureSession();

// 5. 共通ライブラリ
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/sanitize.php';
require_once __DIR__ . '/lib/rating.php';

// 6. セキュリティ
require_once __DIR__ . '/lib/security/csrf.php';

// 7. ガード
require_once __DIR__ . '/guards/redirect_guard.php';
require_once __DIR__ . '/guards/request_guard.php';
require_once __DIR__ . '/guards/auth_guard.php';
require_once __DIR__ . '/guards/admin_guard.php';
