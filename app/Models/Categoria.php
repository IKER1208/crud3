<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias';
    protected $fillable = ['nombre'];

    // Relación con Libros (Uno a Muchos)
    public function libros()
    {
        return $this->hasMany(Libro::class);
    }
}

