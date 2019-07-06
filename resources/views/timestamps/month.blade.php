@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('timestamp.months') }}">
                        < Back to Months
                    </a>
                </div>

                <div class="card-body text-center">
                    <h3>
                        <a class="float-left" href="{{ $header['prev']['url'] }}">{{ $header['prev']['display'] }}</a>
                        <b>{{ $header['current']['display'] }}</b>
                        <a class="float-right" href="{{ $header['next']['url'] }}">{{ $header['next']['display'] }}</a>
                    </h3>
                    <hr class="divider">

                    <div class="row">
                        <table class="table table-striped table-bordered">
                            <thead  class="thead-dark">
                                <tr>
                                    @foreach ($weekdays as $w)
                                        <th scope="col">{{ $w }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $week)
                                    <tr scope="row">
                                    @for ($i = 0; $i < $offset; $i++)
                                        <td> - </td>
                                    @endfor
                                    @php
                                        $offset = 0
                                    @endphp
                                        @foreach($week as $day)
                                            <td>
                                                <a href="{{ route('timestamp.day', $day->format('Y-m-d')) }}"
                                                   class="{{ $day == $today ? 'font-weight-bold' : 'font-weight-normal' }}
                                                   {{ App\Utils\Calculator::stateClass($day, Auth::user()) }}">
                                                    {{ $day->day }}
                                                </a>
                                            </td>
                                        @endforeach
                                        @if ($day->dayOfWeek === 6)
                                            @continue
                                        @endif
                                        @for ($i = 0; $i < 7-count($week); $i++)
                                            <td> - </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
