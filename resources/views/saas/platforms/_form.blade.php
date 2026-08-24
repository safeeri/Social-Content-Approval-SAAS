@extends('layouts.app')

@section('title', '- Platform')
@section('page_title', isset($platform) ? 'Edit: '.$platform->name : 'New Platform')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-share"></i> Platform details</div>
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($platform) ? route('saas.platforms.update', $platform) : route('saas.platforms.store') }}">
                    @csrf
                    @isset($platform) @method('PUT') @endisset

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" required value="{{ old('name', $platform->name ?? '') }}"
                               placeholder="Instagram, TikTok..." class="form-control">
                    </div>

                    <div class="mb-1">
                        <label for="icon_url" class="form-label">Icon URL <span class="text-muted">(optional)</span></label>
                        <input id="icon_url" name="icon_url" type="url"
                               value="{{ old('icon_url', $platform->icon_url ?? '') }}"
                               placeholder="https://cdn.example.com/ig.png"
                               class="form-control @error('icon_url') is-invalid @enderror">
                        @error('icon_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-warning mt-2">
                        {{ isset($platform) ? 'Save changes' : 'Create platform' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
