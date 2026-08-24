@extends('layouts.app')

@section('title', '- Team')
@section('page_title', isset($user) ? 'Edit User: '.$user->name : 'Add User')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-gear"></i> {{ isset($user) ? 'Edit user' : 'New user' }}</div>
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($user) ? route('team.update', $user) : route('team.store') }}">
                    @csrf
                    @isset($user) @method('PUT') @endisset

                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input id="name" name="name" required value="{{ old('name', $user->name ?? '') }}" class="form-control">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email (login)</label>
                            <input id="email" type="email" name="email" required
                                   value="{{ old('email', $user->email ?? '') }}"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="role" class="form-label">Role</label>
                            <select id="role" name="role" class="form-select select2">
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>
                                        {{ str_replace('_', ' ', $role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="clientFieldWrap" style="display:none;">
                            <label for="client_id" class="form-label">Attach to client</label>
                            <select id="client_id" name="client_id" class="form-select select2">
                                <option value="">— choose client —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id', $user->client_id ?? '') == $client->id)>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select id="timezone" name="timezone" required class="form-select select2">
                                @foreach($timezones as $tz)
                                    <option value="{{ $tz }}" @selected(old('timezone', $user->timezone ?? auth()->user()->timezone) === $tz)>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Password @if(isset($user))<span class="text-muted">(leave blank to keep)</span>@endif
                            </label>
                            <input id="password" type="password" name="password"
                                   autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   autocomplete="new-password" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning mt-3">
                        {{ isset($user) ? 'Save changes' : 'Create user' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const roleSelect = document.getElementById('role');
    const clientWrap = document.getElementById('clientFieldWrap');

    function syncClientVisibility() {
        clientWrap.style.display = roleSelect.value === 'client' ? '' : 'none';
    }
    roleSelect.addEventListener('change', syncClientVisibility);
    syncClientVisibility();
</script>
@endpush
