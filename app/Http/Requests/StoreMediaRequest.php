<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    private const ALLOWED_MIMES = 'jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,webm,mkv,pdf,doc,docx,xls,xlsx';

    public function authorize(): bool
    {
        return ($this->user()?->isManager() || $this->user()?->isCompanyAdmin()) ?? false;
    }

    public function rules(): array
    {
        return [
            'post_id' => [
                'required',
                Rule::exists('posts', 'id')->whereNull('deleted_at'),
            ],
            'files' => ['required_without:drive_link', 'nullable', 'array', 'max:10'],
            'files.*' => ['file', 'mimes:'.self::ALLOWED_MIMES, 'max:102400'],
            'drive_link' => ['required_without:files', 'nullable', 'url', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'files.max' => 'You can upload at most 10 files at once.',
            'files.*.max' => 'Each file must be 100 MB or smaller.',
            'drive_link.required_without' => 'Upload a file or provide a Google Drive link.',
            'files.required_without' => 'Upload a file or provide a Google Drive link.',
        ];
    }
}
