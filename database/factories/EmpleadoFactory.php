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
            'sucursal_id' => \App\Models\Sucursal::factory(), // Relación con Sucursal
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
