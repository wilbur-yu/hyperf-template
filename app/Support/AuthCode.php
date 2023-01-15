<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu
 */

namespace App\Support;

use App\Constants\BusCode;
use App\Exception\DecryptException;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Throwable;

/**
 * Discuz! 经典加密解密函数
 */
class AuthCode
{
    /**
     * 动态密钥(向量IV) 长度
     * 动态密钥参与加密后, 可以令密文无任何规律, 即使是原文与密钥完全相同,加密结果也会每次不同, 增大了破解难度
     * 取值越大, 密文变动规律越大, 密文变化 = 16 的 动态密钥长度 次方
     * 作用: 相同的明文会生成不同密文
     */
    private int $dynamicKeyLength;

    /**
     * 从基础密钥中选取一部分, 参与加解密
     * @var string
     */
    private string $subSecretKeyA;

    /**
     * 从基础密钥中选取一部分, 做数据完整性验证
     * @var string
     */
    private string $subSecretKeyB;

    public function __construct(protected ContainerInterface $container)
    {
        try {
            $config = $this->container->get(ConfigInterface::class);
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            throw new RuntimeException($e->getMessage());
        }
        // 初始化基础密钥
        $secretKey = $config->get('auth_code.secret_key');
        // 初始化向量iv
        $this->dynamicKeyLength = $config->get('auth_code.dynamic_key_length');
        $this->subSecretKeyA = md5(substr($secretKey, 0, 16));
        $this->subSecretKeyB = md5(substr($secretKey, 0, 16));
    }

    public function encrypt(string|array|int $data, int $expiry = 0): string
    {
        // 动态密匙用于变化生成的密文
        $dynamicKey = $this->dynamicKeyLength ? substr(md5(microtime()), -$this->dynamicKeyLength) : '';
        $cryptKey = $this->init($dynamicKey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$this->subSecretKeyB，
        // 解密时会通过这个密匙验证数据完整性
        $dataPack = $this->pack($data);
        $dataPack = sprintf('%010d', $expiry ? $expiry + time() : 0)
                    .substr(md5($dataPack.$this->subSecretKeyB), 0, 16)
                    .$dataPack;
        $result = $this->calculation($dataPack, $cryptKey);

        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        $replace = $this->replace(__FUNCTION__);

        return $replace($dynamicKey.str_replace('=', '', base64_encode($result)));
    }

    protected function init(string $dynamicKey): string
    {
        // 参与运算的密钥
        return $this->subSecretKeyA.md5($this->subSecretKeyA.$dynamicKey);
    }

    protected function pack(string|array|int $data): string|int
    {
        return is_numeric($data) && !in_array($data, [INF, -INF], true)
               && !is_nan((float)$data) ? $data : serialize($data);
    }

    protected function calculation(string $data, $cryptKey): string
    {
        $cryptKeyLength = strlen($cryptKey);
        $dataLength = strlen($data);
        $result = '';
        $box = range(0, 255);
        // 生产密匙簿
        $randKeys = [];
        for ($i = 0; $i <= 255; $i++) {
            $randKeys[$i] = ord($cryptKey[$i % $cryptKeyLength]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $randKeys[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $dataLength; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($data[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        return $result;
    }

    protected function replace(string $operation): callable
    {
        return match ($operation) {
            'decrypt' => static function ($decrypt) {
                // $mod4 = strlen($data) % 4;
                // if ($mod4) {
                //     $data .= substr('====', $mod4);
                // }

                return str_replace(['-', '_', '.'], ['+', '/', '='], $decrypt);
            },
            'encrypt' => static function ($encrypt) {
                return str_replace(['+', '/', '='], ['-', '_', '.'], $encrypt);
            },
            default => throw new RuntimeException('调用方法错误'),
        };
    }

    public function decrypt(string $encrypt, ?int $errorCode = null): int|array|string
    {
        try {
            $replace = $this->replace(__FUNCTION__);
            $encrypt = $replace($encrypt);
            // 动态密匙用于变化生成的密文
            $dynamicKey = $this->dynamicKeyLength ? substr($encrypt, 0, $this->dynamicKeyLength) : '';
            $cryptKey = $this->init($dynamicKey);
            // 从第 $this->dynamicKeyLength 位开始，因为密文前 $this->dynamicKeyLength 位保存动态密匙，以保证解密正确
            $encrypt = base64_decode(substr($encrypt, $this->dynamicKeyLength));
            $result = $this->calculation($encrypt, $cryptKey);
            // substr($result, 0, 10) == 0 验证数据有效性
            // substr($result, 0, 10) - time() > 0 验证数据有效性
            // substr($result, 10, 16) == substr(md5(substr($result, 26).$this->subSecretKeyB), 0, 16) 验证数据完整性
            // 验证数据有效性，请看未加密明文的格式
            // $resultExpiryIsValid = substr($result, 0, 10) == 0 || (int)substr($result, 0, 10) - time() > 0;
            // $resultIsValid = substr($result, 10, 16) == substr(md5(substr($result, 26).$this->subSecretKeyB), 0, 16);
            $resultExpiryIsValid = str_starts_with($result, '0') || (((int)substr($result, 0, 10) - time()) > 0);
            $resultIsValid = str_starts_with(
                md5(substr($result, 26).$this->subSecretKeyB),
                substr($result, 10, 16)
            );
            if (!$resultExpiryIsValid) {
                throw new DecryptException(
                    $errorCode ?? BusCode::CRYPT_DECRYPT_EXPIRE_FAILED,
                    data: ['encrypt' => $encrypt]
                );
            }

            if (!$resultIsValid) {
                throw new DecryptException(
                    $errorCode ?? BusCode::CRYPT_DECRYPT_AUTHORITY_FAILED,
                    data: ['encrypt' => $encrypt]
                );
            }

            return $this->unpack(substr($result, 26));
        } catch (Throwable $e) {
            if ($e instanceof DecryptException) {
                throw $e;
            }
            throw new DecryptException(
                $errorCode ?? BusCode::CRYPT_DECRYPT_FAILED,
                previous: $e,
                data: ['encrypt' => $encrypt]
            );
        }
    }

    protected function unpack(string|int $data): int|string|array
    {
        return is_numeric($data) ? $data : unserialize($data, ['allowed_classes' => false]);
    }
}
