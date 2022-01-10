<?php

declare(strict_types=1);
/**
 * This file is part of project hyperf-template.
 *
 * @author   wenber.yu@creative-life.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;
use phpDocumentor\Reflection\Types\Context;

/**
 * @method static getMessage(int $code)
 */
#[Constants]
class BusCode extends AbstractConstants
{
    /**
     * @Message("容器内未找到")
     */
    public const APP_GET_NOT_FOUND_ERROR = 5001;

    public const SUCCESS = 200;

    /**
     * @Message("系统: Server Error！")
     */
    public const SERVER_ERROR = 5000;

    /**
     * @Message("未授权")
     */
    public const SERVICE_UNAUTHORIZED = 4001;

    /**
     * @Message("认证token已过期")
     */
    public const SERVICE_AUTHENTICATION_TOKEN_EXPIRED = 4002;

    /**
     * @Message("认证token无效")
     */
    public const SERVICE_AUTHENTICATION_TOKEN_INVALID = 4003;

    /**
     * @Message("认证token已在黑名单")
     */
    public const SERVICE_AUTHENTICATION_TOKEN_BEEN_BLACKLISTED = 4004;
    /**
     * @Message("授权code无效")
     */
    public const SERVICE_AUTHENTICATION_CODE_INVALID = 4005;

    /**
     * @Message("当前身份不允许请求")
     */
    public const SERVICE_AUTHENTICATION_GUARD_UNAUTHORIZED = 4006;

    /**
     * @Message("信息非法")
     */
    public const CRYPT_DECRYPT_FAILED = 5100;
    /**
     * @Message("信息附加标识解析失败")
     */
    public const CRYPT_DECRYPT_EXPLODE_FAILED = 5101;
    /**
     * @Message("信息以过期, 请刷新")
     */
    public const CRYPT_DECRYPT_EXPIRE_FAILED = 5102;
}
