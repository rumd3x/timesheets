@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    <h2><b>Today:</b> {{ $today }}</h2>
                    <hr class="divider">
                    <p>Last Entered: {{ $lastEnteredString  }}</p>
                    <p>Last Exited: {{ $lastExitedString  }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
