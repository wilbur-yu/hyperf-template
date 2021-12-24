<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Exception\Redis;

use Hyperf\Server\Exception\ServerException;

class LimiterTimeoutException extends ServerException
{
}
