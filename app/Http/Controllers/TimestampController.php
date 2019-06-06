<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Timestamp;
use Carbon\Carbon;
use Illuminate\Http\Response;

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

    public function edit(User $user, Request $request, int $tsId)
    {
        $timestamp = Timestamp::where('user_id', $user->id)->find($tsId);
        if (!$timestamp) {
            return response(['message' => 'Timestamp not found'], Response::HTTP_NOT_FOUND);
        }

        $newTs = $request->input('ts');
        if (!$newTs) {
            return response(["message" => 'Missing "ts" on request body'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $newTs = Carbon::parse($newTs);
        } catch (\Throwable $th) {
            return response(["message" => 'Invalid "ts" on request body'], Response::HTTP_BAD_REQUEST);
        }

        $timestamp->date = $newTs->format('Y-m-d');
        $timestamp->time = $newTs->format('H:i:s');
        $success = $timestamp->save();
        if (!$success) {
            return response(['message' => 'Failed to update timestamp.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response(['message' => 'ok'], Response::HTTP_OK);
    }

    private function register(User $user, Request $request, bool $entry)
    {
        $timestamp = $request->input('ts');
        if (!$timestamp) {
            return response(["message" => 'Missing "ts" on request body'], Response::HTTP_BAD_REQUEST);
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
            'date' => $parsedTimestamp->format('Y-m-d'),
            'time' => $parsedTimestamp->format('H:i:s'),
            'entry' => $entry,
        ]);

        $success = $timestamp->save();

        if (!$success) {
            return response(['message' => 'Failed to insert timestamp.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response(['message' => 'Timestamp inserted successfully'], Response::HTTP_OK);
    }
}
