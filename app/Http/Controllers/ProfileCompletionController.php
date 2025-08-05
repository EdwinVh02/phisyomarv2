<?php

namespace App\Http\Controllers;

use App\Services\UserRoleRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProfileCompletionController extends Controller
{
    /**
     * Verificar el estado de completitud del perfil del usuario actual
     */
    public function checkCompleteness(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Crear automáticamente el registro específico si no existe
        UserRoleRegistrationService::createRoleSpecificRecord($user);
        
        $isComplete = UserRoleRegistrationService::isProfileComplete($user);
        $missingFields = UserRoleRegistrationService::getMissingProfileFields($user);
        $profileData = UserRoleRegistrationService::getUserProfileData($user);

        return response()->json([
            'success' => true,
            'data' => [
                'complete' => $isComplete,
                'missing_fields' => $missingFields,
                'role' => $user->getRoleName(),
                'user_id' => $user->id,
                'profile_data' => $profileData
            ]
        ]);
    }

    /**
     * Obtener los datos actuales del perfil específico del usuario
     */
    public function getProfileData(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Crear automáticamente el registro específico si no existe
        UserRoleRegistrationService::createRoleSpecificRecord($user);
        
        $profileData = UserRoleRegistrationService::getUserProfileData($user);

        return response()->json([
            'success' => true,
            'data' => $profileData
        ]);
    }

    /**
     * Completar el perfil del usuario actual
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            // Crear automáticamente el registro específico si no existe
            UserRoleRegistrationService::createRoleSpecificRecord($user);
            
            // Validar datos según el rol
            $validatedData = $this->validateProfileData($request, $user->rol_id);
            
            // Actualizar datos del usuario base si están presentes
            $userFields = ['telefono', 'direccion', 'fecha_nacimiento', 'sexo', 'curp', 'ocupacion'];
            $userUpdateData = [];
            foreach ($userFields as $field) {
                if (isset($validatedData[$field])) {
                    $userUpdateData[$field] = $validatedData[$field];
                    unset($validatedData[$field]);
                }
            }
            
            if (!empty($userUpdateData)) {
                $user->update($userUpdateData);
            }
            
            // Actualizar datos específicos del rol
            $result = $this->updateRoleSpecificData($user, $validatedData);
            
            if ($result['success']) {
                // Recargar datos completos del perfil
                $profileData = UserRoleRegistrationService::getUserProfileData($user->fresh());
                
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil completado exitosamente',
                    'data' => $profileData
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
     * Obtener información sobre qué campos faltan por completar
     */
    public function getMissingFields(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Crear automáticamente el registro específico si no existe
        UserRoleRegistrationService::createRoleSpecificRecord($user);
        
        $missingFields = UserRoleRegistrationService::getMissingProfileFields($user);
        $isComplete = UserRoleRegistrationService::isProfileComplete($user);

        return response()->json([
            'success' => true,
            'data' => [
                'complete' => $isComplete,
                'missing_count' => count($missingFields),
                'missing_fields' => $missingFields,
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

            // Crear automáticamente el registro específico si no existe
            UserRoleRegistrationService::createRoleSpecificRecord($user);

            // Preparar datos para actualización
            $updateData = [$field => $value];
            
            // Validar datos
            $validatedData = $this->validateProfileData(
                new Request($updateData), 
                $user->rol_id
            );
            
            // Actualizar campo
            $result = $this->updateRoleSpecificData($user, $validatedData);

            if ($result['success']) {
                $profileData = UserRoleRegistrationService::getUserProfileData($user->fresh());
                
                return response()->json([
                    'success' => true,
                    'message' => 'Campo actualizado exitosamente',
                    'data' => $profileData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar campo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar datos del perfil según el rol
     */
    private function validateProfileData(Request $request, int $rolId): array
    {
        $baseRules = [
            'telefono' => 'sometimes|nullable|string|max:20',
            'direccion' => 'sometimes|nullable|string|max:255',
            'fecha_nacimiento' => 'sometimes|nullable|date',
            'sexo' => 'sometimes|nullable|in:Masculino,Femenino,Otro',
            'curp' => 'sometimes|nullable|string|max:18|unique:usuarios,curp,' . Auth::id(),
            'ocupacion' => 'sometimes|nullable|string|max:100',
        ];

        $roleSpecificRules = match ($rolId) {
            4 => [ // Paciente
                'contacto_emergencia_nombre' => 'sometimes|nullable|string|max:100',
                'contacto_emergencia_telefono' => 'sometimes|nullable|string|max:20',
                'contacto_emergencia_parentesco' => 'sometimes|nullable|string|max:50',
                'tutor_nombre' => 'sometimes|nullable|string|max:100',
                'tutor_telefono' => 'sometimes|nullable|string|max:20',
                'tutor_parentesco' => 'sometimes|nullable|string|max:50',
                'tutor_direccion' => 'sometimes|nullable|string|max:150',
            ],
            2 => [ // Terapeuta
                'cedula_profesional' => 'sometimes|string|max:30',
                'especialidad_principal' => 'sometimes|string|max:100',
                'experiencia_anios' => 'sometimes|integer|min:0|max:50',
                'estatus' => 'sometimes|in:activo,inactivo,suspendido',
            ],
            3 => [ // Recepcionista
                // Los recepcionistas no tienen campos específicos obligatorios por ahora
            ],
            1 => [ // Administrador
                'cedula_profesional' => 'sometimes|string|max:30',
                'clinica_id' => 'sometimes|exists:clinicas,id'
            ],
            default => []
        };

        $rules = array_merge($baseRules, $roleSpecificRules);
        return $request->validate($rules);
    }

    /**
     * Actualizar datos específicos del rol
     */
    private function updateRoleSpecificData($user, array $validatedData): array
    {
        try {
            switch ($user->rol_id) {
                case 4: // Paciente
                    if ($user->paciente) {
                        $user->paciente->update($validatedData);
                    } else {
                        // Crear registro de paciente si no existe
                        \App\Models\Paciente::create(array_merge([
                            'id' => $user->id
                        ], $validatedData));
                    }
                    break;
                    
                case 2: // Terapeuta
                    if ($user->terapeuta) {
                        $user->terapeuta->update($validatedData);
                    } else {
                        // Crear registro de terapeuta si no existe
                        \App\Models\Terapeuta::create(array_merge([
                            'id' => $user->id,
                            'estatus' => 'activo'
                        ], $validatedData));
                    }
                    break;
                    
                case 3: // Recepcionista
                    if ($user->recepcionista) {
                        $user->recepcionista->update($validatedData);
                    } else {
                        // Crear registro de recepcionista si no existe
                        \App\Models\Recepcionista::create(array_merge([
                            'id' => $user->id
                        ], $validatedData));
                    }
                    break;
                    
                case 1: // Administrador
                    if ($user->administrador) {
                        $user->administrador->update($validatedData);
                    } else {
                        // Crear registro de administrador si no existe
                        \App\Models\Administrador::create(array_merge([
                            'id' => $user->id
                        ], $validatedData));
                    }
                    break;
            }
            
            return ['success' => true, 'message' => 'Datos actualizados correctamente'];
            
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }
}