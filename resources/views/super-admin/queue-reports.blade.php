@extends('layouts.app')

@section('title', 'Queue Reports')

@section('content')
    @livewire('office-admin.dashboard', ['office' => $office])
@endsection

