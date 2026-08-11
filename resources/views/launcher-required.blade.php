@extends('layouts.app')

@section('title', 'Launcher Required')

@section('content')
    <div id="launcher-app" class="contents"></div>

    @vite(['resources/js/launcher.js'])
@endsection
