<?php

declare(strict_types=1);

/**
 * 平均評価を ★☆ 表示でレンダリングするヘルパー関数
 *
 * 表示イメージ：
 *   ★★★★☆（4.0）
 *   ★★★☆☆（3.1）
 *   未評価
 *
 * 設計方針：
 * - 整数に丸めて塗り★と空☆を並べる
 * - 数値は小数点1桁で添える
 * - Bootstrap に依存しない純粋なHTML文字列を返す
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

    $rounded = (int) round($avg);
    $filled  = str_repeat('★', $rounded);
    $empty   = str_repeat('☆', $max - $rounded);
    $value   = number_format($avg, 1);

    return sprintf(
        '<span class="text-warning">%s</span><span class="text-secondary">%s</span>'
        . '&nbsp;<small class="text-muted">（%s）</small>',
        htmlspecialchars($filled, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($empty, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
    );
}
