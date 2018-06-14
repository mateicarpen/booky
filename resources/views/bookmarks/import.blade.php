@extends('layouts.app')

@section('content')

    <div class="container spark-screen">
        <div class="panel panel-default">
            <div class="panel-heading">
                Import Bookmarks
            </div>

            <div class="panel-body">
                @if (!$done)

                    <form method="POST" action="{{ url('/bookmarks/import') }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="input-group">
                            <input type="file" class="form-control" name="file">
                            <span class="input-group-btn">
                                <input type="submit" value="Go" class = "btn btn-default"/>
                            </span>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger import-error">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </form>

                @elseif ($error)

                    There was an error trying to import your file. Please try again with a different export file.

                @else

                    Done.

                @endif
            </div>
        </div>
    </div>

@stop