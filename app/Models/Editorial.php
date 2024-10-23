<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Editorial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'editoriales'; // Especificas 'editorials'
    protected $fillable = ['nombre', 'pais'];

    public function libros()
    {
        return $this->hasMany(Libro::class);
    }
}

