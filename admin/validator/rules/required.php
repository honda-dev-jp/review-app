<?php

declare(strict_types=1);

/**
 * 値が空でないかチェックする
 *
 * 前後の空白を除去した上で空文字かどうかを判定する。
 * スペースのみの入力は空とみなす。
 *
 * @param string $value チェック対象の文字列
 * @return bool 値が存在する場合 true、空の場合 false
 */
function is_required(string $value): bool
{
    return trim($value) != '';
}
