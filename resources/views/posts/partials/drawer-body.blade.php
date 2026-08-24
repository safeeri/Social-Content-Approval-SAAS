@php
    $user = auth()->user();
    $isPending = $post->status === \App\Models\Post::STATUS_PENDING_APPROVAL;
    $inInternalReview = $post->status === \App\Models\Post::STATUS_INTERNAL_REVIEW;
    $canSubmit = in_array($post->status, [\App\Models\Post::STATUS_DRAFT, \App\Models\Post::STATUS_REJECTED], true)
        && ($user->isManager() || $user->isCompanyAdmin());
@endphp

<span data-drawer-title class="d-none">{{ \App\Models\Post::TYPES[$post->post_type] }} · {{ $post->platform->name }}</span>

<div class="d-flex justify-content-between align-items-start mb-3">
    <span class="badge badge-{{ $post->status }} fs-6">{{ \App\Models\Post::STATUS_LABELS[$post->status] }}</span>
    @if($post->publish_date)
        <small class="text-muted">
            <i class="bi bi-calendar-event"></i>
            {{ \App\Support\TimeZone::format($post->publish_date, $user->timezone) }}
        </small>
    @endif
</div>

{{-- Media gallery --}}
@if($post->media->count())
    <div class="row g-2 mb-3" data-full-caption="{{ e($post->content.($post->client->platform_bottom_content ? "\n\n".$post->client->platform_bottom_content : '')) }}">
        @foreach($post->media as $m)
            @if($m->drive_link && !$m->file_path)
                <div class="col-4">
                    <a href="{{ $m->drive_link }}" target="_blank" rel="noopener"
                       class="d-flex flex-column align-items-center justify-content-center bg-black text-warning rounded p-3 text-decoration-none h-100">
                        <i class="bi bi-google fs-3"></i>
                        <small class="mt-1">Drive video</small>
                    </a>
                </div>
            @elseif($m->file_path && $m->isVideo())
                <div class="col-4 position-relative">
                    <video src="{{ $m->url() }}" muted playsinline class="media-thumb"></video>
                    <button type="button" class="btn btn-sm btn-warning position-absolute top-0 end-0 m-1"
                            data-expand="{{ $m->url() }}" data-media-type="video" title="Expand">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
            @elseif($m->file_path)
                <div class="col-4 position-relative">
                    <img src="{{ $m->url() }}" alt="" class="media-thumb">
                    @if(!$m->isVideo())
                        <button type="button" class="btn btn-sm btn-warning position-absolute top-0 end-0 m-1"
                                data-expand="{{ $m->url() }}" data-media-type="image" title="Expand">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
@endif

{{-- Caption (client-level footer hashtags appended) --}}
<h6 class="text-uppercase small fw-bold text-muted">Caption</h6>
<div id="captionText" class="caption-box mb-3">{{ $post->content }}@if($post->client->platform_bottom_content)

{{ $post->client->platform_bottom_content }}@endif</div>

<dl class="row small mb-3">
    <dt class="col-5">Client</dt><dd class="col-7">{{ $post->client->name }}</dd>
    <dt class="col-5">Platform</dt><dd class="col-7"><i class="bi bi-share"></i> {{ $post->platform->name }}</dd>
    <dt class="col-5">Format</dt><dd class="col-7">{{ \App\Models\Post::TYPES[$post->post_type] }}</dd>
</dl>

{{-- Rejection history --}}
@if($post->feedback->count())
    <h6 class="text-uppercase small fw-bold text-danger">Client feedback</h6>
    @foreach($post->feedback as $fb)
        <div class="border rounded p-2 mb-2 bg-light">
            <small class="text-muted d-block mb-1">
                <i class="bi bi-chat-left-dots"></i> {{ $fb->user?->name ?? 'Client' }}
                · {{ \App\Support\TimeZone::format($fb->created_at, $user->timezone) }}
            </small>
            {{ $fb->comment }}
        </div>
    @endforeach
@endif

{{-- Workflow actions --}}
<hr>
<div class="d-grid gap-2">

    {{-- Client approves / rejects --}}
    @if($user->isClient() && $isPending)
        <form method="POST" action="{{ route('posts.approve', $post) }}">
            @csrf
            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">
                <i class="bi bi-check2-circle"></i> Approve post
            </button>
        </form>

        <button type="button" class="btn btn-outline-dark w-100" data-bs-toggle="collapse" data-bs-target="#rejectForm">
            <i class="bi bi-x-circle"></i> Reject with feedback
        </button>
        <div class="collapse {{ $errors->has('comment') ? 'show' : '' }}" id="rejectForm">
            <form method="POST" action="{{ route('posts.reject', $post) }}" class="border-top pt-3 mt-1">
                @csrf
                <label for="comment" class="form-label small fw-bold">Why are you rejecting this post?</label>
                <textarea name="comment" id="comment" rows="3" required minlength="10"
                          class="form-control @error('comment') is-invalid @enderror"
                          placeholder="Explain what needs to change...">@error('comment'){{ old('comment') ?: $message }}@else{{ old('comment') }}@enderror</textarea>
                @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-warning w-100 mt-2 fw-bold">Send rejection</button>
            </form>
        </div>
    @endif

    {{-- Approver internal decision --}}
    @if($user->isApprover() && $inInternalReview)
        <div class="row g-2">
            <div class="col-6">
                <form method="POST" action="{{ route('posts.review', $post) }}">
                    @csrf
                    <input type="hidden" name="decision" value="approve">
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">
                        <i class="bi bi-check2-square"></i> Sign off → client
                    </button>
                </form>
            </div>
            <div class="col-6">
                <form method="POST" action="{{ route('posts.review', $post) }}">
                    @csrf
                    <input type="hidden" name="decision" value="reject">
                    <button type="submit" class="btn btn-warning w-100 py-2 fw-bold">
                        <i class="bi bi-arrow-counterclockwise"></i> Back to draft
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Manager submits for review --}}
    @if($canSubmit)
        <form method="POST" action="{{ route('posts.submit-review', $post) }}">
            @csrf
            <button type="submit" class="btn btn-warning w-100 py-2 fw-bold">
                <i class="bi bi-send"></i> Submit for internal review
            </button>
        </form>
    @endif

    @unless($user->isClient())
        <a href="{{ route(($user->isManager() || $user->isCompanyAdmin()) ? 'posts.edit' : 'posts.index') }}"
           class="btn btn-outline-dark w-100">
            <i class="bi bi-pencil"></i> Open workspace
        </a>
    @endunless
</div>
