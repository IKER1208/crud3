<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Idioma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'idiomas';
    protected $fillable = ['nombre'];

    // Relación con Libros (si cada libro puede tener un idioma asociado)
    public function libros()
    {
        return $this->hasMany(Libro::class);
    }
}

