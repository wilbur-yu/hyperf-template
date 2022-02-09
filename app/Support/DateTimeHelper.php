<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Hyperf\Utils\Collection;

class DateTimeHelper
{
    /**
     * Create a Collection of times with a given interval for a given period.
     * @link https://github.com/Label84/laravel-hours-helper
     *
     * @param  string|Carbon  $start
     * @param  string|Carbon  $end
     * @param  int            $interval
     * @param  string         $format
     * @param  array          $excludes
     *
     * @return Collection
     */
    public static function cycle(
        Carbon|string $start,
        Carbon|string $end,
        int $interval,
        string $format = 'H:i',
        array $excludes = []
    ): Collection {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        // +1 day if the end time is before the start time AND both are without date/on the same date
        if ($start->isSameDay($end) && $end->isBefore($start)) {
            $end = $end->addDay();
        }

        $period = CarbonInterval::minutes($interval)->toPeriod($start, $end);

        return collect($period)
            ->reject(function (Carbon $carbon) use ($excludes) {
                foreach ($excludes as $exclude) {
                    if ($carbon->between(Carbon::parse($exclude[0]), Carbon::parse($exclude[1]))) {
                        return true;
                    }
                }

                return false;
            })
            ->map(fn (Carbon $carbon) => $carbon->format($format))
            ->values();
    }
}
