<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Quick smoke test harness: boots the app and hits key routes as each role.
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the framework fully before touching Eloquent.
$kernel->handle(Illuminate\Http\Request::create('http://localhost/up'));

function run($kernel, $method, $uri, ?User $user = null, array $input = [])
{
    Illuminate\Support\Facades\Auth::logout();
    session()->flush();

    if ($user) {
        Illuminate\Support\Facades\Auth::login($user);
    }

    $request = Illuminate\Http\Request::create('http://localhost'.$uri, $method, $input + ['_token' => csrf_token()]);
    $request->setLaravelSession(app('session.store'));
    try {
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();

        echo str_pad($user?->role ?? 'guest', 16).str_pad("$method $uri", 42)."$status\n";

        if ($status >= 500) {
            echo "    ERROR: ".substr($response->getContent(), 0, 400)."\n";
        }

        return [$status, $response];
    } catch (Throwable $e) {
        echo str_pad($user?->role ?? 'guest', 16).str_pad("$method $uri", 42)."EXC\n";
        echo '    '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine()."\n";

        return [500, null];
    }
}

$saasAdmin = User::where('email', 'admin@socvial.com')->first();
$admin = User::where('email', 'admin@acme.com')->first();
$manager = User::where('email', 'manager@acme.com')->first();
$approver = User::where('email', 'approver@acme.com')->first();
$client = User::where('email', 'client@bellavista.com')->first();

echo "=== GET pages ===\n";
run($kernel, 'GET', '/login');
run($kernel, 'GET', '/calendar', $client);
run($kernel, 'GET', '/calendar', $manager);
run($kernel, 'GET', '/calendar/events', $client);
run($kernel, 'GET', '/posts', $manager);
run($kernel, 'GET', '/posts/create', $manager);
run($kernel, 'GET', '/clients', $admin);
run($kernel, 'GET', '/clients/create', $admin);
run($kernel, 'GET', '/team', $admin);
run($kernel, 'GET', '/team/create', $admin);
run($kernel, 'GET', '/saas/companies', $saasAdmin);
run($kernel, 'GET', '/saas/companies/create', $saasAdmin);
run($kernel, 'GET', '/saas/platforms', $saasAdmin);

// Tenant isolation: client must NOT see posts index (403), manager must not see saas admin
run($kernel, 'GET', '/posts', $client);
run($kernel, 'GET', '/saas/companies', $admin);

$post = App\Models\Post::first();
$pendingPost = App\Models\Post::where('status', 'pending_approval')->first();

run($kernel, 'GET', "/posts/{$post->id}/preview", $manager);
run($kernel, 'GET', "/posts/{$post->id}/edit", $manager);
// Client must not open another client's post — but this one IS theirs; use isolation check with a foreign client below.
run($kernel, 'GET', "/posts/{$post->id}/preview", $client);

// Isolation: create a second company + client quickly via DB to verify scoping
$otherCompany = App\Models\Company::create(['name' => 'Rival Co '.uniqid()]);
$otherClient = App\Models\Client::create(['company_id' => $otherCompany->id, 'name' => 'Other', 'phone' => '000']);
$otherUser = App\Models\User::firstOrCreate(
    ['email' => 'other@example.com'],
    [
        'role' => 'client',
        'company_id' => $otherCompany->id,
        'client_id' => $otherClient->id,
        'name' => 'Other Client',
        'password' => Hash::make('password'),
    ]
);

run($kernel, 'GET', "/posts/{$post->id}/preview", $otherUser); // expect 404
run($kernel, 'GET', '/calendar/events', $otherUser);           // expect 200 with empty events

echo "\n=== Workflow ===\n";
// draft -> internal_review as manager
$draft = App\Models\Post::where('status', 'draft')->first();
run($kernel, 'POST', "/posts/{$draft->id}/submit-review", $manager);

// internal_review -> pending_approval as approver
$inReview = App\Models\Post::where('status', 'internal_review')->first();
run($kernel, 'POST', "/posts/{$inReview->id}/review", $approver, ['decision' => 'approve']);

// pending -> rejected as the real client user (mandatory comment)
$pending = App\Models\Post::where('status', 'pending_approval')->first();
run($kernel, 'POST', "/posts/{$pending->id}/reject", $client, ['comment' => 'Please change the tone of this caption.']);

// other client cannot reject someone else's post
$stillPending = App\Models\Post::where('status', 'pending_approval')->first();
if ($stillPending) {
    run($kernel, 'POST', "/posts/{$stillPending->id}/reject", $otherUser, ['comment' => 'not mine at all']);
}

echo "\nDone.\n";
