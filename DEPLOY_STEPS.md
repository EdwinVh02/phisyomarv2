# 🚀 Pasos para Desplegar en Railway

## Paso 1: Preparar el Repositorio

1. **Asegúrate de que todos los archivos estén committeados**:
   ```bash
   git add .
   git commit -m "feat: implementar sistema completo de gestión de roles

   - Observer automático para creación de registros específicos por rol
   - Middleware para detección de perfiles incompletos
   - Endpoints API para completar perfiles
   - Comando para corregir inconsistencias existentes
   - Despliegue automatizado en Railway
   
   🤖 Generated with Claude Code"
   
   git push origin main
   ```

## Paso 2: Configurar Variables de Entorno en Railway

### Variables Críticas (OBLIGATORIAS):
```
APP_KEY=base64:DoFynkl+mTwhyP5mPVDkG36EL5SBAB+WPevPf7CebS4=
APP_ENV=production
APP_DEBUG=false
DATABASE_URL=tu-database-url-de-railway
LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_STORE=database
```

### Cómo agregar las variables:
1. Ve a tu proyecto en Railway
2. Settings → Environment
3. Agrega cada variable una por una
4. **¡IMPORTANTE!** Asegúrate de que `DATABASE_URL` esté configurada correctamente

## Paso 3: Verificar Base de Datos

1. **Conectar MySQL en Railway**:
   - Agrega MySQL service si no lo has hecho
   - Copia la `DATABASE_URL` a las variables de entorno

2. **Las migraciones se ejecutarán automáticamente** durante el despliegue

## Paso 4: Desplegar

1. **Push a GitHub** (si no lo has hecho ya):
   ```bash
   git push origin main
   ```

2. **Railway detectará automáticamente** el push y comenzará el despliegue

3. **Monitorear el proceso**:
   - Ve a Railway dashboard
   - Pestaña "Deployments"
   - Observa los logs en tiempo real

## Paso 5: Verificar que Todo Funcione

### Después del despliegue exitoso:

1. **Verificar endpoints básicos**:
   ```bash
   # Verificar que la API responda
   curl https://tu-app.railway.app/api/especialidades
   
   # Debería devolver lista de especialidades
   ```

2. **Probar login existente**:
   ```bash
   curl -X POST https://tu-app.railway.app/api/login \
     -H "Content-Type: application/json" \
     -d '{"correo_electronico":"tu-email","contraseña":"tu-password"}'
   ```

3. **Verificar datos específicos del usuario**:
   ```bash
   curl -H "Authorization: Bearer tu-token" \
     https://tu-app.railway.app/api/user
   
   # Deberías ver datos específicos del rol (paciente, terapeuta, etc.)
   ```

## Paso 6: Probar Nueva Funcionalidad

### Test del middleware de perfiles:
1. **Intentar acceder a ruta de paciente**:
   ```bash
   curl -H "Authorization: Bearer tu-token" \
     https://tu-app.railway.app/api/paciente/mis-citas
   ```

2. **Si el perfil está incompleto**, deberías recibir:
   ```json
   {
     "profile_incomplete": true,
     "role": "Paciente",
     "missing_fields": ["contacto_emergencia_nombre"],
     "redirect_url": "/paciente/profile/complete"
   }
   ```

### Test de completar perfil:
```bash
curl -X POST https://tu-app.railway.app/api/user/profile/complete \
  -H "Authorization: Bearer tu-token" \
  -H "Content-Type: application/json" \
  -d '{
    "contacto_emergencia_nombre": "Juan Pérez",
    "contacto_emergencia_telefono": "555-1234",
    "contacto_emergencia_parentesco": "Hermano"
  }'
```

## Paso 7: Verificar Logs

En Railway, ve a la pestaña "Logs" y busca:

```
✅ Inicialización completada exitosamente
📊 Estadísticas del proceso:
Total de registros creados: X
```

## 🚨 Solución de Problemas Comunes

### Error: "Target class [ProfileCompletionController] does not exist"
- **Solución**: Ejecutar `composer dump-autoload` se hace automáticamente en el build

### Error: "SQLSTATE[42S02]: Base table or view not found"
- **Solución**: Las migraciones fallan. Verificar `DATABASE_URL`

### Error: "Class 'App\Observers\UsuarioObserver' not found"
- **Solución**: Asegúrate de que todos los archivos estén en el commit

### Usuario no ve datos específicos de rol:
1. **Verificar que el comando se ejecutó**:
   ```bash
   railway logs | grep "initialize-data"
   ```
2. **Ejecutar manualmente si es necesario**:
   ```bash
   railway run php artisan production:initialize-data --force
   ```

## ✅ Checkpoint Final

Cuando todo esté funcionando, deberías poder:

- ✅ Hacer login con usuarios existentes
- ✅ Ver datos específicos del rol en `/api/user`
- ✅ Completar perfiles incompletos via API
- ✅ Crear nuevos usuarios con registros automáticos
- ✅ Cambiar roles sin pérdida de datos

## 🎉 ¡Despliegue Completado!

Tu aplicación ahora tiene:
- **Gestión automática de roles** ✨
- **Perfiles íntegros garantizados** 🛡️
- **Experiencia de usuario fluida** 🚀
- **Corrección automática de datos** 🔧

**¡Solo haz push y Railway se encarga del resto!** 🎯