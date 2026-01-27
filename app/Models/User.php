<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    // Guía docente: ver docs/07_roles_permisos.md.
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles,HasApiTokens,HasFactory, Notifiable;

    protected $guard_name = 'sanctum';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function recetas()
    {
        return $this->hasMany(\App\Models\Receta::class);
    }

    // Relación N:M - un usuario puede dar like a muchas recetas
    public function recetasLiked()
    {
        return $this->belongsToMany(Receta::class, 'likes')->withTimestamps();
    }

    // Relación 1:N - un usuario tiene muchos likes (acceso directo al modelo Like)
    public function likes()
    {
        return $this->hasMany(Like::class);
    }


}
