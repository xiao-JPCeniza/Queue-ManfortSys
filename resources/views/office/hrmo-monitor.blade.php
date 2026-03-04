@extends('layouts.app')

@section('title', 'HRMO Live Queue Monitor')
@section('hide_nav', '1')

@section('content')
    @livewire('office-admin.hrmo-office-monitor', ['office' => $office])
@endsection
