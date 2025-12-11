@extends('dynamicauth::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('dynamicauth.name') !!}</p>
@endsection
