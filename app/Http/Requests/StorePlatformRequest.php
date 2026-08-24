<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSaasAdmin() ?? false;
    }

    public function rules(): array
    {
        $platformId = $this->route('platform')?->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('platforms', 'name')->ignore($platformId)],
            'icon_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'icon_url.url' => 'The icon must be a valid URL (https://...).',
        ];
    }
}
