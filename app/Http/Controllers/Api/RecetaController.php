<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Receta;
use Illuminate\Http\Request;
use App\Services\RecetaService;
use App\Http\Resources\RecetaResource;

class RecetaController extends Controller
{
    // Guía docente: ver docs/03_controladores.md.
    // Listar todas las recetas
    public function index(Request $request)
    {
        $query = Receta::query();

        // Búsqueda por texto
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        } //PostgreSQL ✔ (ILIKE)

        // Búsqueda por ingrediente (nuevo)
        if ($ingrediente = $request->query('ingrediente')) {
            $query->whereHas('ingredientes', function ($q) use ($ingrediente) {
                $q->where('nombre', 'ILIKE', "%{$ingrediente}%");
            });
        }

        // Filtrar por minimo de likes (nuevo)
        // Usamos whereHas con un count manual para evitar problemas con having en la paginacion
        if ($minLikes = $request->query('min_likes')) {
            $minLikesInt = (int)$minLikes;
            $query->whereHas('likes', function ($q) {}, '>=', $minLikesInt);
        }

        // Cargar contador de likes siempre
        $query->withCount('likes');

        // Ordenacion
        $allowedSorts = ['titulo', 'created_at', 'likes_count'];
        if ($sort = $request->query('sort')) {
            $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');

            if (in_array($field, $allowedSorts)) {
                if ($field === 'likes_count') {
                    $query->orderBy('likes_count', $direction);
                } else {
                    $query->orderBy($field, $direction);
                }
            }
        }

        // Paginación
        $perPage = min((int) $request->query('per_page', 10), 50);
        $recetas = $query->paginate($perPage);

        return RecetaResource::collection($recetas);
    }

    // Crear una nueva receta
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'instrucciones' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $request->file('imagen')->store('recetas', 'public');
        }

        $receta = Receta::create([
            'user_id' => $request->user()->id,
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'instrucciones' => $data['instrucciones'],
            'imagen' => $imagenPath,
        ]);

        return response()->json($receta, 201);
    }

    // Mostrar una receta específica
    public function show(Receta $receta)
    {
        // Cargar relaciones
        $receta->load(['ingredientes', 'comentarios.user']);
        $receta->loadCount('likes');

        return new RecetaResource($receta);
    }

    // Actualizar una receta existente
    public function update(Request $request, Receta $receta, RecetaService $recetaService)
    {
        // Forma clásica (Laravel <=10, muy común en empresa)
        $this->authorize('update', $receta);

        /*
         * Alternativa recomendada en Laravel 11/12:
         *
         * use Illuminate\Support\Facades\Gate;
         * Gate::authorize('update', $receta);
         */
        // Política de negocio (si se puede)
        $recetaService->assertCanBeModified($receta);

        $data = $request->validate([
            'titulo' => 'sometimes|required|string|max:200',
            'descripcion' => 'sometimes|required|string',
            'instrucciones' => 'sometimes|required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Manejar la imagen si se proporciona
        if ($request->hasFile('imagen')) {
            // Eliminar la imagen anterior si existe
            if ($receta->imagen) {
                \Storage::disk('public')->delete($receta->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('recetas', 'public');
        }

        $receta->update($data);

        return response()->json($receta);
    }


    // Eliminar una receta
    public function destroy(Receta $receta)
    {
        // 1. Autorización (403 si falla)
        $this->authorize('delete', $receta);

        /*
         * Alternativa Laravel 11/12:
         * Gate::authorize('delete', $receta);
         */



        // 2. Acción
        $receta->delete();

        return response()->json(['message' => 'Receta eliminada']);
    }
}
