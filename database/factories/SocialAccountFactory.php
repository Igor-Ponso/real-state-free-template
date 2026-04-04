<?php

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for generating fake OAuth social account records.
 *
 * Creates social login connections (Google, GitHub, Facebook, Apple) linked
 * to a user, with a unique provider ID, a random token, and no refresh token.
 * Used to test Socialite-based authentication flows.
 *
 * @extends Factory<SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['google', 'github', 'facebook', 'apple']),
            'provider_id' => fake()->unique()->numerify('##########'),
            'provider_token' => Str::random(40),
            'provider_refresh_token' => null,
        ];
    }
}
