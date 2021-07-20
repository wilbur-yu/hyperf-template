<?php

declare(strict_types = 1);
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
class SysCode extends AbstractConstants
{
    /**
     * @Message("容器内未找到")
     */
    public const APP_GET_NOT_FOUND_ERROR = 5001;
}
