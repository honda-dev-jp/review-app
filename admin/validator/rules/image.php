<?php

declare(strict_types=1);

/**
 * 画像ファイルが有効かチェックする
 *
 * 以下の順番でチェックを行い、1つでも失敗した場合は false を返す。
 * 1. アップロードエラーがないこと
 * 2. ファイルサイズが1MB以内であること
 * 3. MIMEタイプが許可された形式であること（jpeg / png / gif / webp）
 * 4. 実際に画像として読み取れること
 *
 * @param array $file $_FILES['image'] の配列
 * @return bool 有効な画像ファイルの場合 true、そうでない場合 false
 */
function is_valid_image(array $file): bool
{
    // 1.アップロードエラーがないかチェック
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // 2.tmp_nameの存在確認
    if (!isset($file['tmp_name']) || $file['tmp_name'] === '') {
        return false;
    }

    // 3.ファイルのサイズチェック
    if (isset($file['size']) && $file['size'] > 1 * 1024 * 1024) {
        return false;
    }

    // 4. MIMEタイプのチェック（image/jpeg, image/png, image/gif, image/webpのみ許可）
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed_types = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    // 許可されたMIMEタイプかどうかをチェックする
    if (!isset($allowed_types[$mime])) {
        return false;
    }

    // 5.画像として読み取れるかチェックする
    if (@getimagesize($file['tmp_name']) === false) {
        return false;
    }

    return true;
}
