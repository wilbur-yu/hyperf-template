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
namespace App\Support;

/**
 * ProcessUtils is a bunch of utility methods.
 *
 * This class was originally copied from Symfony 3.
 *
 * @license https://github.com/laravel/framework/blob/8.x/src/Illuminate/Support/ProcessUtils.php
 */
class ProcessUtils
{
    /**
     * Escapes a string to be used as a shell argument.
     */
    public static function escapeArgument(string $argument): string
    {
        // Fix for PHP bug #43784 escapeshellarg removes % from given string
        // Fix for PHP bug #49446 escapeshellarg doesn't work on Windows
        // @see https://bugs.php.net/bug.php?id=43784
        // @see https://bugs.php.net/bug.php?id=49446
        if ('\\' === DIRECTORY_SEPARATOR) {
            if ($argument === '') {
                return '""';
            }

            $escapedArgument = '';
            $quote           = false;

            foreach (preg_split('/(")/', $argument, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
                if ($part === '"') {
                    $escapedArgument .= '\\"';
                } elseif (self::isSurroundedBy($part, '%')) {
                    // Avoid environment variable expansion
                    $escapedArgument .= '^%"' . substr($part, 1, -1) . '"^%';
                } else {
                    // escape trailing backslash
                    if (substr($part, -1) === '\\') {
                        $part .= '\\';
                    }
                    $quote = true;
                    $escapedArgument .= $part;
                }
            }

            if ($quote) {
                $escapedArgument = '"' . $escapedArgument . '"';
            }

            return $escapedArgument;
        }

        return "'" . str_replace("'", "'\\''", $argument) . "'";
    }

    /**
     * Is the given string surrounded by the given character?
     */
    protected static function isSurroundedBy(string $arg, string $char): bool
    {
        return 2 < strlen($arg) && $char === $arg[0] && $arg[strlen($arg) - 1] === $char;
    }
}
