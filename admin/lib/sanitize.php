<?php

declare(strict_types=1);

/**
 * 値または配列の各要素を HTML エスケープする。
 *
 * - 非配列の値は文字列に変換してから htmlspecialchars() を適用する
 * - 配列の場合は各要素を文字列に変換してからエスケープする
 * - 主に View 出力用の補助関数として使用する
 *
 * @param mixed $before エスケープ対象の値、または値を持つ配列
 * @return string|array エスケープ後の文字列、またはエスケープ後の配列
 */
function sanitize($before)
{
    $after = [];
    if (!is_array($before)) {
        return htmlspecialchars((string) $before, ENT_QUOTES, 'UTF-8');
    }
    foreach ($before as $key => $value) {
        $after[$key] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
    return $after;
}
