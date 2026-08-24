<form id="companyForm" method="POST"
      action="{{ isset($company) ? route('saas.companies.update', $company) : route('saas.companies.store') }}">
    @csrf
    @isset($company) @method('PUT') @endisset

    <div class="mb-3">
        <label for="name" class="form-label">Company name</label>
        <input id="name" name="name" value="{{ old('name', $company->name ?? '') }}" required class="form-control">
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select select2">
                @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $company->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label for="timezone" class="form-label">Timezone</label>
            <select id="timezone" name="timezone" required class="form-select select2">
                @foreach($timezones as $tz)
                    <option value="{{ $tz }}" @selected(old('timezone', $company->timezone ?? 'UTC'))>{{ $tz }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label for="plan_type" class="form-label">Plan type</label>
            <input id="plan_type" name="plan_type" value="{{ old('plan_type', $company->plan_type ?? '') }}"
                   placeholder="starter, professional..." class="form-control">
        </div>
        <div class="col-md-6">
            <label for="subscription_status" class="form-label">Subscription</label>
            <select id="subscription_status" name="subscription_status" class="form-select select2">
                @foreach(['trial', 'active', 'past_due', 'cancelled', 'expired'] as $value)
                    <option value="{{ $value }}" @selected(old('subscription_status', $company->subscription_status ?? 'trial'))>{{ ucfirst($value) }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label for="trial_ends_at" class="form-label">Trial ends at (your timezone)</label>
            <input type="datetime-local" id="trial_ends_at" name="trial_ends_at"
                   value="{{ old('trial_ends_at', $trialEndsAt ?? '') }}"
                   class="form-control @error('trial_ends_at') is-invalid @enderror">
            @error('trial_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</form>
