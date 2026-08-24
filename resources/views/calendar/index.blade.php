@extends('layouts.app')

@section('title', '- Calendar')
@section('page_title', auth()->user()->isClient() ? 'My Content Calendar' : 'Content Calendar')

@section('content')
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    @foreach(\App\Models\Post::STATUS_LABELS as $status => $label)
        <button type="button" class="status-pill on" data-status="{{ $status }}">{{ $label }}</button>
    @endforeach
    <span class="ms-auto small text-muted d-none d-md-inline">
        <i class="bi bi-clock-history"></i> Times shown in your timezone ({{ auth()->user()->timezone }})
    </span>
</div>

<div class="card p-3" style="min-height: 70vh;">
    <div id="calendar"></div>
</div>

<p class="text-muted small mt-3 mb-0">
    <i class="bi bi-hand-index"></i> Click any post to open its preview, then approve, reject or read the full caption.
</p>
@endsection
