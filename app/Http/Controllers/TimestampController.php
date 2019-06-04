<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Timestamp;
use Carbon\Carbon;

class TimestampController extends Controller
{
    public function in(User $user, Request $request)
    {
        return $this->register($user, $request, true);
    }

    public function out(User $user, Request $request)
    {
        return $this->register($user, $request, false);
    }

    private function register(User $user, Request $request, bool $entry)
    {
        $timestamp = $request->input('ts');
        if (!$timestamp) {
            return response(["message" => 'Missing "ts" on request body'], 400);
        }

        try {
            $timestamp = implode('', explode(' at ', $timestamp));
            $parsedTimestamp = Carbon::parse($timestamp);
        } catch (\Throwable $th) {
            $parsedTimestamp = Carbon::now();
        }

        $timezone = 'America/Sao_Paulo';
        if (getenv('TZ')) {
            $timezone = getenv('TZ');
        }

        if ($parsedTimestamp->format('Y-m-d H:i') === Carbon::now()->format('Y-m-d H:i')) {
            $parsedTimestamp->setTimezone($timezone);
        }

        $timestamp = new Timestamp([
            'user_id' => $user->id,
            'moment' => $parsedTimestamp,
            'entry' => $entry,
        ]);

        $success = $timestamp->save();

        if (!$success) {
            return response(['message' => 'Failed to insert timestamp.'], 500);
        }

        return response(['message' => 'Timestamp inserted successfully'], 200);
    }
}
