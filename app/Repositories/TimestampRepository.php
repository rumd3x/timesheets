<?php

namespace App\Repositories;

use App\User;
use App\Timestamp;
use Carbon\Carbon;

class TimestampRepository
{
    /**
     * Inserts a new timestamp
     *
     * @param Carbon $timestamp
     * @param User $user
     * @return Timestamp
     */
    public static function insert(Carbon $timestamp, User $user, bool $entry)
    {
        return Timestamp::create([
            'user_id' => $user->id,
            'date' => $timestamp->format('Y-m-d'),
            'time' => $timestamp->format('H:i:s'),
            'entry' => $entry,
        ]);
    }

    /**
     * Retrieves the user timestamps on the given day
     *
     * @param Carbon $day
     * @param User $user
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getByDay(Carbon $day, User $user)
    {
        return Timestamp::where('date', $day->format('Y-m-d'))->where('user_id', $user->id)->orderBy('time')->get();
    }
}
