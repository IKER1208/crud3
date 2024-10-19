<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleados';
    protected $fillable = ['nombre', 'sucursal_id'];

    // Relación con Sucursal (Muchos a Uno)
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}

