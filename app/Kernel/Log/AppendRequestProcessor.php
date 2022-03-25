<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Kernel\Log;

use Hyperf\Context\Context;
use Monolog\Processor\ProcessorInterface;

class AppendRequestProcessor implements ProcessorInterface
{
    public const LOG_REQUEST_ID_KEY = 'log.request_id';

    public const LOG_USER_ID_KEY = 'log.user_id';

    public const LOG_COROUTINE_ID_KEY = 'log.coroutine_id';

    public const LOG_LIFECYCLE_KEY = 'lifecycle';

    public function __invoke(array $record): array
    {
        $record['context']['request_id'] = Context::get(self::LOG_REQUEST_ID_KEY);
        $record['context']['coroutine_id'] = Context::get(self::LOG_COROUTINE_ID_KEY);

        return $record;
    }
}
