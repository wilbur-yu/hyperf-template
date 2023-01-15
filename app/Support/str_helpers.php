<?php

declare(strict_types=1);

/**
 * 截取指定字符中间的内容.
 *
 * @param  string  $begin  开始字符串
 * @param  string  $end  结束字符串
 * @param  string  $string  需要处理的字符串
 *
 * @return string
 */
function sub_string(string $begin, string $end, string $string): string
{
    $b = mb_strpos($string, $begin) + mb_strlen($begin);
    $e = mb_strpos($string, $end) - $b;

    return mb_substr($string, $b, $e);
}

/**
 * 去除字符串中的各种换行符和空格.
 *
 * @param  string  $string  需要处理的字符串
 *
 * @return string 处理结果
 */
function custom_trim(string $string): string
{
    $string = preg_replace("/\n/", '', $string);
    $string = preg_replace("/\r/", '', $string);
    $string = preg_replace("/\t/", '', $string);
    $string = preg_replace('/ /', '', $string);

    return trim($string);
}

/**
 * 返回字符串中的中文.
 *
 * @param  string  $string  需要处理的字符串
 *
 * @return string 处理结果
 */
function get_chinese(string $string): string
{
    preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $string, $chinese);

    return implode('', $chinese[0]);
}

/**
 * 生成随机RGB颜色.
 *
 * @param  int  $min  颜色的最小值
 * @param  int  $max  颜色的最大值
 *
 * @throws \Exception
 * @return string 处理结果
 */
function random_rgb(int $min, int $max): string
{
    $r = random_int($min, $max);
    $g = random_int($min, $max);
    $b = random_int($min, $max);

    return "$r,$g,$b";
}

/**
 * 生成随机数字字符串.
 *
 * @param  int  $length  需要生成的字符串长度
 *
 * @return string 处理结果
 */
function random_number(int $length = 6): string
{
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= random_int(0, 9);
    }

    return $result;
}
