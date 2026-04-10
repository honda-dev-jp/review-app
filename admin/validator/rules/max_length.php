<?php

declare(strict_types=1);

/**
 * 文字数が上限以内かチェックする
 *
 * マルチバイト文字（日本語等）に対応した文字数チェックを行う。
 * 上限文字数と同じ文字数は許可する。
 *
 * @param string $value チェック対象の文字列
 * @param int    $max   上限文字数
 * @return bool 上限以内の場合 true、超えた場合 false
 */
function is_max_length(string $value, int $max): bool
{
    // 文字数が上限以内かチェックする。
    return mb_strlen($value) <= $max;
}
