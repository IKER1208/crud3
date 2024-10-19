<?php

namespace Database\Factories;

use App\Models\Idioma;
use Illuminate\Database\Eloquent\Factories\Factory;

class IdiomaFactory extends Factory
{
    protected $model = Idioma::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->word,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
