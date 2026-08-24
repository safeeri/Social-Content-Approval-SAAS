<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlatformRequest;
use App\Http\Requests\UpdatePlatformRequest;
use App\Models\Platform;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function index(): View
    {
        return view('saas.platforms.index', [
            'platforms' => Platform::withCount('posts')->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('saas.platforms.create');
    }

    public function store(StorePlatformRequest $request): RedirectResponse
    {
        Platform::create($request->validated());

        return redirect()
            ->route('saas.platforms.index')
            ->with('success', 'Platform created.');
    }

    public function edit(Platform $platform): View
    {
        return view('saas.platforms.edit', ['platform' => $platform]);
    }

    public function update(UpdatePlatformRequest $request, Platform $platform): RedirectResponse
    {
        $platform->update($request->validated());

        return redirect()
            ->route('saas.platforms.index')
            ->with('success', "Platform “{$platform->name}” updated.");
    }

    public function destroy(Platform $platform): RedirectResponse
    {
        try {
            $platform->delete();
        } catch (QueryException) {
            // posts.platform_id is FK restrictOnDelete
            return back()->with('error', "“{$platform->name}” has posts attached and cannot be deleted.");
        }

        return redirect()
            ->route('saas.platforms.index')
            ->with('success', "Platform “{$platform->name}” deleted.");
    }
}
