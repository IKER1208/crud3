<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Prestamo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prestamos';
    protected $fillable = ['libro_id', 'cliente_id', 'fecha_prestamo', 'fecha_devolucion'];

    // Relación con Libro (Muchos a Uno)
    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    // Relación con Cliente (Muchos a Uno)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}

