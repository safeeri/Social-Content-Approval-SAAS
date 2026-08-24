<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\Post;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlatformSeeder::class);

        User::create([
            'role' => User::ROLE_SAAS_ADMIN,
            'company_id' => null,
            'client_id' => null,
            'name' => 'SaaS Admin',
            'email' => 'admin@socvial.com',
            'password' => 'password',
            'timezone' => 'UTC',
        ]);

        $company = Company::firstOrCreate(
            ['name' => 'Acme Digital Agency'],
            [
                'status' => 'active',
                'timezone' => 'America/New_York',
                'plan_type' => 'professional',
                'subscription_status' => 'active',
                'trial_ends_at' => now()->addDays(14),
            ]
        );

        foreach ([
            [User::ROLE_COMPANY_ADMIN, 'Ava Stone', 'admin@acme.com', 'America/New_York'],
            [User::ROLE_COMPANY_MANAGER, 'Miles Rivera', 'manager@acme.com', 'Asia/Karachi'],
            [User::ROLE_COMPANY_APPROVER, 'Nora Quinn', 'approver@acme.com', 'America/Chicago'],
        ] as [$role, $name, $email, $tz]) {
            User::create([
                'role' => $role,
                'company_id' => $company->id,
                'client_id' => null,
                'name' => $name,
                'email' => $email,
                'password' => 'password',
                'timezone' => $tz,
            ]);
        }

        $client = Client::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Bella Vista Restaurant'],
            [
                'phone' => '+1-212-555-0199',
                'address' => '128 Mulberry St, New York, NY 10013',
                'website' => 'https://bellavista.example.com',
                'website_start_date' => now()->subMonths(8)->toDateString(),
                'platform_bottom_content' => "#BellaVista #NYCEats #ItalianFood",
            ]
        );

        User::create([
            'role' => User::ROLE_CLIENT,
            'company_id' => $company->id,
            'client_id' => $client->id,
            'name' => 'Gina Bellini',
            'email' => 'client@bellavista.com',
            'password' => 'password',
            'timezone' => 'America/New_York',
        ]);

        $instagram = Platform::where('name', 'Instagram')->firstOrFail();
        $tiktok = Platform::where('name', 'TikTok')->firstOrFail();
        $facebook = Platform::where('name', 'Facebook')->firstOrFail();
        $client->platforms()->sync([$instagram->id, $tiktok->id, $facebook->id]);

        $this->seedPosts($client, [
            'instagram' => $instagram,
            'tiktok' => $tiktok,
        ]);
    }

    private function seedPosts(Client $client, array $platforms): void
    {
        $posts = [
            [
                'content' => "Fresh pasta Fridays are back! Tag someone who needs a plate of our hand-rolled tagliatelle.",
                'status' => Post::STATUS_APPROVED,
                'post_type' => Post::TYPE_FEED,
                'publish_date' => now()->addDays(2)->setTime(17, 30),
                'platform_id' => $platforms['instagram']->id,
            ],
            [
                'content' => "Behind the scenes: our chef shaping 200 gnocchi before service. Sound ON for the full effect.",
                'status' => Post::STATUS_PENDING_APPROVAL,
                'post_type' => Post::TYPE_REEL,
                'publish_date' => now()->addDays(3)->setTime(12, 0),
                'platform_id' => $platforms['tiktok']->id,
            ],
            [
                'content' => "New spring menu drops next week. Aperitivo hour extended to 8pm all month long.",
                'status' => Post::STATUS_DRAFT,
                'post_type' => Post::TYPE_FEED,
                'publish_date' => now()->addDays(5)->setTime(18, 45),
                'platform_id' => $platforms['instagram']->id,
            ],
            [
                'content' => "Wine Wednesday: half-priced bottles from our Tuscan cellar list. Reservations recommended.",
                'status' => Post::STATUS_REJECTED,
                'post_type' => Post::TYPE_FEED,
                'publish_date' => now()->addDays(1)->setTime(15, 0),
                'platform_id' => $platforms['instagram']->id,
                'feedback' => 'Can we avoid discount language this quarter? Please reframe around the new wine list instead.',
            ],
            [
                'content' => "Meet Nonna Rosa - she has been making our tiramisu recipe since 1974. Full story on the blog.",
                'status' => Post::STATUS_INTERNAL_REVIEW,
                'post_type' => Post::TYPE_LONG_VIDEO,
                'publish_date' => now()->addDays(6)->setTime(11, 0),
                'platform_id' => $platforms['tiktok']->id,
            ],
            [
                'content' => "Weekend brunch is here: bottomless espresso martinis and live acoustic sets every Sunday.",
                'status' => Post::STATUS_PENDING_APPROVAL,
                'post_type' => Post::TYPE_SHORT,
                'publish_date' => now()->addDays(4)->setTime(10, 30),
                'platform_id' => $platforms['instagram']->id,
            ],
        ];

        $clientUserId = User::where('email', 'client@bellavista.com')->value('id');

        foreach ($posts as $data) {
            $feedback = $data['feedback'] ?? null;
            unset($data['feedback']);

            $post = $client->posts()->create($data);

            if ($feedback && $clientUserId) {
                $post->feedback()->create([
                    'user_id' => $clientUserId,
                    'comment' => $feedback,
                ]);
            }
        }
    }
}
