<?php

namespace Database\Factories;

use App\Models\Editorial;
use Illuminate\Database\Eloquent\Factories\Factory;

class EditorialFactory extends Factory
{
    protected $model = Editorial::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->company,
            'pais' => $this->faker->country,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
