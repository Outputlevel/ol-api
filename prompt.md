# OL-API: Especificación de Requisitos (ORIGINAL)

Este archivo contiene los requisitos originales del cliente para el plugin OL-API.

**Para la especificación técnica completa**, ver: [ARCHITECTURE.md](ARCHITECTURE.md)

---

## Rol Definido

Actúa como un arquitecto senior de software especializado en WordPress, REST APIs, seguridad, y diseño de plugins empresariales.

### 1.1 Descripción del Sistema

**OL-API** es un plugin empresarial de WordPress que transforma la plataforma en una API REST completamente personalizable y configurable. El sistema permite a administradores crear endpoints personalizados sin escribir código, exponiendo datos de WordPress (posts, CPTs, taxonomías, usuarios, medios) mediante una interfaz administrativa intuitiva.

### 1.2 Propuesta de Valor

- **Configuración sin código**: Dashboard administrativo completo para crear y gestionar endpoints
- **Seguridad granular**: Autenticación multimodo y control de permisos por rol y endpoint
- **Descubrimiento automático**: Detección inteligente de campos (core, custom, ACF, JetEngine)
- **Documentación automática**: Generación dinámica de especificaciones OpenAPI 3.0
- **Escalabilidad**: Arquitectura modular desacoplada soporta miles de endpoints
- **Extensibilidad**: Sistema de hooks/filters para integraciones personalizadas

### 1.3 Casos de Uso Principales

| Caso de Uso | Descripción |
|------------|-------------|
| Mobile Apps | Exponer datos WordPress a aplicaciones móviles nativas |
| Headless CMS | Usar WordPress como CMS sin su frontend |
| Terceros Integrados | Permitir a servicios externos acceder a datos mediante API segura |
| Migraciones | Función como capa de datos durante migraciones |
| Sindicación | Distribuir contenido a múltiples destinos |
| Dashboards Externos | Datos WordPress en aplicaciones de terceros |

### 1.4 Restricciones y Limitaciones

- Requiere WordPress 5.9+
- PHP 8.0+ (tipos estáticos, union types)
- Base de datos con soporte para transacciones
- No soporta GraphQL (fase 2+)
- Limite inicial: 100 endpoints simultáneamente activos

---

## 2. Arquitectura General

### 2.1 Visión en Capas

```
┌─────────────────────────────────────────────────────┐
│          Admin Layer (Dashboard UI)                 │
│  - Endpoint Manager                                 │
│  - Auth Configuration                               │
│  - Permissions UI                                   │
│  - Settings & Logs                                  │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│          API Layer (REST Endpoints)                 │
│  - Router                                           │
│  - Request Dispatcher                               │
│  - Response Formatter                               │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│       Auth & Permissions Layer                      │
│  - Auth Manager (multimode)                         │
│  - Permission Evaluator                             │
│  - Role/Capability Resolver                         │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│    Business Logic Layer (Field & Data Processing)   │
│  - Field Discovery Service                          │
│  - Field Value Resolver                             │
│  - Media Handler                                    │
│  - Relationship Resolver                            │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│      Infrastructure Layer (Storage & Cache)         │
│  - Configuration Repository                         │
│  - Logs Repository                                  │
│  - Cache Manager                                    │
│  - Database Transactions                            │
└─────────────────────────────────────────────────────┘
```

### 2.2 Descripción de Capas

| Capa | Responsabilidad | Componentes |
|------|-----------------|------------|
| **Admin** | Interfaz administrativa para configurar endpoints, auth, permisos | Pages, Forms, Settings API |
| **API** | Manejo de requests HTTP y enrutamiento | Router, Controllers, Response Handler |
| **Auth & Perms** | Autenticación y autorización granular | Auth Strategies, Permission Evaluator |
| **Business Logic** | Procesamiento de datos y campos | Field Discovery, Value Resolvers |
| **Infrastructure** | Persistencia, caché y logs | Repositories, Cache, Logger |

---

## 3. Estructura de Carpetas

```
ol-api/
├── ol-api.php                          // Archivo principal del plugin
├── README.md                           // Documentación del usuario
├── ARCHITECTURE.md                     // Este archivo
│
├── includes/
│   ├── Plugin.php                      // Clase principal del plugin
│   ├── Setup.php                       // Hooks de activación/desactivación
│   ├── Loader.php                      // Cargador de clases (autoload)
│   │
│   ├── Core/                           // Núcleo del plugin
│   │   ├── PluginInterface.php         // Interfaz principal
│   │   ├── Registry.php                // Registro de componentes
│   │   └── Config.php                  // Configuración global
│   │
│   ├── API/                            // Capa de API REST
│   │   ├── Router.php                  // Enrutador de requests
│   │   ├── RequestHandler.php          // Procesador de requests
│   │   ├── ResponseFormatter.php       // Formateador de respuestas
│   │   ├── EndpointController.php      // Controlador de endpoints
│   │   ├── Validators/
│   │   │   ├── RequestValidator.php    // Validación de requests
│   │   │   └── FieldValidator.php      // Validación de campos
│   │   └── Exceptions/
│   │       ├── APIException.php
│   │       ├── ValidationException.php
│   │       └── AuthException.php
│   │
│   ├── Auth/                           // Sistema de autenticación
│   │   ├── AuthManager.php             // Gestor de autenticación
│   │   ├── Strategies/
│   │   │   ├── StrategyInterface.php   // Interfaz de estrategia
│   │   │   ├── APIKeyStrategy.php      // Autenticación por API Key
│   │   │   ├── BearerTokenStrategy.php // Bearer Token
│   │   │   ├── JWTStrategy.php         // JWT
│   │   │   ├── AppPasswordStrategy.php // Application Passwords
│   │   │   ├── OAuthStrategy.php       // OAuth (preparado)
│   │   │   └── NoAuthStrategy.php      // Sin autenticación
│   │   ├── TokenManager.php            // Gestión de tokens
│   │   └── CredentialStore.php         // Almacén de credenciales
│   │
│   ├── Permissions/                    // Sistema de permisos
│   │   ├── PermissionManager.php       // Evaluador de permisos
│   │   ├── RoleResolver.php            // Resolutor de roles
│   │   ├── CapabilityEvaluator.php     // Evaluador de capacidades
│   │   ├── EndpointPermission.php      // Permisos por endpoint
│   │   └── MediaPermission.php         // Permisos para media
│   │
│   ├── Fields/                         // Descubrimiento y resolución de campos
│   │   ├── FieldDiscoveryService.php   // Detector de campos
│   │   ├── FieldValueResolver.php      // Resolutor de valores
│   │   ├── Providers/
│   │   │   ├── ProviderInterface.php   // Interfaz de proveedor
│   │   │   ├── CoreFieldProvider.php   // Campos core
│   │   │   ├── MetaFieldProvider.php   // Meta fields
│   │   │   ├── ACFProvider.php         // ACF Fields
│   │   │   └── JetEngineProvider.php   // JetEngine Fields
│   │   ├── FieldRegistry.php           // Registro de campos
│   │   └── FieldTypeMapper.php         // Mapeo de tipos
│   │
│   ├── Media/                          // Manejo de medios
│   │   ├── MediaHandler.php            // Procesador de media
│   │   ├── MediaUploader.php           // Subida de archivos
│   │   ├── MediaValidator.php          // Validación de archivos
│   │   ├── ImageProcessor.php          // Procesamiento de imágenes
│   │   └── MediaAttacher.php           // Asociación a posts
│   │
│   ├── Docs/                           // Generación de documentación
│   │   ├── OpenAPIGenerator.php        // Generador OpenAPI 3.0
│   │   ├── SchemaBuilder.php           // Constructor de schemas
│   │   ├── DocsCache.php               // Caché de documentación
│   │   └── SwaggerUIManager.php        // Integración Swagger UI
│   │
│   ├── Models/                         // Modelos de datos
│   │   ├── Endpoint.php                // Modelo de Endpoint
│   │   ├── EndpointField.php           // Campos del endpoint
│   │   ├── APIKey.php                  // Credential de API Key
│   │   ├── AuthConfig.php              // Configuración de auth
│   │   ├── PermissionRule.php          // Reglas de permiso
│   │   └── APILog.php                  // Log de API
│   │
│   ├── Repositories/                   // Acceso a datos
│   │   ├── EndpointRepository.php      // Persistencia de endpoints
│   │   ├── APIKeyRepository.php        // Persistencia API Keys
│   │   ├── LogRepository.php           // Persistencia de logs
│   │   ├── SettingsRepository.php      // Persistencia de settings
│   │   └── BaseRepository.php          // Clase base
│   │
│   ├── Infrastructure/                 // Capa de infraestructura
│   │   ├── Database/
│   │   │   ├── DatabaseManager.php     // Gestor de BD
│   │   │   ├── Migrations.php          // Migraciones
│   │   │   └── Tables.php              // Definición de tablas
│   │   ├── Cache/
│   │   │   ├── CacheManager.php        // Gestor de caché
│   │   │   ├── TransientCache.php      // Caché de transients
│   │   │   └── QueryCache.php          // Caché de consultas
│   │   ├── Logger/
│   │   │   ├── Logger.php              // Sistema de logs
│   │   │   ├── DatabaseLogHandler.php  // Handler de DB
│   │   │   └── FileLogHandler.php      // Handler de archivos
│   │   └── Security/
│   │       ├── Encrypter.php           // Encriptación
│   │       ├── Sanitizer.php           // Sanitización
│   │       └── RateLimiter.php         // Rate limiting
│   │
│   ├── Admin/                          // Capa administrativa
│   │   ├── Pages/
│   │   │   ├── EndpointPage.php        // Gestión de endpoints
│   │   │   ├── AuthPage.php            // Configuración auth
│   │   │   ├── PermissionsPage.php     // Configuración permisos
│   │   │   ├── SettingsPage.php        // Configuración global
│   │   │   ├── DocsPage.php            // Documentación
│   │   │   └── LogsPage.php            // Visor de logs
│   │   ├── Forms/
│   │   │   ├── EndpointForm.php        // Formulario endpoint
│   │   │   ├── AuthForm.php            // Formulario auth
│   │   │   └── PermissionForm.php      // Formulario permisos
│   │   ├── Assets/
│   │   │   ├── js/
│   │   │   │   ├── admin.js
│   │   │   │   ├── endpoint-form.js
│   │   │   │   └── permissions.js
│   │   │   └── css/
│   │   │       ├── admin.css
│   │   │       └── dashboard.css
│   │   └── Menu.php                    // Menú administrativo
│   │
│   ├── Traits/                         // Traits reutilizables
│   │   ├── SingletonTrait.php          // Singleton pattern
│   │   ├── HookableTrait.php           // Agregar hooks
│   │   ├── LoggableTrait.php           // Logging
│   │   └── CacheableTrait.php          // Caché
│   │
│   └── Helpers/                        // Funciones auxiliares
│       ├── ArrayHelper.php
│       ├── StringHelper.php
│       ├── URLHelper.php
│       └── DateHelper.php
│
├── tests/                              // Tests
│   ├── UnitTests/
│   ├── IntegrationTests/
│   └── bootstrap.php
│
├── database/                           // SQL migrations
│   ├── migrations/
│   └── seeds/
│
└── config/                             // Configuración
    ├── plugin.config.php               // Config principal
    └── auth-strategies.config.php      // Config de auth
```

---

## 4. Componentes Principales

### 4.1 Plugin (Clase Principal)

**Responsabilidad**: Coordinadora central del plugin. Implementa patrón Singleton y orquesta la inicialización de todos los componentes.

**Dependencias**: Loader, Registry, Setup

**Métodos Clave**:
- `getInstance()` - Obtener instancia singleton
- `activate()` - Ejecutar en activación del plugin
- `deactivate()` - Ejecutar en desactivación
- `register()` - Registrar componentes
- `run()` - Iniciar el plugin

### 4.2 Router

**Responsabilidad**: Enrutador centralizado que mapea requests HTTP a controladores de endpoint. Maneja rewrite rules automáticas.

**Patrones de Ruta**:
- `/wp-json/ol-api/v1/{endpoint}` - Ruta primaria
- `/api/v1/{endpoint}` - Ruta alternativa (configurable)

**Métodos Clave**:
- `register()` - Registrar rutas dinámicamente
- `dispatch()` - Despachar request a controlador
- `flushRules()` - Flush de rewrite rules
- `generatePermalinks()` - Generar estructura de URLs

### 4.3 EndpointRegistry

**Responsabilidad**: Registro centralizado de todos los endpoints configurados. Cache en memoria y persistencia en base de datos.

**Métodos Clave**:
- `register(string $slug, Endpoint $config)` - Registrar endpoint
- `get(string $slug): ?Endpoint` - Obtener configuración
- `all(): array` - Obtener todos los endpoints
- `delete(string $slug)` - Eliminar endpoint
- `exists(string $slug): bool` - Verificar existencia

### 4.4 AuthManager

**Responsabilidad**: Gestor centralizado de autenticación que implementa múltiples estrategias. Abstracción sobre mecanismos concretos.

**Métodos Clave**:
- `authenticate(Request $request): ?User` - Autenticar request
- `getUser(): ?User` - Obtener usuario autenticado
- `registerStrategy(string $type, StrategyInterface $strategy)` - Registrar estrategia
- `isAuthenticated(): bool` - Verificar autenticación

### 4.5 PermissionManager

**Responsabilidad**: Evaluador granular de permisos. Valida qué usuario puede hacer qué acción en qué endpoint.

**Métodos Clave**:
- `can(User $user, string $action, Endpoint $endpoint, ?Entity $entity = null): bool` - Verificar permiso
- `getEndpointPermissions(Endpoint $endpoint): array` - Obtener permisos del endpoint
- `getRoleCapabilities(string $role): array` - Obtener capacidades del rol

### 4.6 FieldDiscoveryService

**Responsabilidad**: Detector inteligente de campos disponibles en el sistema. Escanea providers y cachea resultados.

**Métodos Clave**:
- `discoverFieldsForPostType(string $postType): array` - Descubrir campos
- `discoverFieldsForEntity(Entity $entity): array` - Campos de entidad específica
- `registerProvider(ProviderInterface $provider)` - Registrar proveedor de campos
- `getAvailableProviders(): array` - Obtener proveedores disponibles

### 4.7 FieldValueResolver

**Responsabilidad**: Resuelve valores de campos para una entidad. Maneja core fields, meta, ACF, etc.

**Métodos Clave**:
- `resolve(Entity $entity, Field $field): mixed` - Resolver valor de campo
- `resolveMultiple(Entity $entity, array $fields): array` - Resolver múltiples campos
- `getNestedValue(Entity $entity, string $path): mixed` - Resolver path anidado

### 4.8 OpenAPIGenerator

**Responsabilidad**: Genera dinamicamente especificación OpenAPI 3.0 a partir de endpoints configurados.

**Métodos Clave**:
- `generate(): array` - Generar spec completa
- `generateForEndpoint(Endpoint $endpoint): array` - Spec de endpoint
- `buildSchema(Endpoint $endpoint): array` - Builder de schema
- `toCacheKey(): string` - Clave de caché

### 4.9 ResponseFormatter

**Responsabilidad**: Formatea respuestas en formato estándar. Maneja paginación, campos, errores.

**Estructura de Respuesta**:
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20,
    "has_next": true
  },
  "links": {
    "self": "/wp-json/ol-api/v1/endpoint?page=1",
    "next": "/wp-json/ol-api/v1/endpoint?page=2"
  }
}
```

**Métodos Clave**:
- `success(mixed $data, array $meta = []): array` - Respuesta exitosa
- `paginated(array $data, array $pagination): array` - Respuesta paginada
- `error(string $message, int $code, array $details = []): array` - Respuesta error

### 4.10 MediaHandler

**Responsabilidad**: Gestión completa del flujo de subida de archivos, validación y asociación.

**Métodos Clave**:
- `upload(array $file, UploadContext $context): int` - Subir archivo
- `validate(array $file): ValidationResult` - Validar archivo
- `attachToPost(int $attachmentId, int $postId): bool` - Asociar a post
- `processImage(int $attachmentId, array $sizes): array` - Procesar imágenes

---

## 5. Flujo de Request

### 5.1 Secuencia Completa

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. HTTP Request Incoming                                        │
│    GET /wp-json/ol-api/v1/products?filter=active&page=2        │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. Router.dispatch()                                            │
│    - Parse URL                                                  │
│    - Extract endpoint slug and action                           │
│    - Parse query parameters                                     │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. RequestHandler.validate()                                    │
│    - Validar estructura del request                             │
│    - Check HTTP method allowed                                  │
│    - Validate required parameters                               │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. AuthManager.authenticate()                                   │
│    - Detect auth strategy (Header/Query/Cookie)                 │
│    - Execute strategy authenticate()                            │
│    - Return User object or null                                 │
│    ❌ NO AUTH → Check if endpoint allows no auth               │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. PermissionManager.can()                                      │
│    - Get endpoint config                                        │
│    - Get action (read/create/update/delete)                     │
│    - Evaluate user role against endpoint permissions            │
│    - Check entity-level permissions if needed                   │
│    ❌ NO PERMISSION → Return 403 Forbidden                      │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 6. EndpointController.handle()                                  │
│    - Get endpoint config                                        │
│    - Build query based on configured fields                     │
│    - Apply filters from request                                 │
│    - Handle pagination                                          │
│    - Execute WordPress query                                    │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 7. FieldValueResolver.resolve()                                 │
│    - For each result entity                                     │
│    - For each configured field                                  │
│    - Resolve actual value:                                      │
│      - Core field → get_post_meta() or property                │
│      - Meta field → get_post_meta()                             │
│      - ACF field → get_field()                                  │
│      - JetEngine field → get data from engine                   │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 8. ResponseFormatter.format()                                   │
│    - Structure response envelope                                │
│    - Add pagination metadata                                    │
│    - Add HATEOAS links                                          │
│    - Apply JSON serialization                                   │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 9. Logger.log()                                                 │
│    - Record request (optional based on settings)                │
│    - Log auth method, user, action, result                      │
└──────────────────────────┬──────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────────┐
│ 10. HTTP Response                                               │
│     HTTP 200 OK                                                 │
│     Content-Type: application/json                              │
│     {                                                           │
│       "success": true,                                          │
│       "data": [...],                                            │
│       "meta": {...}                                             │
│     }                                                           │
└─────────────────────────────────────────────────────────────────┘
```

### 5.2 Flujos de Error

| Escenario | Código | Acción |
|-----------|--------|--------|
| Endpoint no existe | 404 | Router no encuentra match |
| No autenticado | 401 | AuthManager retorna null |
| Permisos insuficientes | 403 | PermissionManager retorna false |
| Validación fallida | 400 | RequestValidator lanza excepción |
| Error interno | 500 | Catch-all exception handler |

---

## 6. Sistema de Autenticación

### 6.1 Arquitectura Multi-Estrategia

El sistema implementa patron Strategy compartiendo interfaz común `StrategyInterface`:

```php
interface StrategyInterface {
    public function supports(Request $request): bool;
    public function authenticate(Request $request): ?User;
    public function validateCredentials(array $credentials): bool;
    public function getType(): string;
}
```

### 6.2 Estrategias Implementables

#### 6.2.1 API Key Strategy

**Flujo**:
1. Cliente envía header: `X-API-Key: {key}`
2. AuthManager extrae key
3. Strategy consulta CredentialStore
4. Retorna User asociado o null

**Almacenamiento**:
- Tabla `ol_api_api_keys`
- Campo `key` hasheado con bcrypt
- Metadata: nombre, usuario, activo, último uso

#### 6.2.2 Bearer Token Strategy

**Flujo**:
1. Cliente envía: `Authorization: Bearer {token}`
2. Strategy valida formato JWT/Opaque
3. Consulta TokenManager
4. Retorna User si válido

**TTL**: Configurable (default 24h)  
**Refresh**: Sistema de refresh tokens

#### 6.2.3 JWT Strategy

### 1. Tipo de autenticación

Soportar:

* API Key
* Bearer Token
* JWT
* WordPress Application Passwords
* OAuth (arquitectura preparada, aunque no implementado inicialmente)
* Sin autenticación (opcional por endpoint)

Debe poder configurarse por endpoint.

---

### 2. Configuración de Endpoints

Desde el dashboard se podrá:

* Crear endpoints personalizados

* Seleccionar:

  * post types (incluyendo CPTs)
  * taxonomías
  * usuarios
  * medios

* Seleccionar campos disponibles:

  * Core fields
  * Custom fields
  * Campos de:

    * ACF
    * JetEngine
    * Meta fields estándar
    * Campos registrados vía register_meta

El sistema debe detectar automáticamente todos los campos disponibles.

---

### 3. Permisos por Roles

Permitir configurar por endpoint:

* Qué roles pueden:

  * leer
  * crear
  * actualizar
  * eliminar

Debe soportar:

* roles nativos de WordPress
* roles personalizados

---

### 4. Permisos para Subida de Imágenes

Permitir configurar:

* qué endpoints permiten subir imágenes
* qué roles pueden subir imágenes
* validación de tipo MIME
* tamaño máximo
* asociación automática al post o entidad

---

### 5. Documentación automática (Swagger / OpenAPI)

Debe generarse automáticamente:

* OpenAPI 3.0 spec
* Swagger UI integrado en el dashboard
* documentación pública opcional

Debe actualizarse automáticamente cuando:

* se agrega un endpoint
* se agregan campos
* se cambian permisos

---

### 6. Rewrite Rules Automático

El plugin debe registrar automáticamente rutas como:

/wp-json/custom-api/v1/{endpoint}

y opcionalmente:

/api/{endpoint}

Debe manejar flush rules correctamente.

---

### 7. Dashboard Administrativo

Debe incluir:

Secciones:

* Endpoints
* Autenticación
* Permisos
* Campos
* Media uploads
* Logs
* Swagger Docs
* Settings globales

Debe usar:

* WordPress Settings API
* Custom admin pages
* Arquitectura modular

---

### 8. Sistema de permisos interno

Debe existir una capa de autorización independiente de WordPress REST API.

Debe validar:

* autenticación
* rol
* permisos
* endpoint solicitado

---

### 9. Sistema de Logs

Debe registrar:

* requests
* errores
* accesos denegados

Opcional:

* tabla personalizada en DB

---

### 10. Compatibilidad con proveedores externos

Debe detectar automáticamente campos de:

* ACF
* JetEngine
* Meta fields estándar

Arquitectura extensible para nuevos proveedores.

---

## Requisitos de Arquitectura

Usar arquitectura modular basada en:

* separación por responsabilidades
* principios SOLID
* orientación a objetos
* desacoplamiento

Usar capas:

* Core
* Admin
* API
* Auth
* Permissions
* Fields
* Docs
* Infrastructure

---

## La documentación debe incluir obligatoriamente

Generar Markdown con estas secciones:

---

# 1. Visión General

Descripción completa del sistema

---

# 2. Arquitectura General

Diagramas explicados en texto

Capas:

* Admin Layer
* API Layer
* Auth Layer
* Permissions Layer
* Field Discovery Layer
* Docs Layer
* Infrastructure Layer

---

# 3. Estructura de Carpetas

Ejemplo completo:

plugin-name/
includes/
admin/
api/
auth/
permissions/
fields/
docs/
infrastructure/
models/
repositories/

Explicar cada carpeta.

---

# 4. Componentes principales

Explicar clases necesarias:

Ejemplo:

Plugin
Router
EndpointRegistry
AuthManager
PermissionManager
FieldDiscoveryService
DocsGenerator
SettingsManager
MediaHandler

Explicar responsabilidad de cada uno.

---

# 5. Flujo de Request

Paso a paso desde:

HTTP Request → Router → Auth → Permissions → Endpoint → Response

---

# 6. Sistema de Autenticación

Arquitectura para soportar múltiples estrategias.

---

# 7. Sistema de Permisos

Cómo se evalúan permisos.

---

# 8. Sistema de descubrimiento de campos

Cómo detectar:

* core
* meta
* ACF
* JetEngine

---

# 9. Sistema de generación de documentación

Cómo generar OpenAPI spec dinámicamente.

---

# 10. Sistema de Dashboard

Arquitectura del panel admin.

---

# 11. Sistema de almacenamiento

Qué se guarda en:

* options
* custom tables
* transients
* cache

---

# 12. Sistema de extensibilidad

Hooks, filters, interfaces.

---

# 13. Seguridad

Validación
Sanitización
Autorización

---

# 14. Performance

Cache
Lazy loading

---

# 15. Flujo de inicialización del plugin

Qué ocurre en:

plugins_loaded
init
rest_api_init

---

# 16. Roadmap de implementación

Orden recomendado de desarrollo.

---

# Formato requerido

Markdown estructurado profesional.

Usar:

* headings
* diagrams en texto
* tablas
* listas

No escribir código.

Solo arquitectura y documentación.

---

# Objetivo final

Documento técnico listo para implementar el plugin desde cero.
