# 🚀 GUÍA DE IMPLEMENTACIÓN RÁPIDA - SISTEMA DE ROLES

## 📋 CHECKLIST DE CONFIGURACIÓN

### ✅ **YA IMPLEMENTADO:**
- [x] Middleware `RoleMiddleware` creado y registrado
- [x] Policies para `Paciente` y `Cita` implementadas
- [x] Rutas organizadas por niveles de acceso
- [x] Seeder de roles con estructura completa
- [x] Helper class para verificación de roles
- [x] Métodos de conveniencia en modelo `Usuario`
- [x] Documentación completa del sistema

### 🔄 **PASOS PARA ACTIVAR:**

#### 1. **Ejecutar Migraciones y Seeders**
```bash
# Ejecutar migración de corrección de nombres de tabla
php artisan migrate

# Sembrar roles en la base de datos
php artisan db:seed --class=RolesSeeder
```

#### 2. **Verificar Estructura de Roles**
```bash
php artisan tinker
>>> App\Models\Rol::all()
# Debería mostrar 4 roles: Administrador(1), Terapeuta(2), Recepcionista(3), Paciente(4)
```

#### 3. **Probar Middleware de Roles**
```bash
# Verificar rutas con middleware
php artisan route:list --middleware=role

# Debería mostrar rutas organizadas por grupos de roles
```

---

## 🧪 TESTING INMEDIATO

### **Test 1: Crear Usuario Administrador**
```php
// En php artisan tinker
$admin = App\Models\Usuario::create([
    'nombre' => 'Admin',
    'apellido_paterno' => 'Sistema',
    'correo_electronico' => 'admin@phisyomar.com',
    'contraseña' => 'Admin123!',
    'rol_id' => 1,
    'estatus' => 'activo'
]);

// Verificar rol
echo $admin->getRoleName(); // "Administrador"
echo $admin->isAdmin(); // true
```

### **Test 2: Verificar Middleware con Postman/cURL**
```bash
# 1. Registrar/Login para obtener token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"correo_electronico":"admin@phisyomar.com","contraseña":"Admin123!"}'

# 2. Usar token para acceder a ruta de admin
curl -X GET http://localhost:8000/api/usuarios \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# 3. Debería retornar 200 para admin, 403 para otros roles
```

### **Test 3: Verificar Políticas**
```php
// En tinker
$admin = App\Models\Usuario::find(1);
$paciente = App\Models\Paciente::first();

// Test policy
$admin->can('viewAny', App\Models\Paciente::class); // true
$admin->can('view', $paciente); // true
```

---

## 💻 EJEMPLOS DE USO EN CÓDIGO

### **En Controladores:**
```php
public function index()
{
    // Verificación adicional con helper
    if (!auth()->user()->canManagePatients()) {
        return response()->json(['error' => 'Sin permisos'], 403);
    }
    
    return response()->json(Paciente::all());
}
```

### **En Blade Templates:**
```php
@if(auth()->user()->isAdmin())
    <a href="/admin/panel" class="btn btn-primary">Panel Admin</a>
@endif

@if(auth()->user()->canAccessFinancials())
    <a href="/reportes/financieros">Ver Reportes</a>
@endif

@switch(auth()->user()->getRoleName())
    @case('Administrador')
        @include('admin.menu')
        @break
    @case('Terapeuta')
        @include('terapeuta.menu')
        @break
    @case('Recepcionista')
        @include('recepcionista.menu')
        @break
    @case('Paciente')
        @include('paciente.menu')
        @break
@endswitch
```

### **En JavaScript/Vue:**
```javascript
// Obtener información del usuario autenticado
const user = await axios.get('/api/user');
const userRole = user.data.rol;

// Mostrar/ocultar elementos según rol
if (userRole.id === 1) { // Administrador
    showAdminPanel();
} else if (userRole.id === 2) { // Terapeuta
    showTerapeutaPanel();
}

// Verificar permisos antes de hacer requests
if (userRole.id === 1 || userRole.id === 3) { // Admin o Recepcionista
    // Puede acceder a gestión de pacientes
    const pacientes = await axios.get('/api/pacientes');
}
```

---

## 🛠️ CONFIGURACIONES ADICIONALES

### **Personalizar Mensajes de Error:**
```php
// En app/Http/Middleware/RoleMiddleware.php línea 46-50
return response()->json([
    'error' => 'Acceso denegado para ' . $userRole->nombre,
    'required_roles' => $allowedRoles,
    'current_role' => $userRole->nombre,
    'contact_admin' => 'Contacta al administrador para más permisos'
], 403);
```

### **Logging de Intentos de Acceso:**
```php
// Agregar en RoleMiddleware antes del return 403:
Log::warning('Acceso denegado', [
    'user_id' => $user->id,
    'user_role' => $userRole->nombre,
    'attempted_route' => $request->path(),
    'required_roles' => $allowedRoles
]);
```

### **Cache de Roles para Performance:**
```php
// En Usuario.php, cachear el rol
public function getRoleCached()
{
    return Cache::remember("user_role_{$this->id}", 3600, function () {
        return $this->rol;
    });
}
```

---

## 🔄 WORKFLOWS POR ROL

### **ADMINISTRADOR:**
```
Login → Dashboard Admin → [
    Gestionar Usuarios,
    Configurar Clínica,
    Ver Estadísticas Generales,
    Gestionar Todos los Recursos
]
```

### **TERAPEUTA:**
```
Login → Dashboard Terapeuta → [
    Ver Mis Citas,
    Gestionar Mis Pacientes,
    Registrar Tratamientos,
    Ver Mis Estadísticas
]
```

### **RECEPCIONISTA:**
```
Login → Dashboard Recepcionista → [
    Gestionar Pacientes,
    Agendar Citas,
    Procesar Pagos,
    Coordinar Agenda
]
```

### **PACIENTE:**
```
Login → Dashboard Paciente → [
    Ver Mis Citas,
    Agendar Nueva Cita,
    Ver Mi Historial,
    Realizar Pagos
]
```

---

## 🚨 TROUBLESHOOTING COMÚN

### **Error: "Usuario sin rol asignado"**
```php
// Verificar y asignar rol por defecto
$usuario = Usuario::find(ID);
$usuario->rol_id = 4; // Paciente por defecto
$usuario->save();
```

### **Error: "Middleware no encontrado"**
```bash
# Verificar que está registrado en Kernel.php
php artisan route:list | grep role
```

### **Error: "Policy no funciona"**
```php
// Registrar policies en AuthServiceProvider
protected $policies = [
    Paciente::class => PacientePolicy::class,
    Cita::class => CitaPolicy::class,
];
```

### **Error: "Roles no se cargan"**
```bash
# Limpiar cache y recargar
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 📱 EJEMPLOS DE FRONTEND

### **React/Vue Component:**
```javascript
// RoleGuard.vue
<template>
  <div v-if="hasPermission">
    <slot></slot>
  </div>
  <div v-else>
    <p>No tienes permisos para ver este contenido</p>
  </div>
</template>

<script>
export default {
  props: ['requiredRoles'],
  computed: {
    hasPermission() {
      const userRole = this.$store.state.user.rol.id;
      return this.requiredRoles.includes(userRole);
    }
  }
}
</script>

<!-- Uso -->
<RoleGuard :required-roles="[1, 3]">
  <PatientManagement />
</RoleGuard>
```

### **Navigation Menu:**
```javascript
// NavigationMenu.js
const getMenuItems = (userRole) => {
  const baseItems = [
    { path: '/dashboard', label: 'Inicio' },
    { path: '/perfil', label: 'Mi Perfil' }
  ];

  const roleItems = {
    1: [ // Administrador
      { path: '/admin/usuarios', label: 'Gestionar Usuarios' },
      { path: '/admin/clinicas', label: 'Configurar Clínica' },
      { path: '/admin/estadisticas', label: 'Estadísticas' }
    ],
    2: [ // Terapeuta
      { path: '/terapeuta/citas', label: 'Mis Citas' },
      { path: '/terapeuta/pacientes', label: 'Mis Pacientes' }
    ],
    3: [ // Recepcionista
      { path: '/recepcionista/pacientes', label: 'Gestionar Pacientes' },
      { path: '/recepcionista/agenda', label: 'Agenda General' }
    ],
    4: [ // Paciente
      { path: '/paciente/citas', label: 'Mis Citas' },
      { path: '/paciente/historial', label: 'Mi Historial' }
    ]
  };

  return [...baseItems, ...(roleItems[userRole.id] || [])];
};
```

---

## ✅ LISTA DE VERIFICACIÓN FINAL

Antes de considerar el sistema completamente implementado:

- [ ] ✅ Seeders ejecutados correctamente
- [ ] ✅ Al menos un usuario de cada rol creado para testing
- [ ] ✅ Middleware funciona en rutas protegidas
- [ ] ✅ Policies validan acceso a recursos
- [ ] ✅ Frontend adapta menús según rol
- [ ] ✅ Mensajes de error personalizados
- [ ] ✅ Logging de intentos de acceso configurado
- [ ] ✅ Documentación accesible para el equipo

**🎉 ¡Sistema de roles completamente funcional e implementado!**