# OL-API - WordPress REST API Builder

**OL-API** es un plugin empresarial de WordPress que convierte tu sitio en una API REST completamente configurable, sin escribir código.

## 🎯 ¿Qué es OL-API?

Un plugin que te permite:
- ✅ **Crear endpoints personalizados** mediante dashboard intuitivo
- ✅ **Exponer cualquier dato de WordPress** (posts, CPTs, taxonomías, usuarios, medios)
- ✅ **Autenticar via múltiples métodos** (API Key, JWT, Bearer Token, App Passwords)
- ✅ **Controlar acceso granularmente** (permisos por rol y endpoint)
- ✅ **Generar documentación automática** (OpenAPI 3.0 / Swagger UI)
- ✅ **Integrar con ACF y JetEngine** automáticamente
- ✅ **Auditar cada request** con logs detallados

## 📋 Requisitos

- WordPress 5.9+
- PHP 8.0+
- MySQL 5.7+ (o MariaDB equivalente)

## 🚀 Instalación Rápida

1. **Descarga el plugin** a `/wp-content/plugins/ol-api/`
2. **Actívalo** en WordPress Admin
3. **Accede** a OL-API en el menú de admin lateral
4. **Crea tu primer endpoint** en 5 minutos

## 📖 Documentación

- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Especificación técnica completa (para desarrolladores)
- **Dashboard UI** - Documentación integrada en cada página del admin
- **Swagger UI** - Docs interactivas en `/wp-admin/admin.php?page=ol-api-docs`

## 🔑 Características Principales

### 1. Endpoints Sin-Código
Crea endpoints personalizados seleccionando:
- Tipo de dato (posts, taxonomías, usuarios, media)
- Campos a exponer (core, custom, ACF, JetEngine)
- Configuración de paginación y filtrado

### 2. Multi-Autenticación
Soporta 6 métodos de autenticación simultáneamente:
- **API Key**: `X-API-Key: {key}`
- **Bearer Token**: `Authorization: Bearer {token}`
- **JWT**: Tokens con expiración y refresh
- **App Passwords**: WordPress Application Passwords
- **OAuth**: Preparado para Fase 2
- **Público**: Sin autenticación para endpoints públicos

### 3. Permisos Granulares
Control completo por:
- Endpoint
- Rol (nativo o personalizado)
- Acción (read, create, update, delete)
- Subidas de media (MIME, tamaño, permisos)

### 4. Documentación Automática
- **OpenAPI 3.0** automático
- **Swagger UI** integrado
- **API Pública opcional**
- Se actualiza en tiempo real

### 5. Sistema de Logs
Auditoría completa:
- Timestamp de cada request
- Usuario, IP, endpoint
- Método HTTP, status code
- Tiempo de respuesta
- Errores y detalles

### 6. Cache Inteligente
Optimización automática:
- Descubrimiento de campos (24h)
- Spec OpenAPI (24h)
- Permisos de usuario (1h)
- Rate limiting (1h)

## 🛣️ URLs Disponibles

```
GET /wp-json/ol-api/v1/{endpoint}           # Listar
POST /wp-json/ol-api/v1/{endpoint}          # Crear
PUT /wp-json/ol-api/v1/{endpoint}/{id}      # Actualizar
DELETE /wp-json/ol-api/v1/{endpoint}/{id}   # Eliminar
GET /wp-json/ol-api/v1/openapi.json         # Spec OpenAPI
```

### Rutas Alternativas (Configurables)
```
GET /api/v1/{endpoint}                      # Ruta alternativa
```

## 🔒 Seguridad

- ✅ Validación en múltiples niveles
- ✅ Sanitización automática de inputs
- ✅ Protección contra SQL Injection
- ✅ Protección contra XSS
- ✅ CSRF protection nativa
- ✅ Rate limiting configurable
- ✅ Encriptación de credenciales sensibles
- ✅ HTTPS recomendado (configurable como obligatorio)

## ⚡ Performance

- Caché de descubrimiento de campos
- Caché OpenAPI dinámico
- Paginación obligatoria
- Índices DB optimizados
- Compresión de respuestas (gzip/brotli)
- Sparse fieldsets soportados

## 🧩 Extensibilidad

Amplía OL-API con:

### Hooks (Actions)
```php
add_action('ol_api_endpoint_created', function($endpoint) {
    // Tu lógica aquí
});
```

### Filters
```php
add_filter('ol_api_response_data', function($data, $endpoint, $entities) {
    // Modificar datos antes de responder
    return $data;
}, 10, 3);
```

### Campos Personalizados
```php
add_action('ol_api_register_field_providers', function($registry) {
    $registry->register(new MyCustomFieldProvider());
});
```

## 💡 Casos de Uso

| Caso | Descripción |
|------|-------------|
| **Mobile Apps** | API para apps nativas iOS/Android |
| **Headless CMS** | WordPress como CMS sin frontend |
| **Terceros** | APIs seguras para partners |
| **Migraciones** | Capa de datos durante migraciones |
| **Sindicación** | Distribuir contenido |
| **Dashboards** | Datos WordPress en BI tools |

## 🗓️ Roadmap

### ✅ Fase 1: MVP (Versión 1.0)
- Dashboard completamente funcional
- API REST básica
- Multiples métodos de auth
- Documentación automática

### 🔄 Fase 2: Enterprise (Roadmap Q2)
- JWT avanzado
- Field-level permissions
- Entity-level permissions
- Media handling avanzado

### 🚧 Fase 3+: Advanced (Roadmap Q3+)
- GraphQL support
- Webhooks
- OAuth2 completo
- Analytics dashboard

## 🤝 Soporte

- Documentación técnica: [ARCHITECTURE.md](ARCHITECTURE.md)
- Issues & bugs: Reporta en el panel admin
- Sugerencias: Contacta con el equipo

## 📄 Licencia

TBD (Flexible ou Enterprise)

## 👨‍💻 Desarrollo

Para desarrolladores que implementarán el plugin:

1. **Entiende la arquitectura**: Lee [ARCHITECTURE.md](ARCHITECTURE.md)
2. **Sigue los estándares**: WPCS + PSR-12
3. **Escribe tests**: 80% coverage mínimo
4. **Documenta**: PHPDoc en todo el código

## 🎓 Tutorial Rápido

### Crear tu primer endpoint en 3 pasos:

**Paso 1**: Ve a OL-API → Endpoints → Nuevo

**Paso 2**: Configura:
- Slug: `products`
- Nombre: `Productos`
- Tipo de dato: `Post Type`
- Select: `product`

**Paso 3**: Selecciona campos:
- [ ] ID
- [x] Título
- [x] Contenido
- [x] Imagen destacada
- [x] Precio (custom field)

**¡Listo!** Tu API está en: `/wp-json/ol-api/v1/products`

### Consumir desde una app:

```javascript
// Con API Key
fetch('/wp-json/ol-api/v1/products', {
  headers: {
    'X-API-Key': 'sk_live_abcd1234'
  }
})
.then(r => r.json())
.then(data => console.log(data))
```

```curl
curl -H "X-API-Key: sk_live_abcd1234" \
  https://misite.com/wp-json/ol-api/v1/products
```

## 📊 Ejemplo de Respuesta

```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "title": "Producto A",
      "content": "Descripción...",
      "featured_image": {
        "id": 456,
        "url": "https://...",
        "alt": "Producto"
      },
      "price": 99.99
    }
  ],
  "meta": {
    "total": 1,
    "page": 1,
    "per_page": 20,
    "has_next": false
  },
  "links": {
    "self": "/wp-json/ol-api/v1/products?page=1"
  }
}
```

---

**OL-API - Convierte WordPress en una API Profesional** 🚀