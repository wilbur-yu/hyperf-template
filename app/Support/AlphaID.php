<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Support;

/**
 * Translates a number to a short alhanumeric version
 *
 * Translated any number up to 9007199254740992
 * to a shorter version in letters e.g.:
 * 9007199254740989 --> PpQXn7COf
 *
 * specifiying the second argument true, it will
 * translate back e.g.:
 * PpQXn7COf --> 9007199254740989
 *
 * this function is based on any2dec && dec2any by
 * fragmer[at]mail[dot]ru
 * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
 *
 * If you want the alphaID to be at least 3 letter long, use the
 * $pad_up = 3 argument
 *
 * In most cases this is better than totally random ID generators
 * because this can easily avoid duplicate ID's.
 * For example if you correlate the alpha ID to an auto incrementing ID
 * in your database, you're done.
 *
 * The reverse is done because it makes it slightly more cryptic,
 * but it also makes it easier to spread lots of IDs in different
 * directories on your filesystem. Example:
 * $part1 = substr($alpha_id,0,1);
 * $part2 = substr($alpha_id,1,1);
 * $part3 = substr($alpha_id,2,strlen($alpha_id));
 * $destindir = "/".$part1."/".$part2."/".$part3;
 * // by reversing, directories are more evenly spread out. The
 * // first 26 directories already occupy 26 main levels
 *
 * more info on limitation:
 * - http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/165372
 *
 * if you really need this for bigger numbers you probably have to look
 * at things like: http://theserverpages.com/php/manual/en/ref.bc.php
 * or: http://theserverpages.com/php/manual/en/ref.gmp.php
 * but I haven't really dugg into this. If you have more info on those
 * matters feel free to leave a comment.
 *
 * @param  mixed  $id  String or long input to translate
 * @param  mixed  $pad  Number or boolean padds the result up to a specified length
 *
 * @return mixed string or long
 * @link      http://kevin.vanzonneveld.net/
 *
 * @author    Kevin van Zonneveld <[email protected]>
 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
 * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
 */
class AlphaID
{
    /**
     * 字典
     * @var string
     */
    protected string $dict;

    /**
     * 字典长度
     * @var int
     */
    protected int $dictLength;

    public function __construct()
    {
        $this->dict = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->dictLength = strlen($this->dict);
    }

    public function convert(int|string $id, int|bool $pad = false): int|string
    {
        if (is_int($id)) {
            return $this->encrypt($id);
        }

        return $this->decrypt($id);
    }

    protected function encrypt(int $id, int|bool $pad = false): string
    {
        if (is_numeric($pad)) {
            $pad--;
            if ($pad > 0) {
                $id += $this->dictLength ** $pad;
            }
        }

        $out = '';
        for ($t = floor(log10($id) / log10($this->dictLength)); $t >= 0; $t--) {
            $a = (int)floor($id / bcpow((string)$this->dictLength, (string)$t));
            $out .= $this->dict[$a];
            $id -= ($a * bcpow((string)$this->dictLength, (string)$t));
        }

        return strrev($out); // reverse
    }

    protected function decrypt(string $id, int|bool $pad = false): int
    {
        $reverse = strrev($id);
        $out = 0;
        $len = strlen($reverse) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $bcpow = bcpow((string)$this->dictLength, (string)($len - $t));
            $out = $out + strpos($this->dict, $reverse[$t]) * $bcpow;
        }

        if (is_numeric($pad)) {
            $pad--;
            if ($pad > 0) {
                $out -= $this->dictLength ** $pad;
            }
        }

        return $out;
    }
}
