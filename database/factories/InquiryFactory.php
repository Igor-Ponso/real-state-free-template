<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\InquiryStatus;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
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
