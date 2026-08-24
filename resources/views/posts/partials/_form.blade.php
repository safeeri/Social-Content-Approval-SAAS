@php $isEdit = isset($post); @endphp

<form method="POST" id="postForm"
      action="{{ $isEdit ? route('posts.update', $post) : route('posts.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-sticky"></i> Post details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="client_id" class="form-label">Client</label>
                    <select id="client_id" name="client_id" required class="form-select select2">
                        <option value="">— choose client —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id', $post->client_id ?? '') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="platform_id" class="form-label">Platform</label>
                    <select id="platform_id" name="platform_id" required class="form-select select2">
                        <option value="">— choose platform —</option>
                        @foreach($clients as $client)
                            @foreach($client->platforms as $platform)
                                <option value="{{ $platform->id }}"
                                        data-client-id="{{ $client->id }}"
                                        style="{{ old('client_id', $post->client_id ?? '') == $client->id ? '' : 'display:none;' }}">
                                    {{ $platform->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('platform_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="post_type" class="form-label">Format</label>
                    <select id="post_type" name="post_type" required class="form-select select2">
                        @foreach($postTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('post_type', $post->post_type ?? 'feed') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="publish_date" class="form-label">Publish date &amp; time ({{ auth()->user()->timezone }})</label>
                    <input type="datetime-local" id="publish_date" name="publish_date"
                           value="{{ old('publish_date', $publishLocal ?? '') }}"
                           class="form-control @error('publish_date') is-invalid @enderror">
                    @error('publish_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Stored as UTC, shown to everyone in their own timezone.</small>
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle"></i> Status:
                        @if($isEdit)
                            <span class="badge badge-{{ $post->status }}">{{ \App\Models\Post::STATUS_LABELS[$post->status] }}</span>
                        @else
                            <span class="badge badge-draft">Draft</span>
                        @endif
                    </p>
                </div>

                <div class="col-12">
                    <label for="content" class="form-label">Caption</label>
                    <textarea id="content" name="content" rows="6" required minlength="5"
                              class="form-control @error('content') is-invalid @enderror"
                              placeholder="Write the caption...">{{ old('content', $post->content ?? '') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Media manager: only on edit (a post must exist first) --}}
@if($isEdit)
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-collection-play"></i> Media</div>
        <div class="card-body">

            @if($post->media->count())
                <div class="row g-2 mb-3">
                    @foreach($post->media as $m)
                        <div class="col-6 col-md-3 position-relative">
                            @if($m->drive_link && !$m->file_path)
                                <div class="bg-black text-warning rounded d-flex flex-column align-items-center justify-content-center p-4 h-100 text-decoration-none">
                                    <i class="bi bi-google fs-3"></i>
                                    <small class="mt-1">Drive</small>
                                </div>
                            @elseif($m->file_path && $m->isVideo())
                                <video src="{{ $m->url() }}" muted playsinline class="media-thumb"></video>
                            @else
                                <img src="{{ $m->url() }}" alt="" class="media-thumb">
                            @endif

                            <form method="POST" action="{{ route('media.destroy', [$post, $m]) }}"
                                  data-confirm="Remove this media file?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-dark position-absolute top-0 end-0 m-1" title="Delete media">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            <small class="text-muted d-block mt-1">{{ $m->disk }} · {{ $m->media_type }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small">No media attached yet.</p>
            @endif

            @unless(in_array($post->status, [\App\Models\Post::STATUS_APPROVED, \App\Models\Post::STATUS_PENDING_APPROVAL], true))
                <form method="POST" action="{{ route('media.store') }}" enctype="multipart/form-data" class="border-top pt-3">
                    @csrf
                    <input type="hidden" name="post_id" value="{{ $post->id }}">

                    <div class="mb-3">
                        <label for="files" class="form-label">Upload images / videos / documents</label>
                        <input type="file" id="files" name="files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx"
                               class="form-control @error('files.*') is-invalid @enderror">
                    </div>

                    <div class="mb-3">
                        <label for="drive_link" class="form-label">or paste a Google Drive link</label>
                        <input type="url" id="drive_link" name="drive_link"
                               value="{{ old('drive_link') }}"
                               placeholder="https://drive.google.com/file/d/..."
                               class="form-control @error('drive_link') is-invalid @enderror">
                    </div>

                    @if($errors->hasAny(['files', 'drive_link', 'files.*']))
                        <div class="alert alert-danger py-2 small">
                            {{ $errors->first('files') ?? $errors->first('drive_link') ?? $errors->first('files.*') }}
                        </div>
                    @endif

                    <button type="submit" class="btn btn-outline-dark"><i class="bi bi-cloud-arrow-up"></i> Attach media</button>
                </form>
            @endunless
        </div>
    </div>
@endif
