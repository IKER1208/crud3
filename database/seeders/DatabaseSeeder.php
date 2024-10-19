<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Autor;
use App\Models\Cliente;
use App\Models\Libro;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Editorial;
use App\Models\Prestamo;
use App\Models\Sucursal;
use App\Models\Genero;
use App\Models\Idioma;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::factory()->count(100)->create(); 
        Autor::factory()->count(100)->create(); 
        Cliente::factory()->count(100)->create(); 
        Editorial::factory()->count(100)->create(); 
        Sucursal::factory()->count(100)->create(); 
        Empleado::factory()->count(100)->create();
        Genero::factory()->count(100)->create(); 
        Idioma::factory()->count(100)->create(); 
        Libro::factory()->count(100)->create(); 
        Categoria::factory()->count(100)->create();
        Prestamo::factory()->count(100)->create();
    }
}
