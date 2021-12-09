@extends('coinv2::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('coinv2.name') !!}
    </p>
@endsection
