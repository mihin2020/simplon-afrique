<?php

namespace Database\Factories;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobOffer>
 */
class JobOfferFactory extends Factory
{
    protected $model = JobOffer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'contract_type' => fake()->randomElement(['cdi', 'cdd', 'stage', 'alternance', 'freelance']),
            'location' => fake()->city(),
            'remote_policy' => fake()->randomElement(['sur_site', 'hybride', 'full_remote']),
            'description' => fake()->paragraphs(3, true),
            'experience_years' => fake()->randomElement(['0-2 ans', '2-5 ans', '5-10 ans', '+10 ans']),
            'minimum_education' => fake()->randomElement(['Bac', 'Bac+2', 'Bac+3', 'Bac+5', 'Doctorat']),
            'required_skills' => fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Node.js',
                'Python', 'Django', 'SQL', 'Git', 'Docker', 'AWS', 'Tailwind CSS',
            ], fake()->numberBetween(3, 6)),
            'application_deadline' => fake()->dateTimeBetween('now', '+3 months'),
            'additional_info' => fake()->optional(0.5)->paragraph(),
            'attachment_path' => null,
            'status' => 'draft',
            'published_at' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the job offer is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the job offer is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'published_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'application_deadline' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the job offer is a CDI.
     */
    public function cdi(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_type' => 'cdi',
        ]);
    }

    /**
     * Indicate that the job offer is remote.
     */
    public function remote(): static
    {
        return $this->state(fn (array $attributes) => [
            'remote_policy' => 'full_remote',
        ]);
    }

    /**
     * Indicate that the job offer has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'application_deadline' => fake()->dateTimeBetween('-1 week', 'yesterday'),
        ]);
    }
}
