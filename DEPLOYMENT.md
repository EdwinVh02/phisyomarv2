# 🚀 Guía de Despliegue - PhisyoMar v2

## Variables de Entorno para Railway

### Variables Obligatorias
```env
# Aplicación
APP_NAME=PhisyoMar
APP_ENV=production
APP_KEY=base64:TU_APP_KEY_AQUI
APP_DEBUG=false
APP_URL=https://tu-dominio.railway.app

# Base de Datos (Railway MySQL)
DATABASE_URL=mysql://usuario:password@host:puerto/database
# O separadas:
DB_CONNECTION=mysql
DB_HOST=tu-host-mysql.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=tu-password-mysql

# Logs
LOG_CHANNEL=stderr
LOG_LEVEL=error

# Sesiones y Cache
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Variables Opcionales (Recomendadas)
```env
# Email (si usas notificaciones)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=tu-username
MAIL_PASSWORD=tu-password
MAIL_FROM_ADDRESS="noreply@phisyomar.com"
MAIL_FROM_NAME="PhisyoMar"

# AWS (si usas almacenamiento de archivos)
AWS_ACCESS_KEY_ID=tu-access-key
AWS_SECRET_ACCESS_KEY=tu-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=tu-bucket

# PayPal (del frontend)
PAYPAL_CLIENT_ID=tu-paypal-client-id
PAYPAL_ENVIRONMENT=sandbox # o production
```

## 🔧 Proceso de Despliegue Automático

Cuando hagas push a GitHub, Railway ejecutará automáticamente:

1. **Build Phase**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan config:cache
   php artisan route:cache
   ```

2. **Start Phase**:
   ```bash
   php artisan migrate --force
   php artisan production:initialize-data --force
   php artisan serve --host=0.0.0.0 --port=$PORT
   ```

## ⚡ Lo que hace `production:initialize-data`

Este comando **corrige automáticamente** cualquier inconsistencia en la base de datos:

- ✅ Verifica usuarios con roles sin registros específicos
- ✅ Crea registros faltantes en `pacientes`, `terapeutas`, `recepcionistas`, `administradores`
- ✅ Mantiene datos existentes intactos
- ✅ Ejecuta dentro de una transacción (rollback automático si hay errores)
- ✅ Genera logs detallados del proceso

## 🎯 Nueva Funcionalidad Implementada

### Observer Automático
- **Usuarios nuevos**: Registro específico de rol creado automáticamente
- **Cambios de rol**: Transición de datos gestionada automáticamente
- **Protección de datos**: No elimina información con historial

### Middleware de Perfiles
- **Detección automática**: Identifica perfiles incompletos
- **Redirección inteligente**: Lleva al formulario correcto según el rol
- **API friendly**: Respuestas JSON para aplicaciones frontend

### Endpoints de Perfiles
```bash
# Verificar completitud del perfil
GET /api/user/profile/check-completeness

# Obtener datos específicos del rol
GET /api/user/profile/data

# Completar perfil
POST /api/user/profile/complete

# Actualizar campo específico
PATCH /api/user/profile/update-field
```

## 🐛 Solución de Problemas

### Si los usuarios no ven sus datos específicos:
1. El comando `production:initialize-data` se ejecuta automáticamente
2. Si necesitas ejecutarlo manualmente:
   ```bash
   railway run php artisan production:initialize-data --force
   ```

### Si el middleware no detecta perfiles incompletos:
- Verifica que las rutas tengan el middleware `profile.complete`
- Revisa los logs en Railway para errores del middleware

### Si el Observer no funciona:
- Confirma que `AppServiceProvider` registra el Observer
- Verifica que las tablas tengan las columnas necesarias

## 📋 Checklist Pre-Despliegue

- [ ] Todas las migraciones están en `database/migrations/`
- [ ] Variables de entorno configuradas en Railway
- [ ] Base de datos MySQL conectada
- [ ] Archivos estáticos generados (`npm run build`)
- [ ] Observer registrado en `AppServiceProvider`
- [ ] Middleware registrado en `Kernel.php`
- [ ] Comandos personalizados funcionando

## 🔄 Comandos Útiles Post-Despliegue

```bash
# Verificar estado de la base de datos
railway run php artisan fix:role-data-consistency --dry-run

# Limpiar cache
railway run php artisan cache:clear
railway run php artisan config:clear

# Ver logs en tiempo real
railway logs

# Ejecutar migraciones manualmente
railway run php artisan migrate --force
```

## 🎉 ¡Listo para Producción!

Tu sistema ahora tiene:
- ✅ **Integridad de datos garantizada**
- ✅ **Perfiles completos automáticamente**
- ✅ **Cambios de rol sin pérdida de datos**
- ✅ **Despliegue completamente automatizado**
- ✅ **Corrección automática de inconsistencias**

**¡Solo haz push a GitHub y Railway se encarga del resto!** 🚀