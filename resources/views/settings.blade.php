@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">App Settings</div>

                <div class="card-body">
                    <form action="{{ route('app.settings') }}" method="POST">
                        @foreach ($inputs as $in)
                            <div class="form-group">
                                <label for="{{ $in['name'] }}">{{ $in['display'] }}</label>
                                <input class="form-control" type="{{ $in['type'] }}" name="{{ $in['name'] }}">
                            </div>
                        @endforeach
                        @csrf
                        <button class="btn btn-primary" type="submit">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
