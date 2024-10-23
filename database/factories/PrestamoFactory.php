<?php

namespace Database\Factories;

use App\Models\Prestamo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrestamoFactory extends Factory
{
    protected $model = Prestamo::class;

    public function definition()
    {
        return [
            'cliente_id' => $this->faker->numberBetween(1, 10), // Relación con Cliente
            'libro_id' => $this->faker->numberBetween(1, 10), // Relación con Libro
            'fecha_prestamo' => $this->faker->dateTime,
            'fecha_devolucion' => $this->faker->dateTime,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
