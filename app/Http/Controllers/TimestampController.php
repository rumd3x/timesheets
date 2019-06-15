<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Timestamp;

class TimestampController extends Controller
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

    public function index(int $currentYear =  0)
    {
        if (!$currentYear) {
            $currentYear = Carbon::now()->format('Y');
        }
        $prevYear = Carbon::createFromFormat('Y', $currentYear)->subYear()->format('Y');
        $nextYear = Carbon::createFromFormat('Y', $currentYear)->addYear()->format('Y');

        $currentMonth = Carbon::now()->format('F');

        $months = [];
        for ($i=0; $i < 12; $i++) {
            $months[$i+1] = Carbon::parse('first day of january '.$currentYear)->addMonths($i)->format('F');
        }

        return view('timestamps.months', compact('months', 'currentMonth', 'currentYear', 'prevYear', 'nextYear'));
    }

    public function month(int $year, int $month)
    {
        $today = Carbon::today();
        $currentMonth = Carbon::parse(sprintf('%d-%d-01', $year, $month));
        $prevMonth = Carbon::parse(sprintf('%d-%d-01', $year, $month))->subMonth();
        $nextMonth = Carbon::parse(sprintf('%d-%d-01', $year, $month))->addMonth();
        $header = [
            'prev' => [
                'url' => url(sprintf('/timestamps/%d/month/%d', $prevMonth->year, $prevMonth->month)),
                'display' => $prevMonth->format('Y F'),
            ],
            'current' => [
                'url' => url(sprintf('/timestamps/%d/month/%d', $currentMonth->year, $currentMonth->month)),
                'display' => $currentMonth->format('Y F'),
            ],
            'next' => [
                'url' => url(sprintf('/timestamps/%d/month/%d', $nextMonth->year, $nextMonth->month)),
                'display' => $nextMonth->format('Y F'),
            ],
        ];


        $data = [];
        $weekdays = [
            Carbon::parse('next sunday')->format('D'),
            Carbon::parse('next monday')->format('D'),
            Carbon::parse('next tuesday')->format('D'),
            Carbon::parse('next thursday')->format('D'),
            Carbon::parse('next wednesday')->format('D'),
            Carbon::parse('next friday')->format('D'),
            Carbon::parse('next saturday')->format('D'),
        ];

        $week = 0;
        for ($i=1; $i <= 31; $i++) {
            $day = Carbon::parse(sprintf('%d-%d-%d', $year, $month, $i));
            if ($day->format('n') != $month) {
                break;
            }
            $data[$week][] = $day;
            if ($day->format('w') == 6) {
                $week++;
            }
        }


        $offset = $data[0][0]->format('w');

        return view('timestamps.month', compact('header', 'today', 'weekdays', 'data', 'offset'));
    }

    public function day(string $day){
        $day = Carbon::parse($day);

        $header = [
            'prev' => Carbon::parse($day)->subDay(),
            'current' => $day,
            'next' => Carbon::parse($day)->addDay(),
        ];

        $timestamps = Timestamp::where('date', $day->format('Y-m-d'))->orderBy('time')->get();

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

        return view('timestamps.day', compact('header', 'timestamps', 'totalTime'));
    }
}
