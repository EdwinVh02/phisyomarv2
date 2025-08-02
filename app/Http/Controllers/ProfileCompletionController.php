<?php

namespace App\Http\Controllers;

use App\Services\ProfileCompletionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProfileCompletionController extends Controller
{
    private ProfileCompletionService $profileService;

    public function __construct(ProfileCompletionService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Verificar el estado de completitud del perfil del usuario actual
     */
    public function checkCompleteness(Request $request): JsonResponse
    {
        $user = Auth::user();
        $status = $this->profileService->isProfileComplete($user);
        $requiredFields = $this->profileService->getRequiredFieldsForRole($user->rol_id);

        return response()->json([
            'success' => true,
            'data' => [
                'complete' => $status['complete'],
                'missing_fields' => $status['missing_fields'],
                'required_fields' => $requiredFields,
                'role' => $user->getRoleName(),
                'user_id' => $user->id
            ]
        ]);
    }

    /**
     * Completar el perfil del usuario actual
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            // Validar datos según el rol
            $validatedData = $this->validateProfileData($request, $user->rol_id);
            
            // Completar perfil
            $result = $this->profileService->completeProfile($user, $validatedData);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los datos actuales del perfil específico del usuario
     */
    public function getProfileData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $profileData = null;

        switch ($user->rol_id) {
            case 4: // Paciente
                $profileData = $user->paciente;
                break;
            case 2: // Terapeuta
                $profileData = $user->terapeuta ? $user->terapeuta->load('especialidades') : null;
                break;
            case 3: // Recepcionista
                $profileData = $user->recepcionista;
                break;
            case 1: // Administrador
                $profileData = $user->administrador ? $user->administrador->load('clinica') : null;
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile' => $profileData,
                'role' => $user->getRoleName(),
                'required_fields' => $this->profileService->getRequiredFieldsForRole($user->rol_id)
            ]
        ]);
    }

    /**
     * Validar datos del perfil según el rol
     */
    private function validateProfileData(Request $request, int $rolId): array
    {
        $rules = match ($rolId) {
            4 => [ // Paciente
                'contacto_emergencia_nombre' => 'required|string|max:100',
                'contacto_emergencia_telefono' => 'required|string|max:20',
                'contacto_emergencia_parentesco' => 'required|string|max:50',
                'tutor_nombre' => 'nullable|string|max:100',
                'tutor_telefono' => 'nullable|string|max:20',
                'tutor_parentesco' => 'nullable|string|max:50',
                'tutor_direccion' => 'nullable|string|max:150',
            ],
            2 => [ // Terapeuta
                'cedula_profesional' => 'required|string|max:30|unique:terapeutas,cedula_profesional,' . Auth::id(),
                'especialidad_principal' => 'required|string|max:100',
                'experiencia_anios' => 'required|integer|min:0|max:50',
                'estatus' => 'nullable|in:activo,inactivo,suspendido',
                'especialidades' => 'nullable|array',
                'especialidades.*' => 'exists:especialidads,id'
            ],
            3 => [ // Recepcionista
                // Agregar reglas cuando se definan campos específicos
            ],
            1 => [ // Administrador
                'cedula_profesional' => 'nullable|string|max:30|unique:administradors,cedula_profesional,' . Auth::id(),
                'clinica_id' => 'nullable|exists:clinicas,id'
            ],
            default => []
        };

        return $request->validate($rules);
    }

    /**
     * Obtener información sobre qué campos faltan por completar
     */
    public function getMissingFields(Request $request): JsonResponse
    {
        $user = Auth::user();
        $status = $this->profileService->isProfileComplete($user);
        $requiredFields = $this->profileService->getRequiredFieldsForRole($user->rol_id);

        $missingFieldsInfo = [];
        foreach ($status['missing_fields'] as $field) {
            $missingFieldsInfo[$field] = $requiredFields[$field] ?? ucfirst(str_replace('_', ' ', $field));
        }

        return response()->json([
            'success' => true,
            'data' => [
                'complete' => $status['complete'],
                'missing_count' => count($status['missing_fields']),
                'missing_fields' => $missingFieldsInfo,
                'role' => $user->getRoleName()
            ]
        ]);
    }

    /**
     * Actualizar campos específicos del perfil (para actualizaciones parciales)
     */
    public function updateProfileField(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $field = $request->input('field');
            $value = $request->input('value');

            // Validar que el campo sea válido para el rol
            $allowedFields = array_keys($this->profileService->getRequiredFieldsForRole($user->rol_id));
            
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campo no válido para este rol'
                ], 400);
            }

            // Preparar datos para actualización
            $updateData = [$field => $value];
            
            // Completar perfil (solo actualizará el campo especificado)
            $result = $this->profileService->completeProfile($user, $updateData);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar campo: ' . $e->getMessage()
            ], 500);
        }
    }
}