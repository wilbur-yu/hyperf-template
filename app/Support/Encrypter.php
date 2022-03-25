<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Support;

use App\Constants\BusCode;
use App\Exception\DecryptException;
use Carbon\Carbon;
use HyperfExt\Encryption\Crypt;
use Throwable;

final class Encrypter
{
    /**
     * 有效期, 单位:秒, 默认1小时
     * @var int
     */
    protected static int $expires = 3600;

    protected static string $delimiter = '[]';

    public static function decrypt(string $payload, bool $isExplode = false, ?string $errMessage = null): string|array
    {
        $timestamp = time();
        // 对加密内容解密时, 增加有效期信息判断
        try {
            $decrypt = Crypt::decrypt(urldecode($payload));
            $decrypt = explode(self::$delimiter, $decrypt);
            $decryptLeastCount = $isExplode ? 3 : 2;
            (empty($decrypt) || count($decrypt) < $decryptLeastCount)
            && throw new DecryptException(
                BusCode::CRYPT_DECRYPT_EXPLODE_FAILED
            );

            // 是过期的
            $expired = (int)array_pop($decrypt);
            (empty($expired) || ($timestamp >= $expired))
            && throw new DecryptException(
                BusCode::CRYPT_DECRYPT_EXPIRE_FAILED
            );

            return $isExplode ? $decrypt : array_pop($decrypt);
        } catch (Throwable $e) {
            if ($e instanceof DecryptException) {
                $code = $e->getCode();
                $message = $e->getMessage();
            }
            throw new DecryptException(
                $code ?? BusCode::CRYPT_DECRYPT_FAILED,
                $errMessage ?? $message ?? '',
                previous: !($e instanceof DecryptException) ? $e : null
            );
        }
    }

    public static function encrypt(mixed $value, ?string $slug = null, ?int $expires = null): string
    {
        // 对加密内容增加有效期信息
        $now = Carbon::now();
        $expiredAt = $expires ? $now->addSeconds($expires) : $now->addSeconds(self::$expires);
        $value = $slug ? $value.self::$delimiter.$slug : $value;
        $value .= self::$delimiter.$expiredAt->timestamp;

        return Crypt::encrypt($value);
    }
}
