<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Support;

use JetBrains\PhpStorm\Pure;

/**
 * 用于货币计算
 */
class Money
{
    public static function calc(
        int|float|string $n1,
        string $symbol,
        int|float|string $n2,
        int $scale = 2
    ): string {
        return match ($symbol) {
            '+' => bcadd((string)$n1, (string)$n2, $scale),
            '-' => bcsub((string)$n1, (string)$n2, $scale),
            '*' => bcmul((string)$n1, (string)$n2, $scale),
            '/' => bcdiv((string)$n1, (string)$n2, $scale),
            '%' => bcmod((string)$n1, (string)$n2, $scale),
        };
    }

    public static function comp(int|float|string $n1, int|float|string $n2, int $scale = 2): int
    {
        return bccomp((string)$n1, (string)$n2, $scale);
    }

    #[Pure]
    public static function yuanToFen(int|float|string $price, int $scale = 2): int
    {
        return (int)self::calc(100, '*', $price);
    }

    #[Pure]
    public static function fenToYuan(int|float|string $price, int $scale = 2): string
    {
        return self::calc(self::format($price), '/', 100, $scale);
    }

    /**
     * 价格格式化
     *
     * @param  int|float|string  $price
     * @param  int               $decimal
     *
     * @return string
     */
    public static function format(int|float|string $price, int $decimal = 2): string
    {
        return number_format((float)$price, $decimal, '.', '');
    }
}
