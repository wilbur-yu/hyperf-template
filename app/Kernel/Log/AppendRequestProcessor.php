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
namespace App\Kernel\Log;

use Hyperf\Utils\Context;
use Monolog\Processor\ProcessorInterface;

class AppendRequestProcessor implements ProcessorInterface
{
    public const LOG_REQUEST_ID_KEY = 'log.request_id';

    public const LOG_USER_ID_KEY = 'log.user_id';

    public const LOG_COROUTINE_ID_KEY = 'log.coroutine_id';

    public function __invoke(array $record): array
    {
        $record['context']['request_id']   = Context::get(self::LOG_REQUEST_ID_KEY);
        $record['context']['coroutine_id'] = Context::get(self::LOG_COROUTINE_ID_KEY);

        return $record;
    }
}
