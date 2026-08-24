@extends('layouts.app')

@section('title', '- New Post')
@section('page_title', 'New Post')

@section('content')
@include('posts.partials._form')

<button form="postForm" type="submit" class="btn btn-warning">
    <i class="bi bi-check-lg"></i> Create draft
</button>
<a href="{{ route('posts.index') }}" class="btn btn-outline-dark">Cancel</a>
@endsection

@push('scripts')
<script>
    // Show only the platforms enabled for the selected client.
    const clientSelect = document.getElementById('client_id');
    const platformSelect = document.getElementById('platform_id');

    function syncPlatforms() {
        let firstVisible = '';
        [...platformSelect.options].forEach(opt => {
            if (!opt.dataset.clientId) return;
            const visible = opt.dataset.clientId === clientSelect.value;
            opt.style.display = visible ? '' : 'none';
            if (visible && !firstVisible) firstVisible = opt.value;
            if (!visible && opt.selected) { opt.selected = false; }
        });
        platformSelect.value = firstVisible || '';
        jQuery(platformSelect).trigger('change.select2');
    }

    clientSelect.addEventListener('change', syncPlatforms);
    syncPlatforms();
</script>
@endpush
