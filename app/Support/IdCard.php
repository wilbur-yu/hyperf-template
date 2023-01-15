<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Support;

class IdCard
{
    /**
     * 验证身份证号码
     *
     * @param  string  $idCard  身份证号(18/15 位)
     *
     * @return bool
     */
    public static function filter(string $idCard): bool
    {
        if (strlen($idCard) === 18) {
            return self::checksum18($idCard);
        }

        if ((strlen($idCard) === 15)) {
            $idCard = self::to18($idCard);

            return self::checksum18($idCard);
        }

        return false;
    }

    /**
     * 18位身份证校验码有效性检查
     *
     * @param  string  $idCard
     *
     * @return bool
     */
    protected static function checksum18(string $idCard): bool
    {
        //15位身份证升级到18位
        if (strlen($idCard) === 15) {
            $idCard = self::to18($idCard);
        }
        if (strlen($idCard) !== 18) {
            return false;
        }
        $aCity = [
            11 => "北京",
            12 => "天津",
            13 => "河北",
            14 => "山西",
            15 => "内蒙古",
            21 => "辽宁",
            22 => "吉林",
            23 => "黑龙江",
            31 => "上海",
            32 => "江苏",
            33 => "浙江",
            34 => "安徽",
            35 => "福建",
            36 => "江西",
            37 => "山东",
            41 => "河南",
            42 => "湖北",
            43 => "湖南",
            44 => "广东",
            45 => "广西",
            46 => "海南",
            50 => "重庆",
            51 => "四川",
            52 => "贵州",
            53 => "云南",
            54 => "西藏",
            61 => "陕西",
            62 => "甘肃",
            63 => "青海",
            64 => "宁夏",
            65 => "新疆",
            71 => "台湾",
            81 => "香港",
            82 => "澳门",
            91 => "国外",
        ];
        //非法地区
        if (!array_key_exists(substr($idCard, 0, 2), $aCity)) {
            return false;
        }
        //验证生日
        if (!checkdate((int)substr($idCard, 10, 2), (int)substr($idCard, 12, 2), (int)substr($idCard, 6, 4))) {
            return false;
        }
        //验证年
        if (substr($idCard, 6, 4) > date('Y')) {
            return false;
        }
        $idCardBase = substr($idCard, 0, 17);

        return self::verify($idCardBase) === strtoupper($idCard[17]);
    }

    /**
     * 将15位身份证升级到18位
     *
     * @param  string  $idCard
     *
     * @return string
     */
    protected static function to18(string $idCard): string
    {
        if (strlen($idCard) !== 15) {
            return '';
        }

        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (in_array(substr($idCard, 12, 3), ['996', '997', '998', '999'], true)) {
            $idCard = substr($idCard, 0, 6).'18'.substr($idCard, 6, 9);
        } else {
            $idCard = substr($idCard, 0, 6).'19'.substr($idCard, 6, 9);
        }
        $idCard .= self::verify($idCard);

        return $idCard;
    }

    /**
     * 计算身份证校验码，根据国家标准GB 11643-1999
     *
     * @param $idCardBase
     *
     * @return string|bool
     */
    protected static function verify($idCardBase): string|bool
    {
        if (strlen($idCardBase) !== 17) {
            return false;
        }
        //加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        //校验码对应值
        $verifyNumberList = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $checksum = 0;
        for ($i = 0, $iMax = strlen($idCardBase); $i < $iMax; $i++) {
            $checksum += substr($idCardBase, $i, 1) * $factor[$i];
        }
        $mod = strtoupper((string)($checksum % 11));

        return $verifyNumberList[$mod];
    }
}
