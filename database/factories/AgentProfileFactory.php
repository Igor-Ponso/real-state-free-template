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
    private const BIOS = [
        'With over 15 years in Vancouver luxury real estate, I specialize in waterfront properties and heritage homes. My clients trust me to find residences that match their lifestyle — whether it\'s a Coal Harbour penthouse or a Shaughnessy estate. I believe every transaction should feel as exceptional as the property itself.',
        'I bring a data-driven approach to real estate, combining deep market analytics with a personal touch. Having helped over 200 families find their dream homes across the Lower Mainland, I understand that buying a home is both a financial decision and an emotional journey. Let me guide you through both.',
        'Born and raised in British Columbia, I have an intimate knowledge of every neighborhood from Kitsilano to West Vancouver. My background in interior design gives me a unique eye for potential — I don\'t just sell homes, I help clients see the possibilities within every space.',
        'As a former corporate executive turned real estate professional, I understand the needs of relocating professionals and international buyers. Fluent in three languages, I\'ve built a reputation for discretion, efficiency, and results in the luxury market segment.',
        'My passion is connecting people with properties that tell a story. From century-old Craftsman homes in Mount Pleasant to cutting-edge condos in Yaletown, I find beauty and value where others might overlook it. My clients often become lifelong friends — that\'s how I know I\'m doing this right.',
        'Specializing in investment properties and pre-construction opportunities, I help clients build wealth through strategic real estate decisions. With a background in finance and an MBA from UBC, I bring analytical rigor to every deal while never losing sight of what makes a house feel like home.',
        'I started in real estate because I believe everyone deserves a place that inspires them. Whether you\'re a first-time buyer navigating the market or a seasoned investor expanding your portfolio, I provide the same level of dedication and honest advice. Your goals become my goals.',
        'With deep roots in Vancouver\'s multicultural communities, I bridge cultures and markets to create opportunities others miss. My extensive network of architects, developers, and financial advisors means my clients get a full-service experience from the first viewing to the final signature.',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'phone' => fake()->numerify('+1 (###) ###-####'),
            'bio' => fake()->randomElement(self::BIOS),
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
