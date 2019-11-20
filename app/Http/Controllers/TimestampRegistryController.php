<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Repositories\TimestampRepository;

class TimestampRegistryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function insert(Request $request)
    {
        $request->validate([
            'hour' => 'required|date_format:H:i',
            'date' => 'required|date_format:Y-m-d',
            'entry' => 'required|boolean',
        ]);

        TimestampRepository::insert(
            Carbon::parse(sprintf("%s %s", $request->input('date'), $request->input('hour'))),
            Auth::user(),
            (bool) $request->input('entry')
        );

        return Redirect::back();
    }

    public function delete(int $id)
    {
        $timestamp = TimestampRepository::findById($id, Auth::user());

        if (!$timestamp) {
            return Redirect::back()->withErrors(['Timestamp not found']);
        }

        $timestamp->delete();
        return Redirect::back();
    }
}
