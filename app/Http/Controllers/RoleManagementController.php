<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleManagementController extends Controller
{
    /**
     * Obtener todos los usuarios con sus roles
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $query = Usuario::with(['rol', 'administrador', 'terapeuta', 'recepcionista', 'paciente']);

            // Filtros opcionales
            if ($request->has('rol_id') && $request->rol_id !== '') {
                $query->where('rol_id', $request->rol_id);
            }

            if ($request->has('estatus') && $request->estatus !== '') {
                $query->where('estatus', $request->estatus);
            }

            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('apellido_paterno', 'LIKE', "%{$search}%")
                      ->orWhere('apellido_materno', 'LIKE', "%{$search}%")
                      ->orWhere('correo_electronico', 'LIKE', "%{$search}%");
                });
            }

            $users = $query->orderBy('created_at', 'desc')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Usuarios obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todos los roles disponibles
     */
    public function getRoles(): JsonResponse
    {
        try {
            $roles = Rol::orderBy('id')->get();
            
            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Roles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar el rol de un usuario
     */
    public function changeUserRole(Request $request, $userId): JsonResponse
    {
        try {
            $request->validate([
                'rol_id' => 'required|exists:roles,id',
                'motivo' => 'nullable|string|max:255'
            ]);

            $usuario = Usuario::with('rol')->findOrFail($userId);
            $newRole = Rol::findOrFail($request->rol_id);
            $oldRoleName = $usuario->rol ? $usuario->rol->name : 'Sin rol';

            // No permitir cambiar el rol del usuario actual si es admin
            if ($request->user()->id === $usuario->id && $request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes cambiar tu propio rol de administrador'
                ], 400);
            }

            DB::beginTransaction();

            // Cambiar el rol (el Observer se encargará de crear/actualizar registros específicos)
            $usuario->rol_id = $request->rol_id;
            $usuario->save();

            // Crear bitácora del cambio
            DB::table('bitacoras')->insert([
                'usuario_id' => $request->user()->id,
                'accion' => 'cambio_rol',
                'tabla' => 'usuarios',
                'registro_id' => $usuario->id,
                'fecha_hora' => now(),
                'detalle' => json_encode([
                    'descripcion' => "Cambio de rol de '{$oldRoleName}' a '{$newRole->name}' para usuario {$usuario->correo_electronico}",
                    'usuario_afectado' => $usuario->correo_electronico,
                    'rol_anterior' => $oldRoleName,
                    'rol_nuevo' => $newRole->name,
                    'motivo' => $request->motivo ?? 'Sin motivo especificado',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent')
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Rol cambiado exitosamente de '{$oldRoleName}' a '{$newRole->name}'",
                'data' => [
                    'usuario' => $usuario->load('rol'),
                    'rol_anterior' => $oldRoleName,
                    'rol_nuevo' => $newRole->name
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar o desactivar usuario
     */
    public function toggleUserStatus(Request $request, $userId): JsonResponse
    {
        try {
            $request->validate([
                'estatus' => 'required|in:activo,inactivo,suspendido',
                'motivo' => 'nullable|string|max:255'
            ]);

            $usuario = Usuario::findOrFail($userId);
            $oldStatus = $usuario->estatus;

            // No permitir desactivar el usuario actual
            if ($request->user()->id === $usuario->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes cambiar tu propio estado'
                ], 400);
            }

            DB::beginTransaction();

            $usuario->estatus = $request->estatus;
            $usuario->save();

            // Crear bitácora del cambio
            DB::table('bitacoras')->insert([
                'usuario_id' => $request->user()->id,
                'accion' => 'cambio_estatus',
                'tabla' => 'usuarios',
                'registro_id' => $usuario->id,
                'fecha_hora' => now(),
                'detalle' => json_encode([
                    'descripcion' => "Cambio de estatus de '{$oldStatus}' a '{$request->estatus}' para usuario {$usuario->correo_electronico}",
                    'usuario_afectado' => $usuario->correo_electronico,
                    'estatus_anterior' => $oldStatus,
                    'estatus_nuevo' => $request->estatus,
                    'motivo' => $request->motivo ?? 'Sin motivo especificado',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent')
                ])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Estado cambiado exitosamente de '{$oldStatus}' a '{$request->estatus}'",
                'data' => [
                    'usuario' => $usuario,
                    'estatus_anterior' => $oldStatus,
                    'estatus_nuevo' => $request->estatus
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de usuarios por rol
     */
    public function getUserStats(): JsonResponse
    {
        try {
            $stats = DB::table('usuarios')
                ->select('roles.name as rol_name', 'usuarios.estatus', DB::raw('COUNT(*) as count'))
                ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
                ->groupBy('roles.name', 'usuarios.estatus')
                ->orderBy('roles.name')
                ->get();

            $formattedStats = [];
            foreach ($stats as $stat) {
                if (!isset($formattedStats[$stat->rol_name])) {
                    $formattedStats[$stat->rol_name] = [
                        'total' => 0,
                        'activo' => 0,
                        'inactivo' => 0,
                        'suspendido' => 0
                    ];
                }
                $formattedStats[$stat->rol_name][$stat->estatus] = $stat->count;
                $formattedStats[$stat->rol_name]['total'] += $stat->count;
            }

            return response()->json([
                'success' => true,
                'data' => $formattedStats,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios de roles
     */
    public function getRoleChangeHistory(Request $request): JsonResponse
    {
        try {
            $query = DB::table('bitacoras')
                ->select('bitacoras.*', 'usuarios.nombre', 'usuarios.apellido_paterno', 'usuarios.correo_electronico')
                ->join('usuarios', 'bitacoras.usuario_id', '=', 'usuarios.id')
                ->whereIn('bitacoras.accion', ['cambio_rol', 'cambio_estatus'])
                ->orderBy('bitacoras.fecha_hora', 'desc');

            if ($request->has('usuario_id') && $request->usuario_id !== '') {
                $query->where('bitacoras.registro_id', $request->usuario_id);
            }

            $history = $query->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $history,
                'message' => 'Historial obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }
}