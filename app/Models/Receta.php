<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    // Guía docente: ver docs/04_modelos_policies_servicios.md.
    /** @use HasFactory<\Database\Factories\RecetaFactory> */
    use HasFactory;


    // El atributo protected $fillable sirve para definir qué campos de la tabla
    // pueden ser asignados masivamente (mass assignment). Esto es importante
    // para proteger contra asignaciones no deseadas o maliciosas cuando se crean
    // o actualizan registros en la base de datos.
    protected $fillable = [
        'user_id',
        'titulo',
        'descripcion',
        'instrucciones',
        'publicada',
        'imagen',
    ];

    // Relación inversa: una receta pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Relación: una receta tiene muchos ingredientes
    public function ingredientes()
    {
        return $this->hasMany(Ingrediente::class);
    }

    // Relación N:M - una receta puede tener likes de muchos usuarios
    public function usuariosQueLesGusto()
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    // Relación 1:N - una receta tiene muchos likes (acceso directo al modelo Like)
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Relación: una receta tiene muchos comentarios
    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
}
