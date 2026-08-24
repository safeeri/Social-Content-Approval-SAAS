<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            ['name' => 'Instagram'],
            ['name' => 'Facebook'],
            ['name' => 'TikTok'],
            ['name' => 'YouTube'],
            ['name' => 'LinkedIn'],
            ['name' => 'X (Twitter)'],
        ];

        foreach ($platforms as $platform) {
            Platform::firstOrCreate(['name' => $platform['name']], $platform);
        }
    }
}
