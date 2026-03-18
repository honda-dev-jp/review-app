<?php

declare(strict_types=1);

/**
 * フラッシュメッセージ表示パーツ（Bootstrap版）
 *
 * 設計方針：
 * - セッションを直接読んで Bootstrap の alert として出力する
 * - 表示後はセッションから消去する
 * - sanitize() は bootstrap.php 経由で読み込み済みの前提
 */

// 成功メッセージ
if (!empty($_SESSION['success'])) {
    $successes = is_array($_SESSION['success']) ? $_SESSION['success'] : [$_SESSION['success']];
    foreach ($successes as $message) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo sanitize($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>';
        echo '</div>';
    }
    unset($_SESSION['success']);
}

// エラーメッセージ
if (!empty($_SESSION['error'])) {
    $errors = is_array($_SESSION['error']) ? $_SESSION['error'] : [$_SESSION['error']];
    foreach ($errors as $message) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo sanitize($message);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>';
        echo '</div>';
    }
    unset($_SESSION['error']);
}
