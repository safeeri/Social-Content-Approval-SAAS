@extends('layouts.app')

@section('title', '- Clients')
@section('page_title', 'Clients')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <form class="d-flex gap-2" method="GET">
        <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search name or phone...">
        <button class="btn btn-outline-dark"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('clients.create') }}" class="btn btn-warning"><i class="bi bi-plus-lg"></i> New Client</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Client</th><th>Phone</th><th>Platforms</th>
                    <th>Posts</th><th>Logins</th><th>Client since</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td class="fw-bold">
                            {{ $client->name }}
                            @if($client->website)
                                <a href="{{ $client->website }}" target="_blank" rel="noopener" class="d-block small text-muted text-decoration-none">
                                    <i class="bi bi-globe2"></i> {{ str_replace(['https://','http://'], '', $client->website) }}
                                </a>
                            @endif
                        </td>
                        <td>{{ $client->phone }}</td>
                        <td>
                            @foreach($client->platforms as $platform)
                                <span class="badge bg-black">{{ $platform->name }}</span>
                            @endforeach
                        </td>
                        <td><span class="badge bg-black">{{ $client->posts_count }}</span></td>
                        <td><span class="badge bg-warning text-dark">{{ $client->logins_count }}</span></td>
                        <td>{{ \App\Support\TimeZone::format($client->created_at, auth()->user()->timezone, 'M j, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline"
                                  data-confirm="Delete client “{{ $client->name }}”? Their posts will be hidden.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-dark"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No clients yet — add your first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $clients->links() }}</div>
</div>
@endsection
