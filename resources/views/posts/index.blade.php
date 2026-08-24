@extends('layouts.app')

@section('title', '- Posts')
@section('page_title', 'Posts')

@section('content')
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <form class="d-flex gap-2" method="GET">
        <select name="status" class="form-select select2" style="min-width: 180px;">
            <option value="">All statuses</option>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected($activeStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-outline-dark"><i class="bi bi-funnel"></i></button>
    </form>
    @if(auth()->user()->isManager() || auth()->user()->isCompanyAdmin())
        <a href="{{ route('posts.create') }}" class="btn btn-warning ms-auto">
            <i class="bi bi-plus-lg"></i> New Post
        </a>
    @endif
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Scheduled ({{ auth()->user()->timezone }})</th>
                    <th>Caption</th>
                    <th>Client</th><th>Platform</th><th>Format</th>
                    <th>Status</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td>{{ \App\Support\TimeZone::format($post->publish_date, auth()->user()->timezone, 'M j, Y g:i A') }}</td>
                        <td style="max-width: 320px;">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 70) }}</td>
                        <td>{{ $post->client->name }}</td>
                        <td><span class="badge bg-black">{{ $post->platform->name }}</span></td>
                        <td>{{ \App\Models\Post::TYPES[$post->post_type] }}</td>
                        <td><span class="badge badge-{{ $post->status }}">{{ \App\Models\Post::STATUS_LABELS[$post->status] }}</span></td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-warning"
                                    data-post-url="{{ route('posts.preview', $post) }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if(auth()->user()->isManager() || auth()->user()->isCompanyAdmin())
                                <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-pencil"></i></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No posts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $posts->links() }}</div>
</div>
@endsection
