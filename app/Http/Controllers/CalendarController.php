<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\TimeZone;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        return view('calendar.index');
    }

    /**
     * JSON feed for FullCalendar. Dates are emitted as ISO-8601 strings that
     * already carry the viewer's UTC offset, so the calendar renders the
     * correct wall-clock time for whoever is logged in.
     */
    public function events(Request $request): JsonResponse
    {
        $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);

        $user = $request->user();

        $posts = Post::query()
            ->visibleTo($user)
            ->with(['client:id,name', 'platform:id,name'])
            ->whereNotNull('publish_date')
            ->when($request->filled('start'), fn ($q) => $q->where('publish_date', '>=', Carbon::parse($request->input('start'))->utc()->startOfDay()))
            ->when($request->filled('end'), fn ($q) => $q->where('publish_date', '<=', Carbon::parse($request->input('end'))->utc()->endOfDay()))
            ->get();

        $events = $posts->map(fn (Post $post) => [
            'id' => $post->id,
            'title' => $post->platform->name.' · '.$post->post_type,
            'start' => TimeZone::iso($post->publish_date, $user->timezone),
            'extendedProps' => [
                'status' => $post->status,
                'statusLabel' => Post::STATUS_LABELS[$post->status],
                'clientName' => $post->client->name,
                'platformName' => $post->platform->name,
            ],
        ]);

        return response()->json(['events' => $events]);
    }
}
