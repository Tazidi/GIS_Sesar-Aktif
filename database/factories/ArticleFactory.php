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
            'user_id' => User::factory(), // <-- Membuat user baru untuk setiap artikel, atau bisa diganti User::first()->id jika sudah ada user
            'title' => $this->faker->sentence(6), // Membuat judul dari 6 kata acak
            'content' => $this->faker->paragraphs(5, true), // Membuat 5 paragraf acak
            'thumbnail' => $this->faker->imageUrl(640, 480, 'nature', true),
            'status' => 'approved', // Langsung set statusnya menjadi 'approved'
        ];
    }
}