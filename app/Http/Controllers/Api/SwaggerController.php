<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SwaggerController extends Controller
{
    /**
     * Retorna la especificación OpenAPI 3.0
     */
    public function spec()
    {
        // Obtener la URL base desde la solicitud actual
        $baseUrl = url('/');

        return response()->json([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'API REST de Recetas',
                'description' => 'API completa para gestión de recetas con ingredientes, likes y comentarios',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'Soporte API',
                    'email' => 'api@recetas.local'
                ],
                'license' => [
                    'name' => 'MIT',
                ]
            ],
            'servers' => [
                [
                    'url' => $baseUrl,
                    'description' => 'Servidor actual',
                ]
            ],
            'tags' => [
                ['name' => 'Autenticación', 'description' => 'Endpoints de autenticación'],
                ['name' => 'Recetas', 'description' => 'Gestión de recetas'],
                ['name' => 'Ingredientes', 'description' => 'Gestión de ingredientes de recetas'],
                ['name' => 'Likes', 'description' => 'Sistema de likes/favoritos'],
                ['name' => 'Comentarios', 'description' => 'Sistema de comentarios'],
            ],
            'paths' => [
                '/api/auth/register' => $this->getAuthRegisterPath(),
                '/api/auth/login' => $this->getAuthLoginPath(),
                '/api/auth/logout' => $this->getAuthLogoutPath(),
                '/api/auth/me' => $this->getAuthMePath(),
                '/api/recetas' => $this->getRecetasPath(),
                '/api/recetas/{receta}' => $this->getRecetaPath(),
                '/api/recetas/{receta}/ingredientes' => $this->getIngredientesPath(),
                '/api/recetas/{receta}/ingredientes/{ingrediente}' => $this->getIngredientePath(),
                '/api/recetas/{receta}/like' => $this->getLikePath(),
                '/api/recetas/{receta}/likes' => $this->getLikesCountPath(),
                '/api/recetas/{receta}/comentarios' => $this->getComentariosPath(),
                '/api/recetas/{receta}/comentarios/{comentario}' => $this->getComentarioPath(),
            ],
            'components' => [
                'securitySchemes' => [
                    'sanctum' => [
                        'type' => 'apiKey',
                        'description' => 'Token de autenticación con Sanctum',
                        'name' => 'Authorization',
                        'in' => 'header',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ]
                ],
                'schemas' => [
                    'User' => $this->getUserSchema(),
                    'Receta' => $this->getRecetaSchema(),
                    'Ingrediente' => $this->getIngredienteSchema(),
                    'Comentario' => $this->getComentarioSchema(),
                    'Like' => $this->getLikeSchema(),
                    'Error' => $this->getErrorSchema(),
                    'ValidationError' => $this->getValidationErrorSchema(),
                ]
            ]
        ]);
    }

    private function getAuthRegisterPath()
    {
        return [
            'post' => [
                'tags' => ['Autenticación'],
                'summary' => 'Registrar nuevo usuario',
                'description' => 'Crear una nueva cuenta de usuario',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Juan Pérez'],
                                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'juan@example.com'],
                                    'password' => ['type' => 'string', 'format' => 'password', 'example' => 'password123'],
                                    'password_confirmation' => ['type' => 'string', 'format' => 'password', 'example' => 'password123'],
                                ],
                                'required' => ['name', 'email', 'password', 'password_confirmation']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Usuario registrado exitosamente',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string'],
                                        'user' => ['\$ref' => '#/components/schemas/User']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '422' => ['description' => 'Validación fallida']
                ]
            ]
        ];
    }

    private function getAuthLoginPath()
    {
        return [
            'post' => [
                'tags' => ['Autenticación'],
                'summary' => 'Login de usuario',
                'description' => 'Obtener token de autenticación',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'email' => ['type' => 'string', 'format' => 'email', 'example' => 'admin@demo.local'],
                                    'password' => ['type' => 'string', 'format' => 'password', 'example' => 'password'],
                                ],
                                'required' => ['email', 'password']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Login exitoso',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string', 'example' => 'eyJ0eXAiOiJKV1Qi...'],
                                        'user' => ['\$ref' => '#/components/schemas/User']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['description' => 'Credenciales inválidas']
                ]
            ]
        ];
    }

    private function getAuthLogoutPath()
    {
        return [
            'post' => [
                'tags' => ['Autenticación'],
                'summary' => 'Logout de usuario',
                'security' => [['sanctum' => []]],
                'responses' => [
                    '200' => ['description' => 'Logout exitoso'],
                    '401' => ['description' => 'No autenticado']
                ]
            ]
        ];
    }

    private function getAuthMePath()
    {
        return [
            'get' => [
                'tags' => ['Autenticación'],
                'summary' => 'Obtener datos del usuario autenticado',
                'security' => [['sanctum' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Datos del usuario',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/User']
                            ]
                        ]
                    ],
                    '401' => ['description' => 'No autenticado']
                ]
            ]
        ];
    }

    private function getRecetasPath()
    {
        return [
            'get' => [
                'tags' => ['Recetas'],
                'summary' => 'Listar recetas',
                'description' => 'Obtener lista paginada de recetas con filtros opcionales',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    [
                        'name' => 'q',
                        'in' => 'query',
                        'description' => 'Búsqueda de texto en título o descripción',
                        'schema' => ['type' => 'string', 'example' => 'tortilla']
                    ],
                    [
                        'name' => 'ingrediente',
                        'in' => 'query',
                        'description' => 'Filtrar por nombre de ingrediente',
                        'schema' => ['type' => 'string', 'example' => 'huevo']
                    ],
                    [
                        'name' => 'min_likes',
                        'in' => 'query',
                        'description' => 'Mínimo número de likes',
                        'schema' => ['type' => 'integer', 'example' => 5]
                    ],
                    [
                        'name' => 'sort',
                        'in' => 'query',
                        'description' => 'Campo de ordenamiento (prefijo - para descendente)',
                        'schema' => ['type' => 'string', 'enum' => ['titulo', '-titulo', 'created_at', '-created_at', 'likes_count', '-likes_count']]
                    ],
                    [
                        'name' => 'per_page',
                        'in' => 'query',
                        'description' => 'Items por página',
                        'schema' => ['type' => 'integer', 'default' => 10]
                    ],
                    [
                        'name' => 'page',
                        'in' => 'query',
                        'description' => 'Número de página',
                        'schema' => ['type' => 'integer', 'default' => 1]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de recetas',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['\$ref' => '#/components/schemas/Receta']
                                        ],
                                        'meta' => ['type' => 'object']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'tags' => ['Recetas'],
                'summary' => 'Crear nueva receta',
                'security' => [['sanctum' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'titulo' => ['type' => 'string', 'maxLength' => 200],
                                    'descripcion' => ['type' => 'string'],
                                    'instrucciones' => ['type' => 'string'],
                                ],
                                'required' => ['titulo', 'descripcion', 'instrucciones']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Receta creada',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Receta']
                            ]
                        ]
                    ],
                    '422' => ['description' => 'Validación fallida']
                ]
            ]
        ];
    }

    private function getRecetaPath()
    {
        return [
            'get' => [
                'tags' => ['Recetas'],
                'summary' => 'Obtener receta con detalles',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    [
                        'name' => 'receta',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Detalle de receta',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Receta']
                            ]
                        ]
                    ],
                    '404' => ['description' => 'Receta no encontrada']
                ]
            ],
            'put' => [
                'tags' => ['Recetas'],
                'summary' => 'Actualizar receta',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    [
                        'name' => 'receta',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ]
                ],
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'titulo' => ['type' => 'string'],
                                    'descripcion' => ['type' => 'string'],
                                    'instrucciones' => ['type' => 'string'],
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Receta actualizada',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Receta']
                            ]
                        ]
                    ],
                    '403' => ['description' => 'No autorizado'],
                    '404' => ['description' => 'Receta no encontrada']
                ]
            ],
            'delete' => [
                'tags' => ['Recetas'],
                'summary' => 'Eliminar receta',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    [
                        'name' => 'receta',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Receta eliminada'],
                    '403' => ['description' => 'No autorizado'],
                    '404' => ['description' => 'Receta no encontrada']
                ]
            ]
        ];
    }

    private function getIngredientesPath()
    {
        return [
            'get' => [
                'tags' => ['Ingredientes'],
                'summary' => 'Listar ingredientes de una receta',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    [
                        'name' => 'receta',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de ingredientes',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => ['\$ref' => '#/components/schemas/Ingrediente']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'tags' => ['Ingredientes'],
                'summary' => 'Agregar ingrediente a receta',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    [
                        'name' => 'receta',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'nombre' => ['type' => 'string', 'maxLength' => 200, 'example' => 'Huevos'],
                                    'cantidad' => ['type' => 'string', 'maxLength' => 50, 'example' => '4'],
                                    'unidad' => ['type' => 'string', 'maxLength' => 50, 'example' => 'ud', 'enum' => ['g', 'ml', 'ud', 'cucharadas', 'tazas', 'kg', 'l']],
                                ],
                                'required' => ['nombre', 'cantidad', 'unidad']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Ingrediente agregado',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Ingrediente']
                            ]
                        ]
                    ],
                    '403' => ['description' => 'No autorizado para modificar esta receta']
                ]
            ]
        ];
    }

    private function getIngredientePath()
    {
        return [
            'get' => [
                'tags' => ['Ingredientes'],
                'summary' => 'Obtener ingrediente específico',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'ingrediente', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Detalle del ingrediente',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Ingrediente']
                            ]
                        ]
                    ],
                    '404' => ['description' => 'Ingrediente no encontrado']
                ]
            ],
            'put' => [
                'tags' => ['Ingredientes'],
                'summary' => 'Actualizar ingrediente',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'ingrediente', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'nombre' => ['type' => 'string'],
                                    'cantidad' => ['type' => 'string'],
                                    'unidad' => ['type' => 'string'],
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Ingrediente actualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Ingrediente']
                            ]
                        ]
                    ],
                    '403' => ['description' => 'No autorizado']
                ]
            ],
            'delete' => [
                'tags' => ['Ingredientes'],
                'summary' => 'Eliminar ingrediente',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'ingrediente', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => ['description' => 'Ingrediente eliminado'],
                    '403' => ['description' => 'No autorizado']
                ]
            ]
        ];
    }

    private function getLikePath()
    {
        return [
            'post' => [
                'tags' => ['Likes'],
                'summary' => 'Toggle like de una receta',
                'description' => 'Agregar o quitar un like a una receta. Si el usuario ya le dio like, se elimina. Si no, se agrega.',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Toggle realizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                        'liked' => ['type' => 'boolean'],
                                        'likes_count' => ['type' => 'integer']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getLikesCountPath()
    {
        return [
            'get' => [
                'tags' => ['Likes'],
                'summary' => 'Obtener cantidad de likes de una receta',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cantidad de likes',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'receta_id' => ['type' => 'integer'],
                                        'likes_count' => ['type' => 'integer']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getComentariosPath()
    {
        return [
            'get' => [
                'tags' => ['Comentarios'],
                'summary' => 'Listar comentarios de una receta',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de comentarios',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'array',
                                    'items' => ['\$ref' => '#/components/schemas/Comentario']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'tags' => ['Comentarios'],
                'summary' => 'Agregar comentario a receta',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'texto' => ['type' => 'string', 'maxLength' => 1000, 'example' => '¡Excelente receta!'],
                                ],
                                'required' => ['texto']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Comentario agregado',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Comentario']
                            ]
                        ]
                    ],
                    '422' => ['description' => 'Validación fallida']
                ]
            ]
        ];
    }

    private function getComentarioPath()
    {
        return [
            'get' => [
                'tags' => ['Comentarios'],
                'summary' => 'Obtener comentario específico',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'comentario', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Detalle del comentario',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Comentario']
                            ]
                        ]
                    ],
                    '404' => ['description' => 'Comentario no encontrado']
                ]
            ],
            'put' => [
                'tags' => ['Comentarios'],
                'summary' => 'Actualizar comentario',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'comentario', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'texto' => ['type' => 'string', 'maxLength' => 1000],
                                ],
                                'required' => ['texto']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Comentario actualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => ['\$ref' => '#/components/schemas/Comentario']
                            ]
                        ]
                    ],
                    '403' => ['description' => 'No autorizado'],
                    '404' => ['description' => 'Comentario no encontrado']
                ]
            ],
            'delete' => [
                'tags' => ['Comentarios'],
                'summary' => 'Eliminar comentario',
                'security' => [['sanctum' => []]],
                'parameters' => [
                    ['name' => 'receta', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                    ['name' => 'comentario', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]
                ],
                'responses' => [
                    '200' => ['description' => 'Comentario eliminado'],
                    '403' => ['description' => 'No autorizado'],
                    '404' => ['description' => 'Comentario no encontrado']
                ]
            ]
        ];
    }

    private function getUserSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
                'email' => ['type' => 'string', 'format' => 'email'],
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time'],
            ]
        ];
    }

    private function getRecetaSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'titulo' => ['type' => 'string'],
                'descripcion' => ['type' => 'string'],
                'instrucciones' => ['type' => 'string'],
                'publicada' => ['type' => 'boolean'],
                'imagen' => ['type' => 'string', 'nullable' => true],
                'user_id' => ['type' => 'integer'],
                'likes_count' => ['type' => 'integer'],
                'ingredientes' => [
                    'type' => 'array',
                    'items' => ['\$ref' => '#/components/schemas/Ingrediente']
                ],
                'comentarios' => [
                    'type' => 'array',
                    'items' => ['\$ref' => '#/components/schemas/Comentario']
                ],
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time'],
            ]
        ];
    }

    private function getIngredienteSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'nombre' => ['type' => 'string'],
                'cantidad' => ['type' => 'string'],
                'unidad' => ['type' => 'string'],
                'receta_id' => ['type' => 'integer'],
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time'],
            ]
        ];
    }

    private function getComentarioSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'texto' => ['type' => 'string'],
                'receta_id' => ['type' => 'integer'],
                'user_id' => ['type' => 'integer'],
                'user' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'email' => ['type' => 'string'],
                    ]
                ],
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time'],
            ]
        ];
    }

    private function getLikeSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'ID único del like',
                    'example' => 1
                ],
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'ID del usuario que dio like',
                    'example' => 1
                ],
                'receta_id' => [
                    'type' => 'integer',
                    'description' => 'ID de la receta que recibió el like',
                    'example' => 5
                ],
                'user' => [
                    'type' => 'object',
                    'description' => 'Información del usuario (cuando se incluye con eager loading)',
                    'properties' => [
                        'id' => ['type' => 'integer', 'example' => 1],
                        'name' => ['type' => 'string', 'example' => 'Juan Pérez'],
                        'email' => ['type' => 'string', 'example' => 'juan@example.com'],
                    ]
                ],
                'receta' => [
                    'type' => 'object',
                    'description' => 'Información de la receta (cuando se incluye con eager loading)',
                    'properties' => [
                        'id' => ['type' => 'integer', 'example' => 5],
                        'titulo' => ['type' => 'string', 'example' => 'Paella Valenciana'],
                        'descripcion' => ['type' => 'string', 'example' => 'Receta tradicional española'],
                    ]
                ],
                'created_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'description' => 'Fecha y hora de creación del like',
                    'example' => '2026-01-27T10:30:00.000000Z'
                ],
                'updated_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'description' => 'Fecha y hora de última actualización',
                    'example' => '2026-01-27T10:30:00.000000Z'
                ],
            ],
            'required' => ['user_id', 'receta_id']
        ];
    }

    private function getErrorSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string'],
                'status' => ['type' => 'integer'],
            ]
        ];
    }

    private function getValidationErrorSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string'],
                'errors' => [
                    'type' => 'object',
                    'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]
                ]
            ]
        ];
    }
}
