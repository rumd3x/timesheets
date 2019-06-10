@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

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
                                        <?php
                                            for ($i=0; $i < $offset; $i++) {
                                                echo '<td> - </td>';
                                            }
                                            $offset = 0;
                                        ?>
                                        @foreach($week as $day)
                                            <td>{{ $day->format('d') }}</td>
                                        @endforeach
                                        <?php
                                            if ($day->format('w') == 6) {
                                                continue;
                                            }
                                            for ($i=0; $i < 7-count($week); $i++) {
                                                echo '<td> - </td>';
                                            }
                                        ?>
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
