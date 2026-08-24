<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\Platform;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->query('q');

        $clients = Client::query()
            ->ownedBy($request->user())
            ->withCount(['posts', 'users as logins_count'])
            ->with('platforms')
            ->when($search !== '', fn (Builder $q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('company.clients.index', [
            'clients' => $clients,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('company.clients.create', [
            'platforms' => Platform::orderBy('name')->get(),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create(
            collect($request->validated())->except('platforms')
                ->merge(['company_id' => $request->user()->company_id])
                ->toArray()
        );

        $client->platforms()->sync($request->input('platforms', []));

        return redirect()
            ->route('clients.index')
            ->with('success', "Client “{$client->name}” created.");
    }

    public function edit(Client $client): View
    {
        abort_unless($client->company_id === auth()->user()->company_id, 404);

        return view('company.clients.edit', [
            'client' => $client,
            'platforms' => Platform::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        abort_unless($client->company_id === $request->user()->company_id, 404);

        $client->update(collect($request->validated())->except('platforms')->toArray());
        $client->platforms()->sync($request->input('platforms', []));

        return redirect()
            ->route('clients.index')
            ->with('success', "Client “{$client->name}” updated.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        abort_unless($client->company_id === auth()->user()->company_id, 404);

        $name = $client->name;
        $client->delete();

        return redirect()->route('clients.index')->with('success', "Client “{$name}” deleted.");
    }
}
