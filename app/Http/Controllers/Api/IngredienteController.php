<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingrediente;
use App\Models\Receta;
use Illuminate\Http\Request;
use App\Http\Resources\IngredienteResource;

class IngredienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Receta $receta)
    {
        $ingredientes = $receta->ingredientes;
        return IngredienteResource::collection($ingredientes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Receta $receta)
    {
        // Verificar que el usuario sea el propietario de la receta
        $this->authorize('update', $receta);

        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'cantidad' => 'required|string|max:50',
            'unidad' => 'required|string|max:50',
        ]);

        $ingrediente = $receta->ingredientes()->create($data);

        return new IngredienteResource($ingrediente);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receta $receta, Ingrediente $ingrediente)
    {
        // Verificar que el ingrediente pertenece a la receta
        if ($ingrediente->receta_id !== $receta->id) {
            return response()->json([
                'message' => 'Ingrediente no encontrado en esta receta'
            ], 404);
        }

        return new IngredienteResource($ingrediente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receta $receta, Ingrediente $ingrediente)
    {
        // Verificar que el ingrediente pertenece a la receta
        if ($ingrediente->receta_id !== $receta->id) {
            return response()->json([
                'message' => 'Ingrediente no encontrado en esta receta'
            ], 404);
        }

        $this->authorize('update', $ingrediente);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:200',
            'cantidad' => 'sometimes|required|string|max:50',
            'unidad' => 'sometimes|required|string|max:50',
        ]);

        $ingrediente->update($data);

        return new IngredienteResource($ingrediente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receta $receta, Ingrediente $ingrediente)
    {
        // Verificar que el ingrediente pertenece a la receta
        if ($ingrediente->receta_id !== $receta->id) {
            return response()->json([
                'message' => 'Ingrediente no encontrado en esta receta'
            ], 404);
        }

        $this->authorize('delete', $ingrediente);

        $ingrediente->delete();

        return response()->json([
            'message' => 'Ingrediente eliminado correctamente'
        ], 200);
    }
}
