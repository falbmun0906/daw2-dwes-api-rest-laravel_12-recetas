<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Receta;
use Illuminate\Http\Request;
use App\Http\Resources\ComentarioResource;

class ComentarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Receta $receta)
    {
        $comentarios = $receta->comentarios()->with('user')->get();
        return ComentarioResource::collection($comentarios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Receta $receta)
    {
        $data = $request->validate([
            'texto' => 'required|string|max:1000',
        ]);

        $comentario = $receta->comentarios()->create([
            'texto' => $data['texto'],
            'user_id' => $request->user()->id,
        ]);

        $comentario->load('user');

        return new ComentarioResource($comentario);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receta $receta, Comentario $comentario)
    {
        // Verificar que el comentario pertenece a la receta
        if ($comentario->receta_id !== $receta->id) {
            return response()->json([
                'message' => 'Comentario no encontrado en esta receta'
            ], 404);
        }

        $comentario->load('user');
        return new ComentarioResource($comentario);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receta $receta, Comentario $comentario)
    {
        // Verificar que el comentario pertenece a la receta
        if ($comentario->receta_id !== $receta->id) {
            return response()->json([
                'message' => 'Comentario no encontrado en esta receta'
            ], 404);
        }

        $this->authorize('update', $comentario);

        $data = $request->validate([
            'texto' => 'required|string|max:1000',
        ]);

        $comentario->update($data);
        $comentario->load('user');

        return new ComentarioResource($comentario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receta $receta, Comentario $comentario)
    {
        // Verificar que el comentario pertenece a la receta
        if ($comentario->receta_id !== $receta->id) {
            return response()->json([
                'message' => 'Comentario no encontrado en esta receta'
            ], 404);
        }

        $this->authorize('delete', $comentario);

        $comentario->delete();

        return response()->json([
            'message' => 'Comentario eliminado correctamente'
        ], 200);
    }
}
