<?php

namespace App\Http\Controllers;

use App\Timestamp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
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

    public function index()
    {
        $user = Auth::user();

        if ($user) {
            return redirect('login');
        }

        return redirect('home');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard()
    {
        $today = Carbon::now(getenv('TZ') ?: null)->format('D, M d Y');

        $lastEntered = Timestamp::whereUserId(Auth::user()->id)
        ->whereEntry(true)->orderBy('date')->orderBy('time')->first();
        $lastEnteredString = 'Never';
        if ($lastEntered) {
            $lastEnteredString = Carbon::parse(sprintf('%s %s', $lastEntered->date, $lastEntered->time))->calendar();
        }

        $lastExited = Timestamp::whereUserId(Auth::user()->id)
        ->whereEntry(false)->orderBy('date')->orderBy('time')->first();
        $lastExitedString = 'Never';
        if ($lastExited) {
            $lastExitedString = Carbon::parse(sprintf('%s %s', $lastExited->date, $lastExited->time))->calendar();
        }

        return view('home', compact('today',  'currentMonth', 'lastEnteredString', 'lastExitedString'));
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function settings()
    {
        return view('settings');
    }
}
