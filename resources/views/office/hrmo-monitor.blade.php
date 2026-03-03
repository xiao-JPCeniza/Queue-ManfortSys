@extends('layouts.app')

@section('title', 'HRMO Live Queue Monitor')

@section('content')
    @livewire('office-admin.hrmo-office-monitor', ['office' => $office])
@endsection

