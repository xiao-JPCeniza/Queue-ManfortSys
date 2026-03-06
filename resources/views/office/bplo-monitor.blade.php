@extends('layouts.app')

@section('title', 'BPLO Live Queue Monitor')
@section('hide_nav', '1')

@section('content')
    @livewire('office-admin.bplo-office-monitor', ['office' => $office])
@endsection
