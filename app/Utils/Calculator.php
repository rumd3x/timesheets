<?php

namespace App\Utils;

use App\Timestamp;
use Carbon\Carbon;

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
}
