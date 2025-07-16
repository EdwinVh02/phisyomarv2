<?php

namespace App\Helpers;

use App\Models\Usuario;

class RoleHelper
{
    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function hasRole(Usuario $user, $roleId): bool
    {
        return $user->rol && $user->rol->id == $roleId;
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public static function hasAnyRole(Usuario $user, array $roleIds): bool
    {
        return $user->rol && in_array($user->rol->id, $roleIds);
    }

    /**
     * Verificar si es administrador
     */
    public static function isAdmin(Usuario $user): bool
    {
        return self::hasRole($user, 1);
    }

    /**
     * Verificar si es terapeuta
     */
    public static function isTerapeuta(Usuario $user): bool
    {
        return self::hasRole($user, 2);
    }

    /**
     * Verificar si es recepcionista
     */
    public static function isRecepcionista(Usuario $user): bool
    {
        return self::hasRole($user, 3);
    }

    /**
     * Verificar si es paciente
     */
    public static function isPaciente(Usuario $user): bool
    {
        return self::hasRole($user, 4);
    }

    /**
     * Verificar si puede gestionar pacientes
     */
    public static function canManagePatients(Usuario $user): bool
    {
        return self::hasAnyRole($user, [1, 3]); // Admin o Recepcionista
    }

    /**
     * Verificar si puede ver estadísticas generales
     */
    public static function canViewGeneralStats(Usuario $user): bool
    {
        return self::hasRole($user, 1); // Solo Admin
    }

    /**
     * Verificar si puede gestionar citas
     */
    public static function canManageAppointments(Usuario $user): bool
    {
        return self::hasAnyRole($user, [1, 2, 3]); // Admin, Terapeuta, Recepcionista
    }

    /**
     * Verificar si puede acceder a información financiera
     */
    public static function canAccessFinancials(Usuario $user): bool
    {
        return self::hasAnyRole($user, [1, 3]); // Admin o Recepcionista
    }

    /**
     * Obtener nombre del rol
     */
    public static function getRoleName(Usuario $user): ?string
    {
        return $user->rol ? $user->rol->nombre : null;
    }

    /**
     * Obtener permisos del rol
     */
    public static function getRolePermissions(Usuario $user): array
    {
        if (!$user->rol || !$user->rol->permisos) {
            return [];
        }

        return json_decode($user->rol->permisos, true) ?? [];
    }

    /**
     * Verificar si tiene un permiso específico
     */
    public static function hasPermission(Usuario $user, string $permission): bool
    {
        $permissions = self::getRolePermissions($user);
        return isset($permissions[$permission]) && $permissions[$permission] === true;
    }

    /**
     * Obtener rutas permitidas según el rol
     */
    public static function getAllowedRoutes(Usuario $user): array
    {
        $roleId = $user->rol->id ?? null;

        $routes = [
            'common' => [
                '/api/logout',
                '/api/user',
                '/api/especialidades',
                '/api/padecimientos',
                '/api/tarifas',
                '/api/tratamientos'
            ]
        ];

        switch ($roleId) {
            case 1: // Administrador
                $routes['admin'] = [
                    '/api/administradores',
                    '/api/clinicas',
                    '/api/usuarios',
                    '/api/bitacoras',
                    '/api/pacientes',
                    '/api/recepcionistas',
                    '/api/terapeutas',
                    '/api/encuestas',
                    '/api/pagos',
                    '/api/citas',
                    '/api/registros',
                    '/api/historiales'
                ];
                break;

            case 2: // Terapeuta
                $routes['terapeuta'] = [
                    '/api/terapeuta/mis-citas',
                    '/api/terapeuta/mis-pacientes',
                    '/api/terapeuta/estadisticas',
                    '/api/citas',
                    '/api/registros',
                    '/api/valoraciones'
                ];
                break;

            case 3: // Recepcionista
                $routes['recepcionista'] = [
                    '/api/pacientes',
                    '/api/terapeutas',
                    '/api/citas',
                    '/api/pagos',
                    '/api/encuestas',
                    '/api/paquetes_paciente',
                    '/api/paquetes_sesion'
                ];
                break;

            case 4: // Paciente
                $routes['paciente'] = [
                    '/api/paciente/mis-citas',
                    '/api/paciente/mi-historial',
                    '/api/paciente/agendar-cita'
                ];
                break;
        }

        return $routes;
    }

    /**
     * Verificar si puede acceder a una ruta específica
     */
    public static function canAccessRoute(Usuario $user, string $route): bool
    {
        $allowedRoutes = self::getAllowedRoutes($user);
        
        foreach ($allowedRoutes as $routeGroup) {
            if (in_array($route, $routeGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener dashboard URL según el rol
     */
    public static function getDashboardUrl(Usuario $user): string
    {
        $roleId = $user->rol->id ?? null;

        switch ($roleId) {
            case 1:
                return '/admin/dashboard';
            case 2:
                return '/terapeuta/dashboard';
            case 3:
                return '/recepcionista/dashboard';
            case 4:
                return '/paciente/dashboard';
            default:
                return '/dashboard';
        }
    }
}