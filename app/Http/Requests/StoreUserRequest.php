<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\TimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->isCompanyAdmin()) ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? null;
        $isUpdate = $this->getMethod() !== 'POST';

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', Rule::in([
                User::ROLE_COMPANY_ADMIN,
                User::ROLE_COMPANY_MANAGER,
                User::ROLE_COMPANY_APPROVER,
                User::ROLE_CLIENT,
            ])],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'timezone' => ['required', 'string', 'in:'.implode(',', TimeZone::identifiers())],
            'client_id' => [
                Rule::requiredIf($this->input('role') === User::ROLE_CLIENT),
                'nullable',
                Rule::exists('clients', 'id')->where('company_id', $this->user()->company_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'A client login must be attached to one of your clients.',
        ];
    }

    /**
     * Normalized payload for create/update, enforcing tenant ownership.
     */
    public function validatedPayload(): array
    {
        $data = collect(parent::validated())->only([
            'name', 'email', 'role', 'timezone', 'client_id',
        ])->toArray();

        $data['company_id'] = $this->user()->company_id;

        if ($data['role'] === User::ROLE_CLIENT) {
            $data['client_id'] = (int) $data['client_id'];
        } else {
            $data['client_id'] = null;
        }

        if ($this->filled('password')) {
            $data['password'] = $this->input('password');
        } else {
            unset($data['password']);
        }

        return $data;
    }
}
