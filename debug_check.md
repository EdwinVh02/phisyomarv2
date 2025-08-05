# Debug para Login en Railway

## Problema
Error 500 en login solo en Railway (producción), funciona localmente.

## Endpoints de Debug Creados

### 1. Verificar Estado General
```
GET https://phisyomarv2-production.up.railway.app/api/debug-railway-login
```

### 2. Login Simplificado (sin UserRoleRegistrationService)
```
POST https://phisyomarv2-production.up.railway.app/api/debug-simple-login
```
Body:
```json
{
  "correo_electronico": "tu-email@ejemplo.com",
  "contraseña": "tu-contraseña"
}
```

### 3. Ver Logs del Servidor
```
GET https://phisyomarv2-production.up.railway.app/api/debug-logs
```

## Mejoras Aplicadas

1. **Logging detallado** en AuthController
2. **Manejo de errores** mejorado con try-catch
3. **Fallback graceful** si UserRoleRegistrationService falla
4. **Debug endpoints** temporales

## Pasos a Seguir

1. Esperar a que se desplieguen los cambios en Railway
2. Probar el endpoint `debug-railway-login` primero
3. Probar el `debug-simple-login` 
4. Intentar login normal y revisar logs
5. Identificar la causa exacta del error

## Posibles Causas Identificadas

- UserRoleRegistrationService fallando
- Problema con relaciones de BD (terapeuta, paciente, etc.)
- Migraciones no ejecutadas correctamente
- Problemas de memoria/timeout en Railway
- Configuración de entorno diferente