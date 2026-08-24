<?php

namespace App\Http\Requests;

use App\Models\Post;
use App\Support\TimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInternal() ?? false;
    }

    public function rules(): array
    {
        $clientId = (int) $this->input('client_id');

        return [
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where('company_id', $this->user()->company_id),
            ],
            'platform_id' => [
                'required',
                'integer',
                Rule::exists('platforms', 'id'),
                Rule::exists('client_platform', 'platform_id')->where('client_id', $clientId),
            ],
            'content' => ['required', 'string', 'min:5', 'max:5000'],
            'post_type' => ['required', Rule::in(array_keys(Post::TYPES))],
            'publish_date' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'platform_id.exists' => 'The selected platform is not enabled for this client.',
        ];
    }

    /**
     * Publish dates are picked in the editor's timezone, stored as UTC.
     */
    public function validatedPayload(): array
    {
        $data = parent::validated();

        $data['publish_date'] = TimeZone::toUtc(
            $data['publish_date'] ?? null,
            $this->user()->timezone
        );

        return $data;
    }
}
