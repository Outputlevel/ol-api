# OL-API: Arquitectura Técnica del Plugin WordPress

**Versión:** 1.0.0  
**Fecha:** Febrero 2026  
**Estado:** Especificación de Arquitectura

---

## 1. Visión General

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
- Límite inicial: 100 endpoints simultáneamente activos

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

## 3. Estructura de Carpetas Recomendada

```
ol-api/
├── ol-api.php                          // Archivo principal del plugin
├── README.md                           // Documentación del usuario
├── ARCHITECTURE.md                     // Especificación técnica
│
├── includes/
│   ├── Plugin.php                      // Clase principal del plugin
│   ├── Setup.php                       // Hooks de activación/desactivación
│   ├── Loader.php                      // Cargador de clases (autoload)
│   │
│   ├── Core/
│   │   ├── PluginInterface.php
│   │   ├── Registry.php
│   │   └── Config.php
│   │
│   ├── API/
│   │   ├── Router.php
│   │   ├── RequestHandler.php
│   │   ├── ResponseFormatter.php
│   │   ├── EndpointController.php
│   │   ├── Validators/
│   │   └── Exceptions/
│   │
│   ├── Auth/
│   │   ├── AuthManager.php
│   │   ├── Strategies/
│   │   ├── TokenManager.php
│   │   └── CredentialStore.php
│   │
│   ├── Permissions/
│   │   ├── PermissionManager.php
│   │   ├── RoleResolver.php
│   │   ├── CapabilityEvaluator.php
│   │   ├── EndpointPermission.php
│   │   └── MediaPermission.php
│   │
│   ├── Fields/
│   │   ├── FieldDiscoveryService.php
│   │   ├── FieldValueResolver.php
│   │   ├── Providers/
│   │   ├── FieldRegistry.php
│   │   └── FieldTypeMapper.php
│   │
│   ├── Media/
│   │   ├── MediaHandler.php
│   │   ├── MediaUploader.php
│   │   ├── MediaValidator.php
│   │   ├── ImageProcessor.php
│   │   └── MediaAttacher.php
│   │
│   ├── Docs/
│   │   ├── OpenAPIGenerator.php
│   │   ├── SchemaBuilder.php
│   │   ├── DocsCache.php
│   │   └── SwaggerUIManager.php
│   │
│   ├── Models/
│   │   ├── Endpoint.php
│   │   ├── EndpointField.php
│   │   ├── APIKey.php
│   │   ├── AuthConfig.php
│   │   ├── PermissionRule.php
│   │   └── APILog.php
│   │
│   ├── Repositories/
│   │   ├── EndpointRepository.php
│   │   ├── APIKeyRepository.php
│   │   ├── LogRepository.php
│   │   ├── SettingsRepository.php
│   │   └── BaseRepository.php
│   │
│   ├── Infrastructure/
│   │   ├── Database/
│   │   ├── Cache/
│   │   ├── Logger/
│   │   └── Security/
│   │
│   ├── Admin/
│   │   ├── Pages/
│   │   ├── Forms/
│   │   ├── Assets/
│   │   └── Menu.php
│   │
│   ├── Traits/
│   │   ├── SingletonTrait.php
│   │   ├── HookableTrait.php
│   │   ├── LoggableTrait.php
│   │   └── CacheableTrait.php
│   │
│   └── Helpers/
│
├── tests/
├── database/
└── config/
```

---

## 4. Componentes Principales

### 4.1 Plugin (Clase Principal)
**Responsabilidad**: Coordinadora central. Singleton que orquesta inicialización de componentes.

**Métodos Clave**:
- `getInstance()` - Obtener instancia singleton
- `activate()` - Ejecutar en activación
- `deactivate()` - Ejecutar en desactivación
- `register()` - Registrar componentes
- `run()` - Iniciar plugin

### 4.2 Router
**Responsabilidad**: Enrutador centralizado que mapea requests HTTP a controladores.

**Patrones de Ruta**:
- `/wp-json/ol-api/v1/{endpoint}` - Ruta primaria
- `/api/v1/{endpoint}` - Ruta alternativa (configurable)

### 4.3 EndpointRegistry
**Responsabilidad**: Registro centralizado de endpoints. Cache en memoria y persistencia en BD.

**Métodos Clave**:
- `register(slug, config)` - Registrar endpoint
- `get(slug)` - Obtener configuración
- `all()` - Obtener todos los endpoints
- `delete(slug)` - Eliminar endpoint

### 4.4 AuthManager
**Responsabilidad**: Gestor de autenticación multi-estrategia.

**Métodos Clave**:
- `authenticate(request)` - Autenticar request
- `getUser()` - Obtener usuario autenticado
- `registerStrategy(type, strategy)` - Registrar estrategia
- `isAuthenticated()` - Verificar autenticación

### 4.5 PermissionManager
**Responsabilidad**: Evaluador granular de permisos por rol y endpoint.

**Métodos Clave**:
- `can(user, action, endpoint, entity)` - Verificar permiso
- `getEndpointPermissions(endpoint)` - Obtener permisos
- `getRoleCapabilities(role)` - Capacidades del rol

### 4.6 FieldDiscoveryService
**Responsabilidad**: Detector inteligente de campos disponibles con caché.

**Métodos Clave**:
- `discoverFieldsForPostType(postType)` - Descubrir campos
- `registerProvider(provider)` - Registrar proveedor
- `getAvailableProviders()` - Obtener proveedores

### 4.7 FieldValueResolver
**Responsabilidad**: Resuelve valores de campos para entidades.

**Métodos Clave**:
- `resolve(entity, field)` - Resolver valor
- `resolveMultiple(entity, fields)` - Resolver múltiples
- `getNestedValue(entity, path)` - Valor anidado

### 4.8 OpenAPIGenerator
**Responsabilidad**: Genera especificación OpenAPI 3.0 dinámicamente.

**Métodos Clave**:
- `generate()` - Generar spec completa
- `generateForEndpoint(endpoint)` - Spec del endpoint
- `buildSchema(endpoint)` - Builder de schema

### 4.9 ResponseFormatter
**Responsabilidad**: Formatea respuestas en formato estándar con paginación.

**Estructura**:
```json
{
  "success": true,
  "data": [...],
  "meta": {"total": 100, "page": 1},
  "links": {...}
}
```

### 4.10 MediaHandler
**Responsabilidad**: Gestión completa de subidas de archivos y validación.

**Métodos Clave**:
- `upload(file, context)` - Subir archivo
- `validate(file)` - Validar archivo
- `attachToPost(attachmentId, postId)` - Asociar a post
- `processImage(attachmentId, sizes)` - Procesar imágenes

---

## 5. Flujo de Request Completo

```
HTTP Request
    ↓
Router.dispatch()
    ├─ Parse URL
    ├─ Extract endpoint slug
    └─ Parse query parameters
    ↓
RequestHandler.validate()
    ├─ Check HTTP method
    ├─ Validate parameters
    └─ Parse JSON body
    ↓
AuthManager.authenticate()
    ├─ Detect auth strategy
    ├─ Execute strategy
    └─ Return User or null
    ↓ [ERROR: 401 Unauthorized]
PermissionManager.can()
    ├─ Get endpoint config
    ├─ Check user role
    └─ Evaluate permissions
    ↓ [ERROR: 403 Forbidden]
EndpointController.handle()
    ├─ Build query
    ├─ Apply filters
    ├─ Handle pagination
    └─ Execute WordPress query
    ↓
FieldValueResolver.resolve()
    ├─ For each entity
    ├─ For each field
    └─ Resolve value (core/meta/ACF/JetEngine)
    ↓
ResponseFormatter.format()
    ├─ Structure envelope
    ├─ Add metadata
    └─ Add HATEOAS links
    ↓
Logger.log()
    └─ Record request details
    ↓
HTTP Response 200 OK
```

### Códigos de Error

| Escenario | Código | Causa |
|-----------|--------|-------|
| Endpoint no existe | 404 | Router no encuentra match |
| No autenticado | 401 | AuthManager retorna null |
| Permisos insuficientes | 403 | PermissionManager retorna false |
| Validación fallida | 400 | RequestValidator lanza excepción |
| Error interno | 500 | Excepción no capturada |
| Too many requests | 429 | Rate limiting activado |

---

## 6. Sistema de Autenticación Multi-Estrategia

### 6.1 Interfaz Base

```php
interface StrategyInterface {
    public function supports(\WP_REST_Request $request): bool;
    public function authenticate(\WP_REST_Request $request): ?\WP_User;
    public function validateCredentials(array $credentials): bool;
    public function getType(): string;
}
```

### 6.2 Estrategias Soportadas

#### API Key Strategy
- **Flujo**: Header `X-API-Key: {key}` → CredentialStore → User
- **Almacenamiento**: Tabla `ol_api_api_keys` con bcrypt hash
- **Metadata**: nombre, usuario, activo, último uso

#### Bearer Token Strategy
- **Flujo**: Header `Authorization: Bearer {token}` → TokenManager → User
- **TTL**: Configurable (default 24h)
- **Refresh**: Sistema de refresh tokens

#### JWT Strategy
- **Flujo**: Header `Authorization: Bearer {jwt}` → Decodificar → Validar → User
- **Secret Key**: Encriptado en options table
- **Claims**: sub (user_id), iat, exp, roles

#### Application Passwords Strategy
- **Flujo**: Header `Authorization: Basic {base64}` → wp_authenticate() → User
- **Ventaja**: Integrado con WordPress nativamente

#### OAuth Strategy (Preparado para Fase 2)
- **Estructura**: Clase placeholder con métodos stub
- **Futura Implementación**: OAuth 2.0 con authorization code flow

#### No Auth Strategy
- **Uso**: Endpoints públicos sin autenticación
- **Validación**: PermissionManager verifica si endpoint permite público

### 6.3 AuthManager Orquestación

```
AuthManager.authenticate(Request):
  PARA CADA estrategia registrada:
    SI strategy.supports(request):
      user = strategy.authenticate(request)
      SI user NO null:
        cache user en request context
        RETORNAR user
  
  // No matches
  RETORNAR null
```

---

## 7. Sistema de Permisos Granulares

### 7.1 Niveles de Control

**Nivel 1 - Endpoint Level**: 
- Acceso general al endpoint por rol
- Acciones: read, create, update, delete

**Nivel 2 - Field Level** (Fase 2):
- Qué campos ve cada rol

**Nivel 3 - Entity Level** (Fase 2):
- Entidades específicas por usuario (ej: solo posts del autor)

### 7.2 Estructura de Datos

```php
Permission Record {
  endpoint_slug: "products",
  action: "read|create|update|delete",
  role: "customer|vendor|admin",
  allowed: true,
  created_at: timestamp,
  updated_at: timestamp
}
```

### 7.3 Evaluación de Permisos

```
PermissionManager.can(user, action, endpoint, entity?):
  
  // 1. Get endpoint config
  config = EndpointRegistry.get(endpoint.slug)
  SI config NULL:
    RETORNAR false
  
  // 2. Check public endpoint
  SI action == "read" Y config.is_public:
    RETORNAR true
  
  // 3. Require authentication
  SI user NULL:
    RETORNAR false
  
  // 4. Get user roles
  roles = user.get_roles()
  
  // 5. Check permission rules
  PARA CADA rule en config.permission_rules:
    SI rule.action == action Y rule.role EN roles:
      SI rule.allowed:
        SI entity NO null:
          RETORNAR check_entity_permission(user, entity)
        RETORNAR true
  
  // Default: deny
  RETORNAR false
```

### 7.4 Media Permissions

**Configuración**:
```php
Media Config {
  endpoint: "products",
  allow_upload: true,
  max_size: 5242880,  // 5MB
  mime_types: ["image/jpeg", "image/png"],
  role_restrictions: {
    "customer": false,
    "vendor": true
  },
  attach_to_post: true
}
```

---

## 8. Sistema de Descubrimiento de Campos

### 8.1 Flujo de Descubrimiento

```
FieldDiscoveryService.discoverFieldsForPostType("product"):
  
  fields = []
  
  // 1. Core Fields
  core_provider = registry.get(CoreFieldProvider)
  fields += core_provider.getFields("post", "product")
  // ID, title, content, excerpt, featured_image, etc.
  
  // 2. Meta Fields
  meta_provider = registry.get(MetaFieldProvider)
  fields += meta_provider.getFields("post", "product")
  
  // 3. ACF Fields (si activo)
  SI function_exists("acf_get_field_groups"):
    acf_provider = registry.get(ACFProvider)
    fields += acf_provider.getFields("post", "product")
  
  // 4. JetEngine Fields (si activo)
  SI class_exists("Jet_Engine"):
    jetengine_provider = registry.get(JetEngineProvider)
    fields += jetengine_provider.getFields("post", "product")
  
  // 5. Third-party hooks
  apply_filters("ol_api_discover_fields", fields, "post", "product")
  
  // 6. Cache (24h)
  cache.set("ol_api_fields_post_product", fields, 86400)
  
  RETORNAR fields
```

### 8.2 Proveedores de Campos

#### Core Field Provider
**Campos**: id, title, content, excerpt, featured_image, author_id, date_created, date_modified, status, slug

**Método**: Array hardcoded (más eficiente que introspección)

#### Meta Field Provider
**Flujo**:
1. Consulta `{$wpdb}->postmeta` para post type
2. Detecta keys únicas (últimas 1000 registros)
3. Consulta alternativa si `register_meta()` usado
4. **Caché**: 24h transient

#### ACF Provider
**Requiere**: Plugin ACF activo

**Flujo**:
1. `acf_get_field_groups()` con match post type
2. `acf_get_fields()` para cada group
3. Mapea tipos ACF → OpenAPI types

**Mapeo de Tipos**:
| ACF Type | OpenAPI Type |
|----------|--------------|
| text | string |
| number | integer/number |
| textarea | string |
| date_picker | string (ISO 8601) |
| image | object (url, id, alt) |
| relationship | array |
| repeater | array |

#### JetEngine Provider
**Requiere**: Plugin JetEngine activo

**Flujo**:
1. `Jet_Engine_CPT_Module::instance()` obtiene CPTs
2. Busca meta fields en `jet_engine_cpt_{name}_fields`
3. Extrae fields del UI builder
4. Cachea resultado

### 8.3 FieldValueResolver

```
FieldValueResolver.resolve(entity, field):
  
  SI field.source == "core":
    RETORNAR resolve_core_field(entity, field.name)
  
  SI field.source == "meta":
    RETORNAR get_post_meta(entity.ID, field.key, true)
  
  SI field.source == "acf":
    RETORNAR get_field(field.name, entity.ID)
  
  SI field.source == "jetengine":
    RETORNAR jet_engine_get_field(field.name, entity.ID)
  
  // Custom via filter
  RETORNAR apply_filters("ol_api_resolve_field_value", 
                         null, entity, field)
```

### 8.4 Mapeo de Tipos

| Field Type | PHP Type | OpenAPI Type | Validación |
|-----------|----------|--------------|-----------|
| Text | string | string | maxLength |
| Number | int/float | integer/number | min, max |
| Date | DateTime | string (date-time) | ISO 8601 |
| Boolean | bool | boolean | - |
| Array | array | array | items |
| Object | object | object | properties |
| Image | int/array | object | {url,alt,id} |
| Enum | string | string | enum |

---

## 9. Sistema de Documentación Automática (OpenAPI 3.0)

### 9.1 Generación Dinámica

**Triggers**:
- Endpoint creado/modificado/eliminado
- Campos agregados/removidos
- Permisos cambiados
- Solicitud manual vía admin UI

### 9.2 Especificación Básica

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "OL-API - {SITE_NAME}",
    "version": "1.0.0"
  },
  "servers": [{
    "url": "https://example.com/wp-json/ol-api/v1"
  }],
  "components": {
    "securitySchemes": {
      "apiKey": {"type": "apiKey", "in": "header", "name": "X-API-Key"},
      "bearerAuth": {"type": "http", "scheme": "bearer"},
      "basicAuth": {"type": "http", "scheme": "basic"}
    },
    "schemas": {...}
  },
  "paths": {...}
}
```

### 9.3 Schema Builder

```
SchemaBuilder.buildSchema(endpoint):
  schema = {
    type: "object",
    properties: {},
    required: []
  }
  
  PARA CADA field en endpoint.fields:
    schema.properties[field.name] = {
      type: map_type(field.type),
      description: field.label,
      ...constraints
    }
    
    SI field.required:
      schema.required.push(field.name)
  
  RETORNAR schema
```

### 9.4 Swagger UI Integration

**Ubicación**: `/wp-admin/admin.php?page=ol-api-docs`

**Características**:
- Swagger UI v4 integrado
- Spec cargada dinámicamente
- Try-it-out con autenticación
- Responde en tiempo real a cambios

### 9.5 Documentación Pública

**Configuración**: En settings global  
**Ruta**: `/api-docs` (configurable)  
**Seguridad**: 
- Toggle público/privado
- Oculta endpoints sensibles
- No expone credenciales

---

## 10. Sistema de Dashboard Administrativo

### 10.1 Estructura de Páginas

```
WordPress Admin Menu
└── OL-API
    ├── Endpoints
    ├── Credentials
    ├── Permissions
    ├── Documentation
    ├── Logs
    └── Settings
```

### 10.2 Página de Endpoints

**CRUD Completo**:
- Listado tabular
- Crear, editar, duplicar, eliminar
- Filtros: estado, tipo de dato

**Formulario Multi-Tab**:
1. **Información Básica**: slug, nombre, descripción, tipo dato, activo
2. **Campos**: tabla con selección de campos disponibles
3. **Configuración**: paginación, ordenamiento, filtrado
4. **Autenticación**: selector de estrategia
5. **Publicación**: visibilidad, rate limiting, CORS

### 10.3 Página de Credenciales

**Funcionalidad**: Crear y revocar API Keys y Tokens

**CRUD**:
- Generar nueva clave
- Ver última fecha de uso
- Revocar
- Configurar expiración

### 10.4 Página de Permisos

**Tabla Principal**:
| Endpoint | Rol | Read | Create | Update | Delete |
|----------|-----|------|--------|--------|--------|
| products | customer | ✓ | ✗ | ✗ | ✗ |

**Funcionalidad**: 
- Agregar permiso (selector endpoint + rol + checkboxes)
- Modificar permisos
- Eliminar reglas

### 10.5 Página de Documentación

**Contenido**:
1. Swagger UI (centro) - Documentación interactiva
2. Sidebar - Endpoints listados, búsqueda
3. Toolbar - Descargar spec JSON, copiar link público

### 10.6 Página de Logs

**Tabla de Logs**:
| Timestamp | Usuario | Endpoint | Acción | Status | IP |
|-----------|---------|----------|--------|--------|----| 
| 2026-02-24 | vendor@email.com | products | Read | 200 | 192.168.1.1 |

**Filtros**: Usuario, endpoint, status, date range
**Acciones**: Ver detalles, descargar CSV, limpiar logs

### 10.7 Página de Settings

**Configuración Global**:

**General**:
- API Base URL (readonly)
- Versión API (readonly)
- Documentation URL + toggle público/privado

**Rate Limiting**:
- Global default (enabled + requests/minuto)
- Por IP, por usuario
- Ventana de tiempo

**Seguridad**:
- CORS domains (textarea)
- Require HTTPS (toggle)
- Max request size (bytes)
- Whitelist IPs

**Logging**:
- Log requests (toggle)
- Log only errors (toggle)
- Retention days
- Log to database o file

**Cache**:
- Cache enabled (toggle)
- Duration (segundos)
- Clear All Cache (botón)

---

## 11. Sistema de Almacenamiento

### 11.1 WordPress Options Table

| option_name | Contenido | Tipo |
|-------------|-----------|------|
| `ol_api_settings` | Config global | serialized array |
| `ol_api_endpoints_registry` | Índice de endpoints | serialized array |
| `ol_api_auth_config` | Config auth | serialized array |
| `ol_api_rate_limiting` | Config rate limit | serialized array |
| `ol_api_jwt_secret` | Secret key JWT (encrypted) | encrypted string |
| `ol_api_openapi_spec` | Cache OpenAPI | serialized JSON |

**Autoload**: Sí (frequent access)

### 11.2 Tablas Personalizadas

#### ol_api_endpoints
```sql
CREATE TABLE `ol_api_endpoints` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(255) UNIQUE NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` LONGTEXT,
  `data_source` ENUM('post_type', 'taxonomy', 'user', 'media'),
  `data_source_name` VARCHAR(255) NOT NULL,
  `is_active` BOOLEAN DEFAULT 1,
  `auth_required` BOOLEAN DEFAULT 1,
  `auth_strategies` VARCHAR(255),
  `allow_get` BOOLEAN DEFAULT 1,
  `allow_post` BOOLEAN DEFAULT 0,
  `allow_put` BOOLEAN DEFAULT 0,
  `allow_delete` BOOLEAN DEFAULT 0,
  `rate_limit_enabled` BOOLEAN DEFAULT 0,
  `rate_limit_requests` INT DEFAULT 100,
  `rate_limit_window` INT DEFAULT 3600,
  `cors_origins` LONGTEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_active` (`is_active`)
)
```

#### ol_api_endpoint_fields
```sql
CREATE TABLE `ol_api_endpoint_fields` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `endpoint_id` BIGINT UNSIGNED NOT NULL,
  `field_name` VARCHAR(255) NOT NULL,
  `field_label` VARCHAR(255) NOT NULL,
  `field_type` VARCHAR(50) NOT NULL,
  `field_source` VARCHAR(50) NOT NULL,
  `field_key` VARCHAR(255),
  `is_required` BOOLEAN DEFAULT 0,
  `is_filterable` BOOLEAN DEFAULT 0,
  `default_value` LONGTEXT,
  `validation_rules` LONGTEXT,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`endpoint_id`) REFERENCES `ol_api_endpoints` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_endpoint_field` (`endpoint_id`, `field_name`),
  INDEX `idx_endpoint` (`endpoint_id`)
)
```

#### ol_api_api_keys
```sql
CREATE TABLE `ol_api_api_keys` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key_hash` VARCHAR(255) UNIQUE NOT NULL,
  `key_preview` VARCHAR(20) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `allowed_endpoints` LONGTEXT,
  `is_active` BOOLEAN DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_used_at` TIMESTAMP,
  `expires_at` TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `wp_users` (`ID`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_active` (`is_active`)
)
```

#### ol_api_tokens
```sql
CREATE TABLE `ol_api_tokens` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `token_hash` VARCHAR(255) UNIQUE NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `type` ENUM('access', 'refresh') NOT NULL,
  `jti` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `revoked_at` TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `wp_users` (`ID`) ON DELETE CASCADE,
  INDEX `idx_user_type` (`user_id`, `type`),
  INDEX `idx_expires` (`expires_at`)
)
```

#### ol_api_permissions
```sql
CREATE TABLE `ol_api_permissions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `endpoint_id` BIGINT UNSIGNED NOT NULL,
  `role` VARCHAR(255) NOT NULL,
  `action` ENUM('read', 'create', 'update', 'delete') NOT NULL,
  `is_allowed` BOOLEAN DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`endpoint_id`) REFERENCES `ol_api_endpoints` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_permission` (`endpoint_id`, `role`, `action`),
  INDEX `idx_endpoint` (`endpoint_id`),
  INDEX `idx_role` (`role`)
)
```

#### ol_api_logs
```sql
CREATE TABLE `ol_api_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `user_id` BIGINT UNSIGNED,
  `user_ip` VARCHAR(45),
  `endpoint_slug` VARCHAR(255),
  `method` ENUM('GET', 'POST', 'PUT', 'DELETE', 'PATCH'),
  `action` VARCHAR(50),
  `status_code` SMALLINT,
  `response_time_ms` INT,
  `request_size` INT,
  `response_size` INT,
  `user_agent` TEXT,
  `error_message` LONGTEXT,
  `request_data` LONGTEXT,
  INDEX `idx_timestamp` (`timestamp`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_endpoint` (`endpoint_slug`),
  INDEX `idx_status` (`status_code`)
)
```

### 11.3 Transients (Caché Temporal)

| Transient Key | Contenido | TTL |
|---------------|-----------|-----|
| `ol_api_fields_post_{posttype}` | Campos descubiertos | 24h |
| `ol_api_openapi_spec` | Spec OpenAPI compilada | 24h |
| `ol_api_user_scope_{user_id}` | Cache de permisos | 1h |
| `ol_api_rate_limit_{ip_or_user}` | Request count | 1h |

### 11.4 Encriptación

**Datos Sensibles Encriptados**:
- JWT Secret key
- API Keys (bcrypt hash en DB)
- OAuth tokens

**Método**: AES-256-GCM vía `openssl_encrypt()`  
**Key**: Basada en `AUTH_KEY` + `SECURE_AUTH_KEY` de wp-config.php

---

## 12. Sistema de Extensibilidad

### 12.1 Hooks (Actions)

```php
// Lifecycle
do_action('ol_api_loaded');
do_action('ol_api_init');
do_action('ol_api_rest_api_init');

// Endpoints
do_action('ol_api_endpoint_before_create', $endpoint_data);
do_action('ol_api_endpoint_created', $endpoint);
do_action('ol_api_endpoint_deleted', $slug);

// Field Discovery
do_action('ol_api_fields_discovered', $fields, $source_type, $source_name);
do_action('ol_api_register_field_providers', $registry);

// Auth
do_action('ol_api_user_authenticated', $user, $strategy);
do_action('ol_api_authentication_failed', $request, $reason);

// Permissions
do_action('ol_api_permission_checked', $user, $action, $endpoint, $allowed);

// Requests
do_action('ol_api_request_before', $request);
do_action('ol_api_request_after', $request, $response);
do_action('ol_api_request_error', $request, $exception);
```

### 12.2 Filters (Filtros)

```php
// Fields
apply_filters('ol_api_discover_fields', $fields, $type, $name);
apply_filters('ol_api_resolve_field_value', $value, $entity, $field);

// Auth
apply_filters('ol_api_authenticated_user', $user, $strategy);
apply_filters('ol_api_auth_strategies', $strategies);

// Permissions
apply_filters('ol_api_check_permission', $allowed, $user, $action, $endpoint);
apply_filters('ol_api_user_roles', $roles, $user);

// Response
apply_filters('ol_api_response_data', $data, $endpoint, $entities);
apply_filters('ol_api_response_envelope', $response, $request);
apply_filters('ol_api_response_headers', $headers, $response);

// OpenAPI
apply_filters('ol_api_openapi_spec', $spec);
apply_filters('ol_api_endpoint_schema', $schema, $endpoint);

// Validation
apply_filters('ol_api_validation_rules', $rules, $endpoint, $action);
```

### 12.3 Interfaces Públicas

```php
interface FieldProviderInterface {
    public function getName(): string;
    public function supports(string $objectType, string $entityName): bool;
    public function getFields(string $objectType, string $entityName): array;
    public function getValue($entity, string $fieldName): mixed;
}

interface AuthStrategyInterface {
    public function getType(): string;
    public function supports(\WP_REST_Request $request): bool;
    public function authenticate(\WP_REST_Request $request): ?\WP_User;
    public function validateCredentials(array $credentials): bool;
}

interface FieldValidatorInterface {
    public function validate(mixed $value, Field $field): ValidationResult;
}
```

---

## 13. Seguridad

### 13.1 Validación en Niveles

1. **Estructura**: ¿Request tiene estructura esperada?
2. **Tipo**: ¿Campos tienen tipos correctos?
3. **Rango**: ¿Valores en rangos permitidos?
4. **Lógica**: ¿Valores tienen sentido contextual?

### 13.2 Sanitización por Tipo

```php
Sanitize(value, field_type):
  IF field_type == "string": RETORNAR sanitize_text_field(value)
  IF field_type == "html": RETORNAR wp_kses_post(value)
  IF field_type == "integer": RETORNAR intval(value)
  IF field_type == "boolean": RETORNAR (bool) value
  IF field_type == "email": RETORNAR sanitize_email(value)
  IF field_type == "url": RETORNAR esc_url_raw(value)
  IF field_type == "array": RETORNAR array_map(..., value)
```

### 13.3 Protección contra Ataques

| Ataque | Protección | Implementación |
|--------|-----------|-----------------|
| SQL Injection | Prepared statements | $wpdb->prepare() |
| XSS | Escaping + Sanitization | wp_kses_post(), esc_html() |
| CSRF | Nonce validation | wp_verify_nonce() |
| Brute Force | Rate limiting | Custom middleware |
| Path Traversal | Path normalization | realpath() validation |
| JWT Compromise | Secret key | Hash + rotate periódicamente |
| API Key Leak | Bcrypt hash | Almacenar solo hash en DB |

---

## 14. Performance

### 14.1 Estrategia de Caché Multicapa

```
Request
    ↓
Object Cache (Redis/Memcached/DB)
    ↓ [miss]
Transient Cache (options table)
    ↓ [miss]
WordPress Database
```

**Qué Cachear**:
- Descubrimiento de campos (24h)
- Spec OpenAPI (24h, purga on change)
- Permisos de usuario (1h)
- Rate limit counters (1h)
- Endpoints populares (2h, LRU)

### 14.2 Optimización de Queries

**Lazy Loading**: No cargar campos relacionados sin solicitud explícita

**Paginación Obligatoria**:
- Default: 20 items/página
- Máximo: 100 items/página
- Evitar offset en datasets grandes (cursor paging)

**Índices de Base de Datos**:
```sql
CREATE INDEX idx_slug ON ol_api_endpoints(slug);
CREATE INDEX idx_active ON ol_api_endpoints(is_active);
CREATE INDEX idx_endpoint ON ol_api_endpoint_fields(endpoint_id);
CREATE INDEX idx_timestamp ON ol_api_logs(timestamp);
```

### 14.3 Compresión de Respuesta

- **Content-Encoding**: gzip/brotli
- **JSON Compacto**: Sin espacios innecesarios
- **Sparse Fieldsets**: `?fields=id,title` - solo campos solicitados

### 14.4 Rate Limiting

**Config Global**:
- Default: 100 requests/hour por IP
- Autenticado: 1000 requests/hour

**Headers**:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1645098000
```

---

## 15. Flujo de Inicialización del Plugin

### 15.1 Hooks WordPress

```
plugins_loaded (10)
    ↓
[Load classes, Setup registry, Config]
    ↓
init (10)
    ↓
[Database migrations, Load endpoints, Register strategies]
    ↓
rest_api_init (10)
    ↓
[Register routes, Register auth, Register field providers]
    ↓
admin_init
    ↓
[Register admin pages]
```

### 15.2 Activación

```
Activation Hook
    ↓
Setup::activate()
    ├─ DatabaseManager::create_tables()
    ├─ DatabaseManager::seed_default_data()
    ├─ flush_rewrite_rules()
    ├─ set_transient('ol_api_installed')
    └─ do_action('ol_api_activated')
```

### 15.3 Desactivación

```
Deactivation Hook
    ↓
Setup::deactivate()
    ├─ flush_rewrite_rules()
    ├─ delete_transients()
    ├─ Cache::clear_all()
    └─ do_action('ol_api_deactivated')

Nota: NO elimina tablas (datos preservados)
```

### 15.4 Desinstalación

```
Uninstall Hook
    ↓
Setup::uninstall()
    ├─ DatabaseManager::drop_tables()
    ├─ delete_option('ol_api_*')
    └─ do_action('ol_api_uninstalled')
```

---

## 16. Roadmap de Implementación por Fases

### Fase 1: MVP (Semanas 1-4)

**Sprint 1.1: Core Infrastructure** (Semana 1)
- Plugin class y Loader
- Database schema y migrations
- Settings API y options management
- Tests unitarios básicos

**Sprint 1.2: Admin Dashboard** (Semana 2)
- Endpoint CRUD UI
- Fields selector / discovery
- Basic Settings page
- Admin CSS/JS assets

**Sprint 1.3: API & Auth** (Semana 3)
- Router y RequestHandler
- API Key strategy
- Bearer Token strategy
- Basic endpoint controller

**Sprint 1.4: Documentación & Polish** (Semana 4)
- OpenAPI generator (básico)
- Swagger UI integration
- Logging system
- Error handling

**Deliverable**: API funcional + Dashboard para crear endpoints, seleccionar campos, autenticar, documentación automática.

### Fase 2: Advanced Auth & Permisos (Semanas 5-6)
- JWT strategy con secret management
- Application Passwords strategy
- Permissions UI granular
- Rate limiting
- Media uploads

**Deliverable**: Multi-auth completo, permisos granulares, media handling.

### Fase 3: Advanced Features (Semanas 7-8)
- Field-level permissions
- Entity-level permissions
- ACF/JetEngine integration
- Relationship resolving
- Caching optimization

**Deliverable**: Funcionalidad empresarial completa.

### Fase 4+: Enhancements (Futuro)
- GraphQL support
- Webhooks
- OAuth2 implementation
- Analytics dashboard
- Performance monitoring

---

## 17. Estándares de Código

### 17.1 Testing Strategy

- **Unit Tests**: 80% coverage mínimo
- **Integration Tests**: Request → Response completo
- **E2E Tests**: Admin UI workflows + API calls reales

### 17.2 Code Quality

- **Estándar**: WPCS (WordPress Coding Standards)
- **Linter**: PHP_CodeSniffer
- **Static Analysis**: PHPStan (level 7)
- **Formato**: PSR-12

### 17.3 Documentación

- **PHPDoc** en todas las clases/métodos
- **README** con ejemplos de uso
- **API Documentation** Markdown
- **Architecture Diagrams** este documento

### 17.4 Versionado

- **Semántica**: MAJOR.MINOR.PATCH
- **Deprecations**: Notificar 2 versiones antes de eliminar
- **Migrations**: Always safe, never breaking

### 17.5 Métricas de Performance

| Métrica | Target | Método |
|---------|--------|--------|
| Response Time | <100ms | Request logging |
| Throughput | >1000 req/sec | Load testing |
| Memory | <50MB | Memory profiling |
| Cache Hit Rate | >85% | Cache stats |
| DB Queries | <5 por request | Query logging |

---

## 18. Conclusión

**OL-API** proporciona arquitectura robusta, escalable y extensible para convertir WordPress en una API empresarial profesional mediante:

✅ **Funcionalidad Sin-Código**: Dashboard completo para crear endpoints  
✅ **Seguridad Granular**: Multi-auth + permisos por rol/endpoint  
✅ **Extensibilidad**: Hooks, filters, interfaces públicas  
✅ **Escalabilidad**: Cache, lazy loading, rate limiting  
✅ **Documentación Automática**: OpenAPI 3.0 dinámico  
✅ **Auditabilidad**: Logs completos de acceso  

Esta especificación proporciona suficiente detalle técnico para que un equipo senior implemente el plugin sin ambigüedad, equilibrando profundidad técnica con claridad de intención.

---

**Documento Finalizado**  
**Versión**: 1.0.0  
**Última Actualización**: Febrero 2026  
**Estado**: Listo para Implementación
