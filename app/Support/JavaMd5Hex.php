<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Support;

class JavaMd5Hex
{
    /**
     * 16进制转string拼接
     *
     * @param  array $bytes
     *
     * @return string
     */
    public static function encodeHexString(array $bytes): string
    {
        $LOWER = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
        $length = count($bytes);
        $charArr = [];
        foreach ($bytes as $value) {
            $value = (int)$value;
            $charArr[] = $LOWER[self::uright(0xF0 & $value, 4)];
            $charArr[] = $LOWER[0x0F & $value];
        }

        return implode('', $charArr);
    }

    /** php 无符号右移 */
    public static function uright($a, $n): int
    {
        $c = 2147483647 >> ($n - 1);

        return $c & ($a >> $n);
    }

    /**
     * 模拟DigestUtils.md5
     *
     * @param  string  $string 加密字符
     *
     * @return array|false  加密之后的byte数组
     */
    public static function md5Hex(string $string): bool|array
    {
        return unpack('c*', md5($string, true));
    }
}
