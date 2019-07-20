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
     * Delete a timestamps
     *
     * @param integer $id
     * @param User $user
     * @return bool
     */
    public static function delete(int $id, User $user = null)
    {
        if ($user === null) {
            return Timestamp::find($id)->delete();
        }

        return Timestamp::where('user_id', $user->id)->find($id)->delete();
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

    /**
     * Find timestamp by id and user
     *
     * @param integer $id
     * @param User $user
     * @return Timestamp|null
     */
    public static function findById(int $id, User $user = null)
    {
        if (!$user) {
            return Timestamp::find($id);
        }

        return Timestamp::where('user_id', $user->id)->find($id);
    }

    /**
     * Find Earliest register by day and optionally type
     *
     * @param Carbon $day
     * @param User $user
     * @param boolean $entry
     * @return Timestamp|null
     */
    public static function findEarliestByDay(Carbon $day, User $user, bool $entry = null)
    {
        $query = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time')->where('user_id', $user->id)->limit(1);
        if ($entry !== null) {
            $query->where('entry', $entry);
        }

        $result = $query->get();

        if ($result->isEmpty()) {
            return null;
        }

        return $result->first();
    }

    /**
     * Find latest register by day and optionally type
     *
     * @param Carbon $day
     * @param User $user
     * @param boolean $entry
     * @return Timestamp|null
     */
    public static function findLatestByDay(Carbon $day, User $user, bool $entry = null)
    {
        $query = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time', 'desc')->where('user_id', $user->id)->limit(1);
        if ($entry !== null) {
            $query->where('entry', $entry);
        }

        $result = $query->get();

        if ($result->isEmpty()) {
            return null;
        }

        return $result->first();
    }

    /**
     * Retrieves user latest timestamp optionally filtered by type
     *
     * @param User $user
     * @param boolean $entry
     * @return Timestamp|null
     */
    public static function lastByUser(User $user, bool $entry = null)
    {
        $query = Timestamp::whereUserId($user->id)->orderBy('date', 'desc')->orderBy('time', 'desc')->limit(1);

        if ($entry !== null) {
            $query->whereEntry($entry);
        }

        $result = $query->get();

        if ($result->isEmpty()) {
            return null;
        }

        return $result->first();
    }

    /**
     * Edit the timestamp with the new data
     *
     * @param Timestamp $ts
     * @param array $data
     * @return bool
     */
    public static function edit(Timestamp &$ts, array $data)
    {
        $success = Timestamp::find($ts->id)->update($data);

        if ($success) {
            $ts = self::findById($ts->id);
        }

        return $success;
    }
}
