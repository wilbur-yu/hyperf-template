<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Constants\BusConstant;

class GuardConstant
{
    public const GUARD_CURRENT_KEY = 'current_guard';
    public const GUARD_STUDENT = 'student';
    public const GUARD_COACH = 'coach';
    public const GUARD_DEFAULT = 'user';
    /**
     * @Message("用户角色map")
     */
    public const GUARD_MAP = [
        self::GUARD_STUDENT,
        self::GUARD_COACH,
    ];
}
