<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\InquiryStatus;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating fake property inquiries.
 *
 * Creates realistic inquiry submissions from prospective buyers or renters.
 * Inquiries are optionally linked to an authenticated user (70% chance) or
 * submitted as a guest. Defaults to an unreplied state with a random
 * inquiry status from the lookup table.
 *
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Generates an inquiry linked to a property, with a 70% chance of being
     * associated with a registered user. Includes a fake name, safe email,
     * optional Canadian phone (60% chance), a paragraph message, a random
     * inquiry status, and null replied_at.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'user_id' => fake()->boolean(70) ? User::factory() : null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional(0.6)->numerify('+1 (###) ###-####'),
            'message' => fake()->paragraph(),
            'inquiry_status_id' => fn () => InquiryStatus::inRandomOrder()->first()?->id ?? InquiryStatus::first()->id,
            'replied_at' => null,
        ];
    }
}
