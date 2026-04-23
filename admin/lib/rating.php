<?php

declare(strict_types=1);

/**
 * 平均評価を ★ 塗り割合（CSS幅）でレンダリングするヘルパー関数
 *
 * 表示イメージ：
 *   ★★★★☆（4.0）← 4.0/5.0 = 80% 塗り
 *   ★★★☆☆（3.1）← 3.1/5.0 = 62% 塗り
 *   未評価
 *
 * 設計方針：
 * - ユーザー側と同じ CSS 幅方式で小数点以下も表現する
 * - .star-rating / .star-bg / .star-fg は admin.css で定義
 * - Bootstrap に依存しない純粋なHTML文字列を返す
 * - sanitize() は bootstrap.php 経由で読み込み済みの前提
 *
 * @param float|null $avg  平均評価（null の場合は未評価）
 * @param int        $max  最大評価（通常5）
 * @return string HTML文字列
 */
function renderAdminStarRating(?float $avg, int $max = 5): string
{
    if ($avg === null) {
        return '<span class="text-muted">未評価</span>';
    }

    $avg     = round($avg, 1);
    $percent = ($avg / $max) * 100;
    $stars   = str_repeat('★', $max);

    return sprintf(
        '<div style="display:flex;align-items:center;gap:4px;">'
        . '<div class="star-rating">'
        . '<div class="star-bg">%s</div>'
        . '<div class="star-fg" style="width:%s%%">%s</div>'
        . '</div>'
        . '<span class="rating-value">%s</span>'
        . '</div>',
        $stars,
        $percent,
        $stars,
        sanitize((string) $avg),
    );
}
