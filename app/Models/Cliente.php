<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';
    protected $fillable = ['nombre', 'email', 'telefono', 'direccion'];

    // Relación con Préstamos (Uno a Muchos)
    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }
}

