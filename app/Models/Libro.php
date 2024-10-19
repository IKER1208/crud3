<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// softdeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class Libro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'libros'; // Nombre de la tabla si es diferente al plural del modelo
    protected $fillable = ['titulo', 'autor_id', 'editorial_id', 'categoria_id', 'genero_id', 'anio_publicacion'];

    // Relación con Autor (Muchos a Uno)
    public function autor()
    {
        return $this->belongsTo(Autor::class);
    }

    // Relación con Editorial (Muchos a Uno)
    public function editorial()
    {
        return $this->belongsTo(Editorial::class);
    }

    // Relación con Categoría (Muchos a Uno)
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Relación con Género (Muchos a Uno)
    public function genero()
    {
        return $this->belongsTo(Genero::class);
    }

    // Relación con Préstamos (Uno a Muchos)
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }
}
