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
            <span class="label">Birthed</span>
            <span class="badge green">Source</span>
        </div>
        <div class="stat-value">{{ \App\Models\Pig::where('pig_source', 'birthed')->count() }}</div>
        <div class="stat-sub">Pigs recorded as birthed</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Purchased</span>
            <span class="badge orange">Source</span>
        </div>
        <div class="stat-value">{{ \App\Models\Pig::where('pig_source', 'purchased')->count() }}</div>
        <div class="stat-sub">Pigs recorded as purchased</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="label">Breeds</span>
            <span class="badge red">Variety</span>
        </div>
        <div class="stat-value">{{ \App\Models\Pig::distinct('breed')->count('breed') }}</div>
        <div class="stat-sub">Different pig breeds</div>
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
                    <th>Pen Location</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                @forelse(\App\Models\Pig::latest()->take(5)->get() as $pig)
                    <tr>
                        <td>{{ $pig->ear_tag }}</td>
                        <td>{{ $pig->breed }}</td>
                        <td>{{ ucfirst($pig->sex) }}</td>
                        <td>{{ $pig->pen_location }}</td>
                        <td>{{ ucfirst($pig->pig_source) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-muted">No pigs added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection