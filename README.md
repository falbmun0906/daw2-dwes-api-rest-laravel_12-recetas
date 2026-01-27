# API REST de Recetas - Laravel 12

API REST completa para gestión de recetas con ingredientes, likes y comentarios. Proyecto desarrollado con Laravel 12, PostgreSQL, Sanctum y Spatie Permissions.

## 🚀 Características Principales

### ✅ Funcionalidades Obligatorias
- **CRUD de Recetas** - Crear, leer, actualizar y eliminar recetas
- **Ingredientes** - Gestión completa de ingredientes por receta
- **Likes** - Sistema de "me gusta" con toggle
- **Comentarios** - Sistema de comentarios con autorización
- **Autenticación** - JWT con Laravel Sanctum
- **Autorización** - Policies y roles (admin/user) con Spatie

### 🎯 Funcionalidades Adicionales
- **Upload de Imágenes** - Subida y validación de imágenes
- **Búsquedas Avanzadas** - Filtros por ingrediente, likes, texto
- **Ordenamiento** - Por popularidad, fecha, título
- **Tests Completos** - 15+ feature tests
- **Swagger/OpenAPI** - Documentación interactiva 🆕

## 📋 Requisitos

- Docker Desktop
- Git
- HTTPie (opcional, para testing)

## 🔧 Instalación Rápida

```bash
# 1. Clonar repositorio
git clone <url>
cd daw2-dwes-api-rest-laravel_12-recetas

# 2. Instalar dependencias
docker run --rm -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php83-composer:latest composer install --ignore-platform-reqs

# 3. Configurar entorno
cp .env.example .env

# 4. Iniciar contenedores
./vendor/bin/sail up -d

# 5. Generar key y migrar
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan storage:link

# 6. Verificar
./vendor/bin/sail artisan test
```

## 📚 Documentación

- **[ENTREGA.md](ENTREGA.md)** - Documentación completa del proyecto
- **[SWAGGER.md](SWAGGER.md)** - Documentación interactiva OpenAPI/Swagger 🆕
- **[HTTPIE_COMMANDS.md](HTTPIE_COMMANDS.md)** - Ejemplos de uso con HTTPie
- **[INSTALACION.md](INSTALACION.md)** - Guía de instalación paso a paso

## 🎨 Swagger UI

Accede a la documentación interactiva con Swagger UI:

```
http://localhost:8000/api-docs.html
```

En Swagger UI puedes:
- ✅ Ver todos los endpoints documentados
- ✅ Probar endpoints en tiempo real
- ✅ Ver esquemas de request/response
- ✅ Autenticarse con Bearer Token
- ✅ Ver ejemplos de uso

**OpenAPI Spec JSON**: http://localhost:8000/api/docs/openapi.json

## 🧪 Testing

```bash
# Ejecutar todos los tests
./vendor/bin/sail artisan test

# Tests específicos
./vendor/bin/sail artisan test --filter=IngredienteTest
./vendor/bin/sail artisan test --filter=LikeTest
./vendor/bin/sail artisan test --filter=ComentarioTest

# Con cobertura
./vendor/bin/sail artisan test --coverage
```

## 🔐 Usuarios de Prueba

| Email | Password | Rol |
|-------|----------|-----|
| admin@demo.local | password | admin |
| user@demo.local | password | user |

## 🛣️ Endpoints Principales

### Autenticación
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me
```

### Recetas
```
GET    /api/recetas
POST   /api/recetas
GET    /api/recetas/{id}
PUT    /api/recetas/{id}
DELETE /api/recetas/{id}
```

### Ingredientes
```
GET    /api/recetas/{id}/ingredientes
POST   /api/recetas/{id}/ingredientes
PUT    /api/recetas/{id}/ingredientes/{ingrediente}
DELETE /api/recetas/{id}/ingredientes/{ingrediente}
```

### Likes
```
POST   /api/recetas/{id}/like (toggle)
GET    /api/recetas/{id}/likes
GET    /api/recetas/{id}/like/status
```

### Comentarios
```
GET    /api/recetas/{id}/comentarios
POST   /api/recetas/{id}/comentarios
PUT    /api/recetas/{id}/comentarios/{comentario}
DELETE /api/recetas/{id}/comentarios/{comentario}
```

## 💡 Ejemplo de Uso

```bash
# 1. Login
http POST :8000/api/auth/login email=admin@demo.local password=password

# 2. Guardar token
export TOKEN="<tu_token>"

# 3. Crear receta
http POST :8000/api/recetas "Authorization:Bearer $TOKEN" \
  titulo="Tortilla Española" \
  descripcion="Clásica" \
  instrucciones="..."

# 4. Agregar ingredientes
http POST :8000/api/recetas/1/ingredientes "Authorization:Bearer $TOKEN" \
  nombre="Huevos" cantidad="4" unidad="ud"

# 5. Dar like
http POST :8000/api/recetas/1/like "Authorization:Bearer $TOKEN"

# 6. Comentar
http POST :8000/api/recetas/1/comentarios "Authorization:Bearer $TOKEN" \
  texto="¡Excelente receta!"
```

Ver más ejemplos en **[HTTPIE_COMMANDS.md](HTTPIE_COMMANDS.md)**

## 🏗️ Tecnologías

- **Laravel 12** - Framework PHP
- **PostgreSQL** - Base de datos
- **Laravel Sanctum** - Autenticación API
- **Spatie Laravel Permission** - Roles y permisos
- **Laravel Sail** - Docker development environment
- **PHPUnit** - Testing
- **OpenAPI 3.0** - Documentación con Swagger

## 📁 Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── RecetaController.php
│   │   ├── IngredienteController.php
│   │   ├── ComentarioController.php
│   │   ├── LikeController.php
│   │   └── SwaggerController.php (nuevo)
│   └── Resources/
│       ├── RecetaResource.php
│       ├── IngredienteResource.php
│       └── ComentarioResource.php
├── Models/
│   ├── Receta.php
│   ├── Ingrediente.php
│   └── Comentario.php
└── Policies/
    ├── RecetaPolicy.php
    ├── IngredientePolicy.php
    └── ComentarioPolicy.php
```

## 🎯 Características Implementadas

- ✅ Ingredientes con CRUD completo
- ✅ Sistema de likes con toggle
- ✅ Comentarios con autorización
- ✅ Upload de imágenes
- ✅ Búsquedas avanzadas
- ✅ Filtros y ordenamiento
- ✅ Policies completas
- ✅ API Resources
- ✅ 15+ tests automatizados
- ✅ Seeders con datos de ejemplo
- ✅ **Documentación Swagger/OpenAPI completa** 🆕

## 🤝 Contribuir

1. Fork el proyecto
2. Crea tu feature branch (`git checkout -b feature/amazing-feature`)
3. Commit tus cambios (`git commit -m 'Add amazing feature'`)
4. Push al branch (`git push origin feature/amazing-feature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto es software de código abierto bajo la licencia MIT.

## 👥 Créditos

Desarrollado como proyecto educativo para el curso de Desarrollo Web en Entorno Servidor.
