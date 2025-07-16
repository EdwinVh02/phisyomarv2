# 🏥 SISTEMA DE ROLES Y PERMISOS - PHISYOMARV2

## 📋 ESTRUCTURA DE ROLES

### 🔴 **ADMINISTRADOR (ID: 1)**
**Acceso**: Total al sistema
**Finalidad**: Gestión completa de la clínica y supervisión general

#### ✅ Permisos Completos:
- **Gestión de Clínica**: Configuración, servicios, tarifas
- **Gestión de Usuarios**: Crear, modificar, eliminar usuarios de todos los roles
- **Gestión de Terapeutas**: Asignación, especialidades, horarios
- **Gestión de Recepcionistas**: Control operativo
- **Gestión de Servicios**: Catálogos médicos, tratamientos
- **Gestión de Pagos**: Control financiero completo
- **Estadísticas Generales**: Reportes de toda la clínica
- **Configuración Global**: Parámetros del sistema
- **Auditoría**: Acceso a bitácoras y logs

#### 🚀 Rutas de API Exclusivas:
```
POST|GET|PUT|DELETE /api/administradores
POST|GET|PUT|DELETE /api/clinicas
POST|GET|PUT|DELETE /api/usuarios
GET /api/bitacoras
```

---

### 🟢 **TERAPEUTA (ID: 2)**
**Acceso**: Limitado a pacientes asignados
**Finalidad**: Atención médica especializada

#### ✅ Permisos Específicos:
- **Atender Pacientes**: Solo pacientes asignados a través de citas
- **Registrar Tratamientos**: Comentarios clínicos y observaciones
- **Gestionar Citas**: Ver y modificar sus propias citas
- **Historial Médico**: Acceso a registros de sus pacientes
- **Encuestas**: Aplicar encuestas de satisfacción
- **Estadísticas Personales**: Ver su rendimiento individual
- **Datos Clínicos**: Actualizar escalas de dolor, tratamientos

#### 🚫 Restricciones:
- No puede ver pacientes de otros terapeutas
- No puede acceder a configuración global
- No puede ver estadísticas generales
- No puede gestionar pagos

#### 🚀 Rutas de API Específicas:
```
GET /api/terapeuta/mis-citas
GET /api/terapeuta/mis-pacientes
GET /api/terapeuta/estadisticas
POST|PUT /api/registros (solo sus pacientes)
POST|PUT /api/valoraciones (solo sus pacientes)
```

---

### 🔵 **RECEPCIONISTA (ID: 3)**
**Acceso**: Gestión operativa completa
**Finalidad**: Coordinación y administración operativa

#### ✅ Permisos Operativos:
- **Gestión de Pacientes**: Registrar, modificar información
- **Gestión de Terapeutas**: Coordinación de horarios
- **Gestión de Citas**: Agendar, modificar, cancelar para todos
- **Gestión de Lesiones**: Registrar padecimientos
- **Gestión de Encuestas**: Administrar encuestas
- **Control de Pagos**: Procesar y registrar pagos
- **Manejo de Agenda**: Ver agenda completa de la clínica
- **Información a Terapeutas**: Coordinación interna

#### 🚫 Restricciones:
- No accede a configuración global
- No ve estadísticas generales
- No puede eliminar registros (solo modificar)

#### 🚀 Rutas de API Compartidas:
```
POST|GET|PUT /api/pacientes
POST|GET|PUT /api/terapeutas
POST|GET|PUT|DELETE /api/citas
POST|GET|PUT /api/pagos
GET /api/encuestas
```

---

### 🟡 **PACIENTE (ID: 4)**
**Acceso**: Solo información personal
**Finalidad**: Autogestión de citas y consulta de información

#### ✅ Permisos Personales:
- **Información Personal**: Ver y modificar datos propios
- **Mis Citas**: Agendar, ver y cancelar citas propias
- **Mi Historial**: Consultar historial médico personal
- **Pagos**: Realizar y consultar pagos de citas
- **Encuestas**: Responder encuestas de satisfacción
- **Facturas**: Ver facturas y comprobantes

#### 🚫 Restricciones Estrictas:
- No puede ver información de otros pacientes
- No puede acceder a datos de terapeutas
- No puede ver agenda completa
- No puede modificar citas ya realizadas

#### 🚀 Rutas de API Específicas:
```
GET /api/paciente/mis-citas
GET /api/paciente/mi-historial
POST /api/paciente/agendar-cita
PUT /api/paciente/cancelar-cita/{id}
GET|PUT /api/pacientes/{id} (solo si ID = usuario autenticado)
```

---

## 🛡️ IMPLEMENTACIÓN TÉCNICA

### 1. **Middleware de Roles**
```php
// Aplicación en rutas
Route::middleware('role:1')->group(function () {
    // Solo administradores
});

Route::middleware('role:1,3')->group(function () {
    // Administradores y recepcionistas
});
```

### 2. **Policies por Modelo**
```php
// PacientePolicy
public function view(Usuario $user, Paciente $paciente): bool
{
    $roleId = $user->rol->id ?? null;
    
    // Administrador y Recepcionista: acceso total
    if (in_array($roleId, [1, 3])) return true;
    
    // Terapeuta: solo pacientes asignados
    if ($roleId === 2) {
        return $paciente->citas()->whereHas('terapeuta', 
            fn($q) => $q->where('id', $user->id))->exists();
    }
    
    // Paciente: solo información propia
    if ($roleId === 4) {
        return $paciente->id === $user->id;
    }
    
    return false;
}
```

### 3. **Validación de Middleware**
```php
// RoleMiddleware verifica:
1. Usuario autenticado
2. Rol asignado válido
3. Rol permitido para la ruta
4. Administrador siempre tiene acceso
```

---

## 📊 MATRIZ DE PERMISOS

| Recurso | Admin | Terapeuta | Recepcionista | Paciente |
|---------|-------|-----------|---------------|----------|
| **Usuarios** | ✅ CRUD | ❌ | ❌ | ❌ |
| **Pacientes** | ✅ CRUD | 🔶 Solo asignados | ✅ CRUD | 🔶 Solo propio |
| **Citas** | ✅ CRUD | 🔶 Solo propias | ✅ CRUD | 🔶 Solo propias |
| **Pagos** | ✅ CRUD | ❌ | ✅ CRUD | 🔶 Solo propios |
| **Estadísticas** | ✅ Generales | 🔶 Personales | ❌ | ❌ |
| **Configuración** | ✅ Total | ❌ | ❌ | ❌ |
| **Historiales** | ✅ Todos | 🔶 Asignados | ✅ Todos | 🔶 Solo propio |
| **Encuestas** | ✅ CRUD | 🔶 Aplicar | ✅ CRUD | 🔶 Responder |

**Leyenda:**
- ✅ Acceso completo
- 🔶 Acceso limitado/condicional
- ❌ Sin acceso

---

## 🔧 COMANDOS DE CONFIGURACIÓN

### 1. **Ejecutar Seeder de Roles**
```bash
php artisan db:seed --class=RolesSeeder
```

### 2. **Verificar Middleware**
```bash
php artisan route:list --middleware=role
```

### 3. **Crear Usuario de Prueba**
```php
// En Tinker o Seeder
$admin = Usuario::create([
    'nombre' => 'Admin',
    'correo_electronico' => 'admin@phisyomar.com',
    'contraseña' => 'Admin123!',
    'rol_id' => 1
]);
```

---

## 📝 EJEMPLOS DE USO EN FRONTEND

### 1. **Verificar Rol en Blade/Vue**
```php
@if(auth()->user()->rol->id === 1)
    <button>Panel Administrador</button>
@endif

@if(in_array(auth()->user()->rol->id, [1, 3]))
    <a href="/pacientes">Gestionar Pacientes</a>
@endif
```

### 2. **API Request con Headers**
```javascript
// Login y obtener token
const response = await axios.post('/api/login', credentials);
const token = response.data.token;

// Usar token en requests
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Request que require rol específico
const pacientes = await axios.get('/api/pacientes'); // Solo admin/recepcionista
```

### 3. **Manejo de Errores de Permisos**
```javascript
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response.status === 403) {
            alert('No tienes permisos para esta acción');
            // Redirigir a dashboard apropiado según rol
        }
        return Promise.reject(error);
    }
);
```

---

## 🚨 CONSIDERACIONES DE SEGURIDAD

### 1. **Validación Doble**
- Middleware en rutas (primera capa)
- Policies en controladores (segunda capa)
- Validación en frontend (tercera capa - UX)

### 2. **Principio de Menor Privilegio**
- Cada rol tiene solo los permisos mínimos necesarios
- Escalación solo por administrador
- Auditoría de cambios de roles

### 3. **Tokens y Sesiones**
- Tokens Sanctum con expiración
- Revocación inmediata al cambiar roles
- Logout automático en cambios críticos

---

## 🧪 TESTING DEL SISTEMA

### 1. **Tests de Middleware**
```php
public function test_admin_can_access_all_routes()
{
    $admin = User::factory()->create(['rol_id' => 1]);
    
    $this->actingAs($admin, 'sanctum')
         ->get('/api/usuarios')
         ->assertStatus(200);
}

public function test_paciente_cannot_access_admin_routes()
{
    $paciente = User::factory()->create(['rol_id' => 4]);
    
    $this->actingAs($paciente, 'sanctum')
         ->get('/api/usuarios')
         ->assertStatus(403);
}
```

### 2. **Tests de Policies**
```php
public function test_terapeuta_can_only_see_assigned_patients()
{
    $terapeuta = User::factory()->create(['rol_id' => 2]);
    $paciente = Patient::factory()->create();
    
    // Sin cita asignada
    $this->assertFalse($terapeuta->can('view', $paciente));
    
    // Con cita asignada
    Cita::factory()->create([
        'terapeuta_id' => $terapeuta->terapeuta->id,
        'paciente_id' => $paciente->id
    ]);
    
    $this->assertTrue($terapeuta->can('view', $paciente));
}
```

---

## 📞 SOPORTE Y MANTENIMIENTO

### Logs de Roles
Los cambios de roles se registran automáticamente en:
- `storage/logs/roles.log`
- Tabla `bitacoras` con tipo 'role_change'

### Troubleshooting Común
```bash
# Verificar relación usuario-rol
php artisan tinker
>>> User::with('rol')->find(1)

# Limpiar cache de permisos
php artisan cache:clear

# Regenerar rutas
php artisan route:clear && php artisan route:cache
```

---

*Documentación generada para PhisyomarV2 - Sistema de Gestión de Roles y Permisos*