<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $post = $this->route('post');

        return $user?->isClient()
            && $post
            && (int) $post->client_id === (int) $user->client_id;
    }
}
