<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'receta_id',
        'user_id',
        'texto',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación: un comentario pertenece a una receta
    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }

    // Relación: un comentario pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
