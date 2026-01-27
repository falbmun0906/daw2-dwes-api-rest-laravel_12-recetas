<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Receta;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Toggle like on a recipe (add or remove).
     * Usa Create/Delete en lugar de attach/detach.
     */
    public function toggle(Request $request, Receta $receta)
    {
        $user = $request->user();

        // Buscar si existe un like con el user_id y receta_id actuales
        $existingLike = Like::where('user_id', $user->id)
            ->where('receta_id', $receta->id)
            ->first();

        if ($existingLike) {
            // Si existe, eliminarlo (Delete)
            $existingLike->delete();

            return response()->json([
                'message' => 'Like eliminado',
                'liked' => false,
                'likes_count' => $receta->likes()->count()
            ], 200);
        } else {
            // Si no existe, crear una nueva instancia (Create)
            Like::create([
                'user_id' => $user->id,
                'receta_id' => $receta->id,
            ]);

            return response()->json([
                'message' => 'Like agregado',
                'liked' => true,
                'likes_count' => $receta->likes()->count()
            ], 200);
        }
    }

    /**
     * Get likes count for a recipe.
     */
    public function count(Receta $receta)
    {
        return response()->json([
            'receta_id' => $receta->id,
            'likes_count' => $receta->likes()->count()
        ], 200);
    }

    /**
     * Check if current user liked a recipe.
     */
    public function status(Request $request, Receta $receta)
    {
        $user = $request->user();

        // Buscar usando el modelo Like con hasMany
        $liked = Like::where('user_id', $user->id)
            ->where('receta_id', $receta->id)
            ->exists();

        return response()->json([
            'receta_id' => $receta->id,
            'liked' => $liked,
            'likes_count' => $receta->likes()->count()
        ], 200);
    }
}
