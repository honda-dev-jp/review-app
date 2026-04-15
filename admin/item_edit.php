<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/controller/AdminItemController.php';
require_once __DIR__ . '/model/AdminItemModel.php';

// 認証・認可チェック
checkLogin();
checkAdmin();

// PDO取得
$pdo = getPdo();

// Model / Controller生成
$model      = new \Admin\Models\AdminItemModel($pdo);
$controller = new \Admin\Controller\AdminItemController($model);

// 作品編集画面表示 / 更新処理
$controller->edit();
