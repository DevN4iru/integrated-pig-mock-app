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
                            <th>Actions</th>
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
                                <td>
                                    <div style="display:grid; gap:8px;">
                                        <button
                                            type="button"
                                            class="btn"
                                            onclick="openPenEditPrompt('{{ route('pens.edit', $pen) }}')"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            onclick="togglePenDelete('pen-delete-{{ $pen->id }}')"
                                        >
                                            Delete
                                        </button>

                                        <form
                                            id="pen-delete-{{ $pen->id }}"
                                            method="POST"
                                            action="{{ route('pens.destroy', $pen) }}"
                                            style="display:none; gap:8px;"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <input type="text" name="confirm_code" placeholder="type DELETE" required>
                                            <button type="submit" class="btn btn-danger">Confirm Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
function openPenEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong access code.');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}

function togglePenDelete(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'grid' : 'none';
}
@endsection