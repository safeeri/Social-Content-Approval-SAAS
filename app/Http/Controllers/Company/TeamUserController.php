<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Client;
use App\Models\User;
use App\Support\TimeZone;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamUserController extends Controller
{
    public function index(Request $request): View
    {
        $role = (string) $request->query('role');
        $search = (string) $request->query('q');

        $users = User::query()
            ->where('company_id', $request->user()->company_id)
            ->with(['client'])
            ->when(in_array($role, User::ROLES, true), fn (Builder $q) => $q->where('role', $role))
            ->when($search !== '', fn (Builder $q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderByRaw("FIELD(role, 'company_admin','company_manager','company_approver','client')")
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('company.team.index', [
            'users' => $users,
            'roles' => collect(User::ROLES)->reject(fn ($r) => $r === User::ROLE_SAAS_ADMIN),
            'activeRole' => in_array($role, User::ROLES, true) ? $role : null,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('company.team.create', [
            'roles' => collect(User::ROLES)->reject(fn ($r) => $r === User::ROLE_SAAS_ADMIN),
            'clients' => Client::ownedBy(auth()->user())->orderBy('name')->get(),
            'timezones' => TimeZone::identifiers(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->validatedPayload());

        return redirect()
            ->route('team.index')
            ->with('success', "Team member “{$request->input('name')}” created.");
    }

    public function edit(User $user): View
    {
        abort_unless($user->company_id === auth()->user()->company_id, 404);

        return view('company.team.edit', [
            'user' => $user,
            'roles' => collect(User::ROLES)->reject(fn ($r) => $r === User::ROLE_SAAS_ADMIN),
            'clients' => Client::ownedBy(auth()->user())->orderBy('name')->get(),
            'timezones' => TimeZone::identifiers(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->company_id === $request->user()->company_id, 404);

        $user->update($request->validatedPayload());

        return redirect()
            ->route('team.index')
            ->with('success', "“{$user->name}” updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->company_id === auth()->user()->company_id, 404);
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        $name = $user->name;
        $user->delete();

        return redirect()->route('team.index')->with('success', "“{$name}” deleted.");
    }
}
