@extends('layouts.app')

@section('content')

<div id="app">
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-3 treeview"></div>
            <div class="col-md-9">
                <listing-page></listing-page>
            </div>
        </div>
    </div>
</div>

<script>
    window.apiToken = '{{ $currentUser->api_token }}';
</script>

@stop