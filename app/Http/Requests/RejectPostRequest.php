<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RejectPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $post = $this->route('post');

        return $user?->isClient()
            && $post
            && (int) $post->client_id === (int) $user->client_id;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'Please explain why you are rejecting this post — the agency needs your feedback to revise it.',
            'comment.min' => 'Please write at least :min characters so the team understands the issue.',
        ];
    }
}
