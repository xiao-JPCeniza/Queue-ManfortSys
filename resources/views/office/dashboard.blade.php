@extends('layouts.app')

@section('title', $office->name . ' Queue')

@section('content')
    @livewire('office-admin.dashboard', ['office' => $office])
@endsection
