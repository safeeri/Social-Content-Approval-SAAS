@extends('layouts.app')

@section('title', '- Team')
@section('page_title', 'Team & Client Logins')

@section('content')
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <a href="{{ route('team.index') }}" class="btn btn-sm {{ !$activeRole ? 'btn-warning' : 'btn-outline-dark' }}">All</a>
    @foreach($roles as $role)
        <a href="{{ route('team.index', ['role' => $role]) }}"
           class="btn btn-sm {{ $activeRole === $role ? 'btn-warning' : 'btn-outline-dark' }}">
            {{ str_replace('_', ' ', $role) }}
        </a>
    @endforeach
    <a href="{{ route('team.create') }}" class="btn btn-warning ms-auto"><i class="bi bi-person-plus"></i> Add User</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>User</th><th>Role</th><th>Attached client</th><th>Timezone</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="fw-bold">
                            {{ $user->name }}
                            <div class="small text-muted">{{ $user->email }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $user->role === 'client' ? 'bg-warning text-dark' : ($user->role === 'company_admin' ? 'bg-black text-white' : 'bg-black text-warning') }}">
                                {{ str_replace('_', ' ', $user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->client?->name ?? '—' }}</td>
                        <td>{{ $user->timezone }}</td>
                        <td class="text-end">
                            <a href="{{ route('team.edit', $user) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            @unless($user->id === auth()->id())
                                <form action="{{ route('team.destroy', $user) }}" method="POST" class="d-inline"
                                      data-confirm="Delete user “{{ $user->name }}”?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-dark"><i class="bi bi-trash"></i></button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $users->links() }}</div>
</div>
@endsection
