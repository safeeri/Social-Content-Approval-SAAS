<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Models\Media;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function store(StoreMediaRequest $request)
    {
        $post = Post::with('client')->findOrFail($request->input('post_id'));

        abort_unless((int) $post->client->company_id === (int) $request->user()->company_id, 404);
        abort_if(! in_array($post->status, [Post::STATUS_DRAFT, Post::STATUS_REJECTED], true), 403, 'Media can only be attached to drafts or rejected posts.');

        $disk = config('filesystems.default');
        $nextOrder = (int) $post->media()->max('sort_order') + 1;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store("posts/{$post->id}", $disk);

                $post->media()->create([
                    'file_path' => $path,
                    'disk' => $disk,
                    'media_type' => $this->detectType($file->getMimeType()),
                    'sort_order' => $nextOrder++,
                ]);
            }
        }

        if ($link = $request->input('drive_link')) {
            $post->media()->create([
                'drive_link' => $link,
                'disk' => $disk,
                'media_type' => Media::TYPE_VIDEO, // Drive imports are treated as video sources
                'sort_order' => $nextOrder++,
            ]);
        }

        return back()->with('success', 'Media attached to post.');
    }

    public function destroy(Post $post, Media $media)
    {
        abort_unless((int) $media->post_id === (int) $post->id, 404);
        abort_unless((int) $post->client->company_id === (int) auth()->user()->company_id, 404);
        abort_unless(auth()->user()->isManager() || auth()->user()->isCompanyAdmin(), 403);

        // File cleanup handled by the model's deleting event.
        $media->delete();

        return back()->with('success', 'Media removed.');
    }

    private function detectType(string $mime): string
    {
        if (str_starts_with($mime, 'video/')) {
            return Media::TYPE_VIDEO;
        }

        if (str_starts_with($mime, 'image/')) {
            return Media::TYPE_IMAGE;
        }

        return Media::TYPE_DOCUMENT;
    }
}
