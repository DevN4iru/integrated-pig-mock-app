@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Welcome to the shared Pigstep application shell.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}">View Pigs</a> |
    <a href="{{ route('pigs.create') }}">Create Pig</a>
@endsection

@section('content')
    <p>This is a placeholder dashboard page.</p>
@endsection
