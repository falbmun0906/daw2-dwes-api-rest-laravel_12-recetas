<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecetaController;
use App\Http\Controllers\Api\IngredienteController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\SwaggerController;
// Guía docente: ver docs/02_rutas_api.md.

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Rutas de recetas
    Route::apiResource('recetas', RecetaController::class);

    // Rutas de ingredientes (anidadas bajo recetas)
    Route::apiResource('recetas.ingredientes', IngredienteController::class)
        ->except(['index', 'show']);
    Route::get('recetas/{receta}/ingredientes', [IngredienteController::class, 'index']);
    Route::get('recetas/{receta}/ingredientes/{ingrediente}', [IngredienteController::class, 'show']);

    // Rutas de comentarios (anidadas bajo recetas)
    Route::apiResource('recetas.comentarios', ComentarioController::class)
        ->except(['index', 'show']);
    Route::get('recetas/{receta}/comentarios', [ComentarioController::class, 'index']);
    Route::get('recetas/{receta}/comentarios/{comentario}', [ComentarioController::class, 'show']);

    // Rutas de likes
    Route::post('recetas/{receta}/like', [LikeController::class, 'toggle']);
    Route::get('recetas/{receta}/likes', [LikeController::class, 'count']);
    Route::get('recetas/{receta}/like/status', [LikeController::class, 'status']);
});

Route::get('/ping', fn () => response()->json(['pong' => true]));

// Swagger/OpenAPI spec
Route::get('/docs/openapi.json', [SwaggerController::class, 'spec']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

/*
 * Alternativa Laravel 11/12 (autorización por middleware):
 *
 * Route::put('/recetas/{receta}', [RecetaController::class, 'update'])
 *     ->middleware(['auth:sanctum', 'can:update,receta']);
 *
 * Route::delete('/recetas/{receta}', [RecetaController::class, 'destroy'])
 *     ->middleware(['auth:sanctum', 'can:delete,receta']);
 */
