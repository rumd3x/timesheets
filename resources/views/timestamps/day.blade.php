@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body text-center">
                    <h3>
                        <a class="float-left" href="{{ url('/timestamps/day/'.$header['prev']->format('Y-m-d')) }}">{{ $header['prev']->format('l F dS, Y') }}</a>
                        <b>{{ $header['current']->format('l F dS, Y') }}</b>
                        <a class="float-right" href="{{ url('/timestamps/day/'.$header['next']->format('Y-m-d')) }}">{{ $header['next']->format('l F dS, Y') }}</a>
                    </h3>
                    <hr class="divider">

                    <div class="row">

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
