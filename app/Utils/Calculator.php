<?php

namespace App\Utils;

use App\Timestamp;
use Carbon\Carbon;
use App\AppSetting;

class Calculator
{

    /**
     * Calculate work time in minutes on given date
     *
     * @param Carbon $date
     * @return int
     */
    public static function timeInside(Carbon $date)
    {
        $timestamps = Timestamp::where('date', $date->format('Y-m-d'))->orderBy('time')->get();

        $lastTs = null;
        $totalTime = 0;
        foreach ($timestamps as $ts) {
            if (!$lastTs) {
                $lastTs = $ts;
                continue;
            }

            if ($ts->entry == $lastTs->entry) {
                continue;
            }

            if ($lastTs->entry) {
                $totalTime += Carbon::parse("$lastTs->date $lastTs->time")->diffInMinutes(new Carbon("$ts->date $ts->time"));
            }
            $lastTs = $ts;
        }

        return $totalTime;
    }

    /**
     * Returns zero if passed date is weekend or if it is today or future
     * Return positive integer if worked less than 8 hours in the given date
     * Otherwise returns 1
     *
     * @param Carbon $day
     * @return int
     */
    public static function state(Carbon $day)
    {
        if ($day->isWeekend()) {
            return 0;
        }

        if ($day >= Carbon::today()) {
            return 0;
        }

        $targetHours = AppSetting::where('name', AppSetting::TARGET_HOURS_DAY)->first();
        if (!$targetHours) {
            $targetHours = 8;
        }

        $targetHours = (int) $targetHours;

        return self::timeInside($day) >= 60 * $targetHours ? 1 : -1;
    }

    /**
     * Returns bootstrap call for date state
     *
     * @param Carbon $day
     * @return string
     */
    public static function stateClass(Carbon $day)
    {
        $state = self::state($day);

        if ($state < 0) {
            return 'text-danger';
        }

        if ($state > 0) {
            return 'text-success';
        }

        return '';
    }
}
