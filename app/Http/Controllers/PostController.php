<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Client;
use App\Models\Post;
use App\Models\Platform;
use App\Support\TimeZone;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status');
        $search = (string) $request->query('q');

        $posts = Post::query()
            ->visibleTo($request->user())
            ->with(['client', 'platform', 'media'])
            ->when(in_array($status, Post::STATUSES, true), fn (Builder $q) => $q->where('status', $status))
            ->when($search !== '', fn (Builder $q) => $q->where('content', 'like', "%{$search}%"))
            ->orderByDesc('publish_date')
            ->paginate(10)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'statuses' => Post::STATUS_LABELS,
            'activeStatus' => in_array($status, Post::STATUSES, true) ? $status : null,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        $clients = Client::ownedBy($request->user())->orderBy('name')->get();

        return view('posts.create', [
            'clients' => $clients,
            'postTypes' => Post::TYPES,
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create($request->validatedPayload());

        return redirect()
            ->route('posts.edit', $post)
            ->with('success', 'Draft created — now attach your media files or a Drive link.');
    }

    public function edit(Request $request, Post $post): View
    {
        abort_unless($this->canManage($request, $post), 404);

        $post->load(['client', 'platform', 'media']);

        return view('posts.edit', [
            'post' => $post,
            'clients' => Client::ownedBy($request->user())->orderBy('name')->get(),
            'platforms' => $post->client->platforms()->orderBy('name')->get(),
            'selectedPlatforms' => [$post->platform_id],
            'postTypes' => Post::TYPES,
            'publishLocal' => TimeZone::format($post->publish_date, $request->user()->timezone, 'Y-m-d\TH:i'),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        abort_unless($this->canManage($request, $post), 404);
        abort_if(! in_array($post->status, [Post::STATUS_DRAFT, Post::STATUS_REJECTED], true), 403, 'Only drafts or rejected posts can be edited.');

        $post->update($request->validatedPayload());

        return redirect()->route('posts.edit', $post)->with('success', 'Post updated.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        abort_unless($this->canManage($request, $post), 404);

        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted.');
    }

    /**
     * HTML fragment rendered inside the right-side drawer.
     */
    public function preview(Request $request, Post $post)
    {
        abort_unless($this->visibleToUser($request, $post), 404);

        $post->load(['client', 'platform', 'media', 'feedback.user']);

        return response()
            ->view('posts.partials.drawer-body', ['post' => $post])
            ->header('Cache-Control', 'no-store');
    }

    private function visibleToUser(Request $request, Post $post): bool
    {
        $user = $request->user();

        if ($user->isSaasAdmin()) {
            return true;
        }

        if ((int) $post->client->company_id !== (int) $user->company_id) {
            return false;
        }

        if ($user->isClient()) {
            return (int) $post->client_id === (int) $user->client_id;
        }

        return true;
    }

    private function canManage(Request $request, Post $post): bool
    {
        return ($request->user()?->isManager() || $request->user()?->isCompanyAdmin())
            && (int) $post->client->company_id === (int) $request->user()->company_id;
    }
}
