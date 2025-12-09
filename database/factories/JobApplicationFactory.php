<?php

namespace Database\Factories;

use App\Models\JobApplication;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_offer_id' => JobOffer::factory(),
            'user_id' => User::factory(),
            'applicant_type' => fake()->randomElement(['formateur', 'admin']),
            'cv_path' => null,
            'profile_snapshot' => [
                'name' => fake()->lastName(),
                'first_name' => fake()->firstName(),
                'email' => fake()->email(),
                'applied_at' => now()->toIso8601String(),
            ],
            'status' => 'pending',
            'notes' => null,
        ];
    }

    /**
     * Indicate that the application is from a formateur.
     */
    public function fromFormateur(): static
    {
        return $this->state(fn (array $attributes) => [
            'applicant_type' => 'formateur',
            'cv_path' => 'formateurs/cv/test_cv.pdf',
            'profile_snapshot' => [
                'name' => fake()->lastName(),
                'first_name' => fake()->firstName(),
                'email' => fake()->email(),
                'phone' => fake()->phoneNumber(),
                'country' => 'France',
                'technical_profile' => fake()->randomElement(['Développeur Web', 'Data Scientist', 'DevOps']),
                'years_of_experience' => fake()->randomElement(['moins_de_2_ans', 'entre_2_et_5_ans', 'plus_de_5_ans']),
                'portfolio_url' => fake()->optional()->url(),
                'certifications' => fake()->randomElements(['AWS', 'Azure', 'Google Cloud', 'Scrum Master'], 2),
                'applied_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Indicate that the application is from an admin.
     */
    public function fromAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'applicant_type' => 'admin',
            'cv_path' => null,
            'profile_snapshot' => [
                'name' => fake()->lastName(),
                'first_name' => fake()->firstName(),
                'email' => fake()->email(),
                'applied_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Indicate that the application has been reviewed.
     */
    public function reviewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reviewed',
        ]);
    }

    /**
     * Indicate that the application has been accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'notes' => 'Candidature acceptée.',
        ]);
    }

    /**
     * Indicate that the application has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'notes' => fake()->optional()->sentence(),
        ]);
    }
}
