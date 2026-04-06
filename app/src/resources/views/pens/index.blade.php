@extends('layouts.app')

@section('title', 'Pens')
@section('page_title', 'Pen List')
@section('page_subtitle', 'View all housing pens in the system.')

@section('top_actions')
    <a href="{{ route('pens.create') }}" class="btn primary">Add Pen</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Saved Pens</h3>
                <p>Track pen type, capacity, and notes.</p>
            </div>
        </div>

        @if ($pens->isEmpty())
            <div class="empty-state">
                No pens found.
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pens as $pen)
                            <tr>
                                <td>{{ $pen->id }}</td>
                                <td>{{ $pen->name }}</td>
                                <td>{{ $pen->type }}</td>
                                <td>{{ $pen->capacity }}</td>
                                <td>{{ $pen->pigs_count }}</td>
                                <td>{{ max($pen->capacity - $pen->pigs_count, 0) }}</td>
                                <td>{{ $pen->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection