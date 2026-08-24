<form id="clientForm" method="POST"
      action="{{ isset($client) ? route('clients.update', $client) : route('clients.store') }}">
    @csrf
    @isset($client) @method('PUT') @endisset

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Client name</label>
            <input id="name" name="name" required value="{{ old('name', $client->name ?? '') }}"
                   placeholder="Bella Vista Restaurant" class="form-control">
        </div>

        <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input id="phone" name="phone" required
                   value="{{ old('phone', $client->phone ?? '') }}"
                   placeholder="212-555-0100"
                   class="form-control phone-mask @error('phone') is-invalid @enderror">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="website" class="form-label">Website <span class="text-muted">(optional)</span></label>
            <input id="website" name="website" type="url"
                   value="{{ old('website', $client->website ?? '') }}"
                   placeholder="https://..." class="form-control @error('website') is-invalid @enderror">
            @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="website_start_date" class="form-label">Website start date <span class="text-muted">(optional)</span></label>
            <input type="date" id="website_start_date" name="website_start_date"
                   value="{{ old('website_start_date', isset($client) && $client->website_start_date ? $client->website_start_date->format('Y-m-d') : '') }}"
                   class="form-control @error('website_start_date') is-invalid @enderror">
            @error('website_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
            <label for="address" class="form-label">Address <span class="text-muted">(optional)</span></label>
            <textarea id="address" name="address" rows="2" class="form-control">{{ old('address', $client->address ?? '') }}</textarea>
        </div>

        <div class="col-12">
            <label for="platforms" class="form-label">Active platforms</label>
            <select id="platforms" name="platforms[]" multiple class="form-select select2">
                @foreach($platforms as $platform)
                    @php $selected = old('platforms', isset($client) ? $client->platforms->pluck('id')->all() : []); @endphp
                    <option value="{{ $platform->id }}" @if(in_array($platform->id, $selected)) selected @endif>
                        {{ $platform->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label for="platform_bottom_content" class="form-label">
                Default caption footer <span class="text-muted">(hashtags/text appended to every post)</span>
            </label>
            <textarea id="platform_bottom_content" name="platform_bottom_content" rows="2"
                      class="form-control @error('platform_bottom_content') is-invalid @enderror"
                      placeholder="#BrandHashtag #CityTag">{{ old('platform_bottom_content', $client->platform_bottom_content ?? '') }}</textarea>
            @error('platform_bottom_content')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</form>
