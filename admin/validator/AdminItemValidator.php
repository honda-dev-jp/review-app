<?php

declare(strict_types=1);

require_once __DIR__ . '/rules/required.php';
require_once __DIR__ . '/rules/max_length.php';
require_once __DIR__ . '/rules/image.php';

/*
* 管理画面：作品登録・編集の入力バリデーション
*
* 設計方針：
* - 入力値を引数で受け取り、エラーメッセージの配列を返す関数として実装する。
* - エラーメッセージは lang/messages.php から取得する。
* - バリデーションルールは rules/ 以下の関数を呼び出して実装する。
*
* 使用例：
*   $errors = validateAdminItem($_POST, $_FILES);
*   if (!empty($errors)) {
*       // エラーがある場合の処理（例：エラーメッセージを表示）
*   }
*
*/
function validateAdminItem(array $post, array $files): array
{
    $messages = require __DIR__ . '/../lang/messages.php';

    // エラーメッセージの初期化
    $errors = [];

    // 1. タイトルは必須、80文字以内
    if (!is_required($post['title'] ?? '')) {
        $errors['title'] = $messages['validate']['item_title_required'];
    } elseif (!is_max_length($post['title'] ?? '', 80)) {
        $errors['title'] = $messages['validate']['item_title_too_long'];
    }

    // 2. 説明文は任意

    // 3. 画像は任意。アップロードエラー（サイズ超過）
    if ($files['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if (!is_valid_image($files['image'])) {
            $errors['image'] = $messages['validate']['item_image_invalid'];
        }
    }

    return $errors;
}
