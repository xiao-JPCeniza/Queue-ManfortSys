@extends('layouts.app')

@section('title', $office->name . ' ' . $office->serviceWindowDisplayTitle($windowNumber))
@section('full_width', '1')

@section('content')
    @livewire('office-admin.window-desk', ['office' => $office, 'windowNumber' => $windowNumber])
@endsection
