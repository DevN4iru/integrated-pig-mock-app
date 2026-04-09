@extends('layouts.app')

@section('title', 'Pigs')
@section('page_title', 'Pig List')
@section('page_subtitle', 'View active and archived pig records.')

@section('top_actions')
    <a href="{{ route('pigs.create') }}" class="btn primary">Add Pig</a>
@endsection

@section('content')
    <div class="panel-card">
        <div class="section-title">
            <div>
                <h3>Search and Filter</h3>
                <p>Find pigs by ear tag, breed, pen, source, or filter by record status.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('pigs.index') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input
                        id="search"
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Ear tag, breed, pen, sex, or source"
                    >
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="sold" {{ $status === 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="dead" {{ $status === 'dead' ? 'selected' : '' }}>Dead</option>
                        <option value="archived" {{ $status === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="source">Source</label>
                    <select id="source" name="source">
                        <option value="all" {{ $source === 'all' ? 'selected' : '' }}>All sources</option>
                        <option value="birthed" {{ $source === 'birthed' ? 'selected' : '' }}>Birthed</option>
                        <option value="purchased" {{ $source === 'purchased' ? 'selected' : '' }}>Purchased</option>
                    </select>
                </div>

                <div class="form-group full">
                    <div class="form-actions">
                        <button type="submit" class="btn primary">Apply Filters</button>
                        <a href="{{ route('pigs.index') }}" class="btn">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="panel-card" style="margin-top: 20px;">
        <div class="section-title">
            <div>
                <h3>Active Pig Records</h3>
                <p>These pigs are currently part of the active system list.</p>
            </div>
        </div>

        @if ($activePigs->isEmpty())
            <div class="empty-state">No active pigs found for the current filters.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ear Tag</th>
                            <th>Breed</th>
                            <th>Sex</th>
                            <th>Assigned Pen</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Weight</th>
                            <th>Asset Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activePigs as $pig)
                            @php
                                $isDead = $pig->mortalityLogs->isNotEmpty();
                                $isSold = $pig->sales->isNotEmpty();
                                $statusLabel = $isDead ? 'Dead' : ($isSold ? 'Sold' : 'Active');
                                $statusBadgeClass = $isDead ? 'red' : ($isSold ? 'orange' : 'green');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ $pig->breed }}</td>
                                <td>{{ ucfirst($pig->sex) }}</td>
                                <td>{{ $pig->pen?->name ?? ($pig->pen_location ?? '—') }}</td>
                                <td>
                                    <span class="badge {{ $pig->pig_source === 'birthed' ? 'green' : 'blue' }}">
                                        {{ ucfirst($pig->pig_source) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td>{{ $pig->date_added }}</td>
                                <td>{{ number_format((float) $pig->latest_weight, 2) }} kg</td>
                                <td>₱ {{ number_format((float) $pig->asset_value, 2) }}</td>
                                <td>
                                    <div style="display:grid; gap:8px;">
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View</a>

                                        <button type="button" class="btn"
                                            onclick="openPigEditPrompt('{{ route('pigs.edit', $pig) }}')">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('pigs.destroy', $pig->id) }}"
                                            onsubmit="return confirm('Archive this pig? It will be removed from the active list but can still be restored later.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-warning">Archive</button>
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

    <div class="panel-card" style="margin-top: 20px;">
        <div class="section-title">
            <div>
                <h3>Archived Pig Records</h3>
                <p>Archived pigs are hidden from the active list but their history is still preserved.</p>
            </div>
        </div>

        @if ($archivedPigs->isEmpty())
            <div class="empty-state">No archived pigs found for the current filters.</div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ear Tag</th>
                            <th>Breed</th>
                            <th>Sex</th>
                            <th>Assigned Pen</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Weight</th>
                            <th>Asset Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($archivedPigs as $pig)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pig->ear_tag }}</td>
                                <td>{{ $pig->breed }}</td>
                                <td>{{ ucfirst($pig->sex) }}</td>
                                <td>{{ $pig->pen?->name ?? ($pig->pen_location ?? '—') }}</td>
                                <td>
                                    <span class="badge {{ $pig->pig_source === 'birthed' ? 'green' : 'blue' }}">
                                        {{ ucfirst($pig->pig_source) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge blue">Archived</span>
                                </td>
                                <td>{{ $pig->date_added }}</td>
                                <td>{{ number_format((float) $pig->latest_weight, 2) }} kg</td>
                                <td>₱ {{ number_format((float) $pig->asset_value, 2) }}</td>
                                <td>
                                    <div style="display:grid; gap:8px;">
                                        <a href="{{ route('pigs.show', $pig->id) }}" class="btn">View</a>

                                        <form method="POST" action="{{ route('pigs.restore', $pig->id) }}"
                                            onsubmit="return confirm('Restore this pig back to the active list?');">
                                            @csrf
                                            <button type="submit" class="btn">Restore</button>
                                        </form>

                                        <button type="button" class="btn btn-danger"
                                            onclick="confirmPigPermanentDelete('{{ route('pigs.force-delete', $pig->id) }}')">
                                            Permanently Delete
                                        </button>
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

function confirmPigPermanentDelete(url) {
    const code = prompt('Permanent delete will erase this pig and its related records forever.\n\nEnter challenge code 12345 to continue:');
    if (code === null) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';

    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';

    const codeInput = document.createElement('input');
    codeInput.type = 'hidden';
    codeInput.name = 'code';
    codeInput.value = code;

    form.appendChild(csrf);
    form.appendChild(method);
    form.appendChild(codeInput);

    document.body.appendChild(form);
    form.submit();
}
@endsection
