<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'client_id' => null,
            'role' => 'client',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'timezone' => 'UTC',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function forCompany(\App\Models\Company $company): static
    {
        return $this->state(fn () => ['company_id' => $company->id]);
    }

    public function forClient(\App\Models\Client $client): static
    {
        return $this->state(fn () => ['client_id' => $client->id]);
    }
}
