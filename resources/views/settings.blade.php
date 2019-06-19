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

                            @if ($in['type'] == 'file')
                                <div class="custom-file mb-4">
                                    <label class="custom-file-label" for="{{ $in['name'] }}">{{ $in['display'] }}</label>
                                    <input type="file" class="custom-file-input" id="{{ $in['name'] }}">
                                </div>

                                <script>
                                    $("#{{ $in['name'] }}").on("change", function() {
                                        var fileName = $(this).val().split("\\").pop();
                                        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
                                    });
                                </script>
                                @continue
                            @endif

                            <div class="form-group">
                                <label for="{{ $in['name'] }}">{{ $in['display'] }}</label>
                                <input class="form-control" type="{{ $in['type'] }}" name="{{ $in['name'] }}" id="{{ $in['name'] }}">
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
