<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Exception\Report;

use App\Support\Macros\CollectionMacro;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Str;
use Throwable;

use function mb_strlen;

/**
 * This is file is modified from the laravel/telescope.
 */
class ExceptionContext
{
    /**
     * Get the exception code context for the given exception.
     *
     * @param  \Throwable  $exception
     *
     * @return string
     */
    public static function getContextAsString(Throwable $exception): string
    {
        Collection::mixin(make(CollectionMacro::class));
        $contextStr = collect(static::get($exception))
            ->tap(
                function (Collection $context) use ($exception, &$exceptionLine, &$markedExceptionLine, &$maxLineLen) {
                    $exceptionLine = $exception->getLine();
                    $markedExceptionLine = sprintf('➤ %s', $exceptionLine);
                    $maxLineLen = max(
                        mb_strlen((string)array_key_last($context->toArray())),
                        mb_strlen($markedExceptionLine)
                    );
                }
            )->reduces(function ($carry, $code, $line) use ($maxLineLen, $markedExceptionLine, $exceptionLine) {
                $line === $exceptionLine and $line = $markedExceptionLine;
                $line = sprintf("%{$maxLineLen}s", $line);

                return "$carry  $line    $code".PHP_EOL;
            }, '');

        return sprintf('(%s)', PHP_EOL.$contextStr);
    }

    /**
     * Get the exception code context for the given exception.
     *
     * @param  \Throwable  $exception
     *
     * @return array
     */
    public static function get(Throwable $exception): array
    {
        return static::getEvalContext($exception) ?? static::getFileContext($exception);
    }

    /**
     * Get the exception code context when eval() failed.
     *
     * @param  \Throwable  $exception
     *
     * @return array|null
     */
    protected static function getEvalContext(Throwable $exception): ?array
    {
        if (Str::contains($exception->getFile(), "eval()'d code")) {
            return [
                $exception->getLine() => "eval()'d code",
            ];
        }

        return null;
    }

    /**
     * Get the exception code context from a file.
     *
     * @param  \Throwable  $exception
     *
     * @return array
     */
    protected static function getFileContext(Throwable $exception): array
    {
        return collect(explode("\n", file_get_contents($exception->getFile())))
            ->slice($exception->getLine() - 10, 20)
            ->mapWithKeys(function ($value, $key) {
                return [$key + 1 => $value];
            })
            ->all();
    }
}
