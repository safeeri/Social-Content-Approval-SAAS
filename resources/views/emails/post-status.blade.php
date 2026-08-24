@component('mail::layout')
{{-- Header --}}
@slot('header')
    @component('mail::header', ['url' => config('app.url')])
        <span style="display:inline-block;background:#FFD600;color:#111111;padding:6px 14px;font-weight:700;border-radius:4px;">SOCVIAL</span>
    @endcomponent
@endslot

## {{ $headline }}

{{ $message }}

@component('mail::panel')
**Client:** {{ $post->client->name }}<br>
**Platform:** {{ $post->platform->name }}<br>
**Type:** {{ \App\Models\Post::TYPES[$post->post_type] }}<br>
@if($post->publish_date)
**Scheduled:** {{ \App\Support\TimeZone::format($post->publish_date, 'UTC') }} (UTC)<br>
@endif
**Status:** {{ ucfirst(str_replace('_', ' ', $post->status)) }}
@endcomponent

@component('mail::button', ['url' => $ctaUrl, 'color' => 'primary'])
{{ $ctaLabel }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}

@slot('footer')
    @component('mail::footer')
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    @endcomponent
@endslot
@endcomponent
