<?php

declare(strict_types=1);

/**
 * 値が正の整数かチェックする
 *
 * 値が正の整数であるかを判定する。
 * 文字列や数値であっても、正の整数であれば true を返す。
 *
 * @param mixed $value チェック対象の値
 * @return bool 値が正の整数の場合 true、そうでない場合 false
 */
function is_positive_integer(mixed $value): bool
{
    // 値が正の整数かチェックする
    return ctype_digit((string) $value) && (int) $value > 0;
}
