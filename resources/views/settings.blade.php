@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">API</div>

                <div class="card-body">
                    <label for="txtApi">Your API Key</label>
                    <div class="input-group">
                        <input class="form-control" type="password" name="api" id="txtApi" value="{{ Auth::user()->api_key }}" onclick="this.select()">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" onclick="document.getElementById('txtApi').type = 'text;'">Show Key</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
