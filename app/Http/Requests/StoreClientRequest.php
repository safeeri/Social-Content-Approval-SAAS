<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->isCompanyAdmin()) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()\.]+$/'],
            'address' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'url', 'max:255'],
            'website_start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'platform_bottom_content' => ['nullable', 'string', 'max:2000'],
            'platforms' => ['nullable', 'array'],
            'platforms.*' => ['integer', 'exists:platforms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number may only contain digits, spaces, +, -, ( ) and dots.',
        ];
    }
}
