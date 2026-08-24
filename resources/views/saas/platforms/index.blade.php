@extends('layouts.app')

@section('title', '- Platforms')
@section('page_title', 'Platforms')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Global social platforms available to all tenants.</p>
    <a href="{{ route('saas.platforms.create') }}" class="btn btn-warning">
        <i class="bi bi-plus-lg"></i> New Platform
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Platform</th><th>Icon</th><th>Posts using it</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($platforms as $platform)
                    <tr>
                        <td class="fw-bold">{{ $platform->name }}</td>
                        <td>@if($platform->icon_url)<img src="{{ $platform->icon_url }}" width="28" height="28" alt="">@else<i class="bi bi-share fs-5"></i>@endif</td>
                        <td><span class="badge bg-black">{{ $platform->posts_count }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('saas.platforms.edit', $platform) }}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('saas.platforms.destroy', $platform) }}" method="POST" class="d-inline"
                                  data-confirm="Delete “{{ $platform->name }}”?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-dark"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No platforms yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $platforms->links() }}</div>
</div>
@endsection
