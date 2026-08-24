<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Support\TimeZone;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('q');

        $companies = Company::query()
            ->withCount(['users', 'clients'])
            ->when($search !== '', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('saas.companies.index', [
            'companies' => $companies,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('saas.companies.create', [
            'timezones' => TimeZone::identifiers(),
        ]);
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validatedPayload());

        return redirect()
            ->route('saas.companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function edit(Company $company): View
    {
        return view('saas.companies.edit', [
            'company' => $company,
            'timezones' => TimeZone::identifiers(),
            'trialEndsAtLocal' => TimeZone::format($company->trial_ends_at, auth()->user()->timezone, 'Y-m-d\TH:i'),
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validatedPayload());

        return redirect()
            ->route('saas.companies.index')
            ->with('success', "Company “{$company->name}” updated.");
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('saas.companies.index')
            ->with('success', "Company “{$company->name}” deleted.");
    }
}
