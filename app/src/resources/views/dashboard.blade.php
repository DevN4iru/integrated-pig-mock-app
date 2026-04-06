@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview, reminders, and daily visibility for pig farm operations.')

@section('top_actions')
    <a href="{{ route('pigs.index') }}" class="btn">View Pigs</a>
    <a href="{{ route('pigs.create') }}" class="btn primary">+ Add Pig</a>
@endsection

@section('content')

<div class="grid stats">
    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Total Pigs</span>
            <span class="badge blue">Live</span>
        </div>
        <div class="stat-value">{{ \App\Models\Pig::count() }}</div>
        <div class="stat-sub">Total pigs in the system</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Active Pigs</span>
            <span class="badge green">Healthy</span>
        </div>
        <div class="stat-value">{{ \App\Models\Pig::where('status','active')->count() }}</div>
        <div class="stat-sub">Currently active pigs</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Breeds</span>
            <span class="badge orange">Variety</span>
        </div>
        <div class="stat-value">{{ \App\Models\Pig::distinct('breed')->count('breed') }}</div>
        <div class="stat-sub">Different pig breeds</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Records</span>
            <span class="badge red">Tracking</span>
        </div>
        <div class="stat-value">{{ \App\Models\Pig::count() }}</div>
        <div class="stat-sub">Total entries recorded</div>
    </div>
</div>

<div class="panel-card">
    <div class="section-title">
        <h3>Recent Pigs</h3>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ear Tag</th>
                    <th>Breed</th>
                    <th>Sex</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\Pig::latest()->take(5)->get() as $pig)
                <tr>
                    <td>{{ $pig->ear_tag }}</td>
                    <td>{{ $pig->breed }}</td>
                    <td>{{ $pig->sex }}</td>
                    <td>{{ $pig->status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection