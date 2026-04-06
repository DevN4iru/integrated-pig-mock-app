@extends('layouts.app')

@section('title', 'Pigs')
@section('page_title', 'Pig List')
@section('page_subtitle', 'View all saved pigs.')

@section('top_actions')
    <a href="{{ route('pigs.create') }}" class="btn primary">Add Pig</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Saved Pig Records</h3>
                <p>Browse all pigs currently stored in the system.</p>
            </div>
        </div>

        @if ($pigs->isEmpty())
            <div class="empty-state">
                No pigs found.
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ear Tag</th>
                            <th>Breed</th>
                            <th>Sex</th>
                            <th>Assigned Pen</th>
                            <th>Source</th>
                            <th>Date Added</th>
                            <th>Weight</th>
                            <th>Asset Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pigs as $pig)
                            <tr>
                                <td>{{ $pig->id }}</td>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ $pig->breed }}</td>
                                <td>{{ ucfirst($pig->sex) }}</td>
                                <td>{{ $pig->pen?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $pig->pig_source === 'birthed' ? 'green' : 'blue' }}">
                                        {{ ucfirst($pig->pig_source) }}
                                    </span>
                                </td>
                                <td>{{ optional($pig->date_added)->format('Y-m-d') }}</td>
                                <td>{{ $pig->latest_weight }}</td>
                                <td>{{ $pig->asset_value }}</td>
                                <td>
                                    <div style="display:grid; gap:8px;">
                                        <button
                                            type="button"
                                            class="btn"
                                            onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')"
                                        >
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-danger"
                                            onclick="togglePigDelete('pig-delete-{{ $pig->id }}')"
                                        >
                                            Delete
                                        </button>

                                        <form
                                            id="pig-delete-{{ $pig->id }}"
                                            method="POST"
                                            action="{{ route('pigs.destroy', $pig) }}"
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
function openPigEditPrompt(url) {
    const code = prompt('Type the edit access code to continue:');
    if (code === null) return;
    if (code !== '12345') {
        alert('Wrong access code.');
        return;
    }
    window.location.href = url + '?code=' + encodeURIComponent(code);
}

function togglePigDelete(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = el.style.display === 'none' ? 'grid' : 'none';
}
@endsection