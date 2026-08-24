@extends('layouts.app')

@section('title', '- Companies')
@section('page_title', 'Companies')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <form class="d-flex gap-2" method="GET">
        <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Search companies...">
        <button class="btn btn-outline-dark"><i class="bi bi-search"></i></button>
    </form>
    <a href="{{ route('saas.companies.create') }}" class="btn btn-warning">
        <i class="bi bi-plus-lg"></i> New Company
    </a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Plan</th>
                    <th>Trial ends ({{ auth()->user()->timezone }})</th>
                    <th>Team</th>
                    <th>Clients</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($companies as $company)
                    <tr>
                        <td class="fw-bold">{{ $company->name }}
                            <div class="small text-muted">{{ $company->timezone }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $company->status === 'active' ? 'bg-black text-warning' : 'bg-secondary' }}">
                                {{ ucfirst($company->status) }}
                            </span>
                        </td>
                        <td>{{ $company->plan_type ? ucfirst($company->plan_type) : '—' }}
                            <div class="small text-muted">{{ ucfirst($company->subscription_status) }}</div>
                        </td>
                        <td>{{ \App\Support\TimeZone::format($company->trial_ends_at, auth()->user()->timezone) }}</td>
                        <td><span class="badge bg-black">{{ $company->users_count }}</span></td>
                        <td><span class="badge bg-black">{{ $company->clients_count }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('saas.companies.edit', $company) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('saas.companies.destroy', $company) }}" method="POST" class="d-inline"
                                  data-confirm="Delete “{{ $company->name }}”? All its data will be hidden.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-dark" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No companies yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $companies->links() }}</div>
</div>
@endsection
