<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Socvial') }} @yield('title')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    {{-- FullCalendar --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    {{-- App theme --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    @include('layouts.partials.sidebar')

    <div class="sv-backdrop"></div>

    <div class="sv-main">
        <div class="sv-topbar d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebarToggle" class="btn btn-outline-dark btn-sm d-lg-none" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="h5 mb-0 fw-bold">@yield('page_title')</h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-black text-warning">{{ auth()->user()->timezone }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning" title="Log out">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-md-inline ms-1">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="sv-content">
            @include('layouts.partials.flash')
            @yield('content')
        </div>
    </div>

    {{-- Right-side post drawer --}}
    <aside id="postDrawer" class="sv-drawer" aria-hidden="true">
        <div class="sv-drawer-head">
            <strong id="drawerTitle">Post preview</strong>
            <button type="button" class="btn-close btn-close-white" data-close-drawer aria-label="Close"></button>
        </div>
        <div class="sv-drawer-body" id="drawerBody"></div>
    </aside>

    {{-- Fullscreen media modal --}}
    <div class="modal fade modal-fullscreen-media" id="mediaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-warning text-dark">Full view</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="mediaStage"></div>
                <div id="mediaCaptionFull" class="caption-box mt-3"></div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
