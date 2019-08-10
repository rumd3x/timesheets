<?php

namespace App\Utils;

use App\User;
use Carbon\Carbon;
use App\AppSetting;
use App\Repositories\TimestampRepository;
use App\Repositories\AppSettingRepository;

class Calculator
{

    /**
     * Calculate work time in minutes on given date
     *
     * @param Carbon $date
     * @param User $user
     * @return int
     */
    public static function timeInside(Carbon $date, User $user)
    {
        $timestamps = TimestampRepository::getByDay($date, $user);

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
     * @param User $user
     * @return int
     */
    public static function state(Carbon $day, User $user)
    {
        if ($day->isWeekend()) {
            return 0;
        }

        if ($day >= Carbon::today()) {
            return 0;
        }

        $targetHours = AppSettingRepository::get(AppSetting::TARGET_HOURS_DAY);
        if (!$targetHours) {
            $targetHours = 8;
        }

        return self::timeInside($day, $user) >= 60 * $targetHours ? 1 : -1;
    }

    /**
     * Returns bootstrap call for date state
     *
     * @param Carbon $day
     * @param User $user
     * @return string
     */
    public static function stateClass(Carbon $day, User $user)
    {
        $state = self::state($day, $user);

        if ($state < 0) {
            return 'text-danger';
        }

        if ($state > 0) {
            return 'text-success';
        }

        return '';
    }
}
