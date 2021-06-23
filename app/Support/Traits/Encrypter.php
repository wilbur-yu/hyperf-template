<?php

declare(strict_types = 1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Support\Traits;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use HyperfExt\Encryption\Crypt;
use HyperfExt\Encryption\Exception\DecryptException;

trait Encrypter
{
    public static function decrypt(string $payload, string $errMessage = ''): string
    {
        try {
            return Crypt::decrypt($payload);
        } catch (DecryptException $exception) {
            throw new BusinessException(ErrorCode::CRYPT_DECRYPT_ERROR, $errMessage);
        }
    }

    public static function encrypt(string $value): string
    {
        return Crypt::encrypt($value);
    }
}
