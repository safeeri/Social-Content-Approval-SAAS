<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $post = $this->route('post');

        return ($user?->isManager() || $user?->isCompanyAdmin())
            && $post
            && $post->client->company_id === $user->company_id;
    }
}
