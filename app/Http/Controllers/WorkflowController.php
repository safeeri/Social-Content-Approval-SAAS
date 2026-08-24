<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApprovePostRequest;
use App\Http\Requests\InternalReviewRequest;
use App\Http\Requests\RejectPostRequest;
use App\Http\Requests\SubmitReviewRequest;
use App\Mail\PostStatusChanged;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class WorkflowController extends Controller
{
    /**
     * draft / rejected -> internal_review (manager submits work).
     */
    public function submit(SubmitReviewRequest $request, Post $post): RedirectResponse
    {
        abort_if(! in_array($post->status, [Post::STATUS_DRAFT, Post::STATUS_REJECTED], true), 403, 'This post is not awaiting submission.');

        $post->update(['status' => Post::STATUS_INTERNAL_REVIEW]);

        return back()->with('success', 'Post sent to internal review.');
    }

    /**
     * internal_review -> pending_approval (approver signs off) | -> draft (sent back).
     */
    public function internalDecision(InternalReviewRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->status === Post::STATUS_INTERNAL_REVIEW, 403, 'This post is not in internal review.');

        if ($request->input('decision') === 'approve') {
            $post->update(['status' => Post::STATUS_PENDING_APPROVAL]);
            $this->notifyClientUsers($post);

            return back()->with('success', 'Approved internally and pushed to the client’s calendar.');
        }

        $post->update(['status' => Post::STATUS_DRAFT]);

        return back()->with('success', 'Post returned to the manager as a draft.');
    }

    /**
     * pending_approval -> approved (client signs off).
     */
    public function clientApprove(ApprovePostRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->status === Post::STATUS_PENDING_APPROVAL, 403, 'This post is not waiting for your approval.');

        $post->update(['status' => Post::STATUS_APPROVED]);
        $this->notifyManagers($post,
            'Your post was APPROVED',
            "{$request->user()->name} approved “{$this->excerpt($post)}”. It is scheduled to go live.",
            'View post',
        );

        return back()->with('success', 'Post approved. The agency has been notified.');
    }

    /**
     * pending_approval -> rejected (client must explain why).
     */
    public function clientReject(RejectPostRequest $request, Post $post): RedirectResponse
    {
        abort_unless($post->status === Post::STATUS_PENDING_APPROVAL, 403, 'This post is not waiting for your approval.');

        $post->feedback()->create([
            'user_id' => Auth::id(),
            'comment' => $request->validated('comment'),
        ]);
        $post->update(['status' => Post::STATUS_REJECTED]);

        $this->notifyManagers($post,
            'Your post was REJECTED',
            "{$request->user()->name} rejected “{$this->excerpt($post)}” with feedback: “{$request->validated('comment')}”",
            'Revise post',
        );

        return back()->with('success', 'Rejection recorded — the agency will revise this post.');
    }

    private function notifyClientUsers(Post $post): void
    {
        $users = User::where('client_id', $post->client_id)
            ->where('role', User::ROLE_CLIENT)
            ->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new PostStatusChanged(
                $post,
                'New post awaiting your approval',
                'A new post for '.$post->client->name.' has been placed on your calendar and needs your sign-off before publishing.',
                'Review & approve',
                route('calendar.index'),
            ));
        }
    }

    private function notifyManagers(Post $post, string $headline, string $message, string $cta): void
    {
        $users = User::managersForCompany($post->client->company_id);

        foreach ($users as $user) {
            Mail::to($user->email)->send(new PostStatusChanged(
                $post,
                $headline,
                $message,
                $cta,
                route('posts.edit', $post),
            ));
        }
    }

    private function excerpt(Post $post): string
    {
        return \Illuminate\Support\Str::limit(strip_tags($post->content), 60);
    }
}
