<?php

namespace Database\Factories;

use App\Models\Libro;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibroFactory extends Factory
{
    protected $model = Libro::class;

    public function definition()
    {
        return [
            'titulo' => $this->faker->sentence, // Genera un título aleatorio
            'editorial_id' => \App\Models\Editorial::factory(), // Relación con Editorial
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
