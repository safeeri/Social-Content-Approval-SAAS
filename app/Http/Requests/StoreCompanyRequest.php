<?php

namespace App\Http\Requests;

use App\Support\TimeZone;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSaasAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'timezone' => ['required', 'string', 'in:'.implode(',', TimeZone::identifiers())],
            'plan_type' => ['nullable', 'string', 'max:100'],
            'subscription_status' => ['required', 'in:trial,active,past_due,cancelled,expired'],
            'trial_ends_at' => ['nullable', 'date'],
        ];
    }

    public function validatedPayload(): array
    {
        $data = parent::validated();

        if (! empty($data['trial_ends_at'])) {
            $data['trial_ends_at'] = TimeZone::toUtc($data['trial_ends_at'], $this->user()->timezone);
        }

        return $data;
    }
}
