@extends('layouts.app')

@section('title', 'Pigs')
@section('page_title', 'Pig List')
@section('page_subtitle', 'View all saved pigs.')

@section('top_actions')
    <a href="{{ route('pigs.create') }}">Create Pig</a>
@endsection

@section('content')
    @if ($pigs->isEmpty())
        <p>No pigs found.</p>
    @else
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ear Tag</th>
                    <th>Breed</th>
                    <th>Sex</th>
                    <th>Pen Location</th>
                    <th>Status</th>
                    <th>Latest Weight</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pigs as $pig)
                    <tr>
                        <td>{{ $pig->id }}</td>
                        <td>{{ $pig->ear_tag }}</td>
                        <td>{{ $pig->breed }}</td>
                        <td>{{ $pig->sex }}</td>
                        <td>{{ $pig->pen_location }}</td>
                        <td>{{ $pig->status }}</td>
                        <td>{{ $pig->latest_weight }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
