<?php

namespace Database\Factories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->name,
            'sucursal_id' => $this->faker->numberBetween(1, 10), // Relación con Sucursal
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
