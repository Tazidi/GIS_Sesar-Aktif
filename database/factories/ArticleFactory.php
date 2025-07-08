<?php

namespace Database\Factories;

use App\Models\User; // <-- Pastikan ini ada
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
public function definition(): array
{
    return [
        'user_id'  => \App\Models\User::factory(),
        'title'     => $this->faker->sentence(6),
        'content'   => $this->faker->paragraphs(5, true),
        'author'    => fake()->name(),
        'thumbnail' => 'thumbnails/' . $this->faker->numberBetween(1, 4) . '.jpg',
        'status'    => 'approved', // <-- PASTIKAN BARIS INI ADA
    ];
}
}