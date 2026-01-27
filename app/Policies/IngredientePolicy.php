<?php

namespace App\Policies;

use App\Models\Ingrediente;
use App\Models\User;

class IngredientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ingrediente $ingrediente): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ingrediente $ingrediente): bool
    {
        // Solo el propietario de la receta o un admin puede modificar el ingrediente
        return $user->id === $ingrediente->receta->user_id
            || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ingrediente $ingrediente): bool
    {
        // Solo el propietario de la receta o un admin puede eliminar el ingrediente
        return $user->id === $ingrediente->receta->user_id
            || $user->hasRole('admin');
    }
}
