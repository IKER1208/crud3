<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Sucursal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sucursales';
    protected $fillable = ['nombre', 'direccion'];

    // Relación con Empleados (Uno a Muchos)
    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }
}
