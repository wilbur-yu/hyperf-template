<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Exception\Formatter;

use Hyperf\Utils\Arr;
use Throwable;

trait ExceptionFormatter
{
    public function format(Throwable $throwable, bool $isHoldArgs = true): array
    {
        $exception = [];
        $exception['current'] = [
            'exception' => get_class($throwable),
            'code' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
            'custom_data' => $isHoldArgs && method_exists($throwable, 'getCustomData') ?
                $throwable->getCustomData() : null,
            'trace' => $isHoldArgs ? $throwable->getTrace() : $this->removeKeys($throwable->getTrace(), ['args']),
        ];
        if ($throwable->getPrevious()) {
            $exception['previous'] = [
                'exception' => get_class($throwable->getPrevious()),
                'code' => $throwable->getPrevious()->getCode(),
                'message' => $throwable->getPrevious()->getMessage(),
                'custom_data' => $isHoldArgs && is_object($throwable->getPrevious())
                                 && method_exists(
                                     $throwable->getPrevious(),
                                     'getCustomData'
                                 ) ? $throwable->getPrevious()->getCustomData() : null,
                'trace' => $isHoldArgs ? $throwable->getPrevious()->getTrace() :
                    $this->removeKeys($throwable->getPrevious()->getTrace(), ['args']),
            ];
        }

        return $exception;
    }

    protected function removeKeys(array $data, array $keys): array
    {
        return collect($data)->map(static function ($datum) use (&$keys) {
            Arr::except($datum, $keys);
        })->toArray();
    }
}
