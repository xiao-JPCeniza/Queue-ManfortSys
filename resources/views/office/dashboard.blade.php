@extends('layouts.app')

@section('title', request()->routeIs('super-admin.reports')
    ? 'Reports'
    : (request()->routeIs('super-admin.queue-management')
        ? 'Queue Management'
        : (auth()->user()?->isSuperAdmin() && request()->routeIs('super-admin.*') ? 'Super Admin Queue' : $office->name . ' Queue')))

@section('content')
    @livewire('office-admin.dashboard', ['office' => $office])
@endsection
