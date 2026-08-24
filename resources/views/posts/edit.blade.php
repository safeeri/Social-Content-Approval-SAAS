@extends('layouts.app')

@section('title', '- Edit Post')
@section('page_title', 'Edit Post')

@section('content')
@include('posts.partials._form')

<div class="d-flex flex-wrap gap-2">
    @if(in_array($post->status, [\App\Models\Post::STATUS_DRAFT, \App\Models\Post::STATUS_REJECTED], true))
        <button form="postForm" type="submit" class="btn btn-warning">
            <i class="bi bi-check-lg"></i> Save changes
        </button>
        <form method="POST" action="{{ route('posts.submit-review', $post) }}">
            @csrf
            <button type="submit" class="btn btn-dark">
                <i class="bi bi-send"></i> Save &amp; submit for review
            </button>
        </form>
    @else
        <button form="postForm" type="submit" class="btn btn-warning" disabled title="Only drafts or rejected posts can be edited">
            <i class="bi bi-lock"></i> Locked ({{ \App\Models\Post::STATUS_LABELS[$post->status] }})
        </button>
    @endif
    <a href="{{ route('posts.index') }}" class="btn btn-outline-dark">Back to posts</a>

    @unless($post->status === 'approved')
        <form method="POST" action="{{ route('posts.destroy', $post) }}" data-confirm="Delete this post permanently?" class="ms-auto">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-dark"><i class="bi bi-trash"></i> Delete</button>
        </form>
    @endunless
</div>
@endsection

@push('scripts')
<script>
    const clientSelect = document.getElementById('client_id');
    const platformSelect = document.getElementById('platform_id');

    function syncPlatforms() {
        [...platformSelect.options].forEach(opt => {
            if (!opt.dataset.clientId) return;
            opt.style.display = opt.dataset.clientId === clientSelect.value ? '' : 'none';
            if (opt.dataset.clientId !== clientSelect.value && opt.selected) opt.selected = false;
        });
        jQuery(platformSelect).trigger('change.select2');
    }

    clientSelect.addEventListener('change', syncPlatforms);
</script>
@endpush
