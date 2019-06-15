@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    <h3 class="text-center">
                        <a class="float-left" href="{{ url('/timestamps/day/'.$header['prev']->format('Y-m-d')) }}">{{ $header['prev']->format('l F dS, Y') }}</a>
                        <b>{{ $header['current']->format('l F dS, Y') }}</b>
                        <a class="float-right" href="{{ url('/timestamps/day/'.$header['next']->format('Y-m-d')) }}">{{ $header['next']->format('l F dS, Y') }}</a>
                    </h3>
                    <hr class="divider">

                    <div class="row">
                        <div class="col-md-12">

                            <p><b>Total Time:</b> {{ floor($totalTime / 60) }} Hour(s) and {{ $totalTime - floor($totalTime / 60) * 60 }} Minute(s)</p>
                            <p><b>Centesimal Time:</b> {{ sprintf("%.2f", $totalTime / 60) }} Hour(s)</p>
                            <ul class="list-group list-group-flush">
                                @forelse ($timestamps as $t)
                                    <li class="list-group-item list-group-item-action {{ $t->entry ? '' : 'list-group-item-secondary' }}">
                                        - {{ $t->entry ? 'Entered' : 'Exited'}} {{ Carbon\Carbon::parse("$t->date $t->time")->format('g:iA') }}
                                    </li>
                                @empty
                                    <li class="list-group-item list-group-item-action">
                                        No Timestamps today
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
