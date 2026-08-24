@extends('layouts.guest')

@section('content')
<div class="card shadow-lg border-0 p-2">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <span class="bg-yellow text-dark fw-bold px-4 py-2 rounded d-inline-block fs-4">SOCVIAL</span>
            <p class="text-muted mt-2 mb-0">Social content approval, simplified.</p>
        </div>

        @include('layouts.partials.flash')

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}" required autofocus
                       autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="you@example.com">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password"
                       required autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-warning w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Log in
            </button>
        </form>
    </div>
    <div class="card-footer bg-white text-center small text-muted border-0 pb-3">
        Trouble logging in? Contact your agency administrator.
    </div>
</div>
@endsection
