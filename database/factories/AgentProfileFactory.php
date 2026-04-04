<?php

namespace Database\Factories;

use App\Models\AgentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentProfile>
 */
class AgentProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => fake()->numerify('+1 (###) ###-####'),
            'bio' => fake()->paragraphs(2, true),
            'license_number' => strtoupper(fake()->bothify('RE-####-??')),
            'specializations' => fake()->randomElements(
                ['Luxury Homes', 'Condos', 'Commercial', 'Waterfront', 'Investment', 'First-Time Buyers'],
                fake()->numberBetween(1, 3),
            ),
            'social_links' => [
                'linkedin' => 'https://linkedin.com/in/'.fake()->slug(2),
                'instagram' => 'https://instagram.com/'.fake()->userName(),
            ],
            'is_featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
