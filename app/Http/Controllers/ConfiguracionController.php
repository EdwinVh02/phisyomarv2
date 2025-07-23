<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    private $configPath = 'config/sistema.json';
    
    /**
     * Obtener toda la configuración
     */
    public function index()
    {
        try {
            $config = $this->getConfig();
            
            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener configuración por categoría
     */
    public function getByCategory($category)
    {
        try {
            $config = $this->getConfig();
            
            if (!isset($config[$category])) {
                return response()->json([
                    'success' => false,
                    'message' => "Categoría '{$category}' no encontrada"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $config[$category]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar toda la configuración
     */
    public function update(Request $request)
    {
        try {
            $config = $request->all();
            $this->validateConfig($config);
            $this->saveConfig($config);

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Configuración actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar configuración por categoría
     */
    public function updateByCategory(Request $request, $category)
    {
        try {
            $config = $this->getConfig();
            $config[$category] = $request->all();
            
            $this->validateConfig($config);
            $this->saveConfig($config);

            return response()->json([
                'success' => true,
                'data' => $config[$category],
                'message' => "Configuración de {$category} actualizada exitosamente"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetear configuración a valores por defecto
     */
    public function reset($category = null)
    {
        try {
            $defaultConfig = $this->getDefaultConfig();
            
            if ($category) {
                $config = $this->getConfig();
                $config[$category] = $defaultConfig[$category];
                $this->saveConfig($config);
                
                return response()->json([
                    'success' => true,
                    'data' => $config[$category],
                    'message' => "Configuración de {$category} restablecida a valores por defecto"
                ]);
            } else {
                $this->saveConfig($defaultConfig);
                
                return response()->json([
                    'success' => true,
                    'data' => $defaultConfig,
                    'message' => 'Toda la configuración restablecida a valores por defecto'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar configuración
     */
    public function export()
    {
        try {
            $config = $this->getConfig();
            $filename = 'configuracion_' . date('Y-m-d_H-i-s') . '.json';
            
            return response()->json($config)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importar configuración
     */
    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'config_file' => 'required|file|mimes:json'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo inválido. Debe ser un archivo JSON.'
                ], 400);
            }

            $file = $request->file('config_file');
            $content = file_get_contents($file->getPathname());
            $config = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo JSON inválido'
                ], 400);
            }

            $this->validateConfig($config);
            $this->saveConfig($config);

            return response()->json([
                'success' => true,
                'data' => $config,
                'message' => 'Configuración importada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar configuración
     */
    public function validate(Request $request)
    {
        try {
            $config = $request->all();
            $errors = [];

            // Validar configuración general
            if (isset($config['general'])) {
                if (empty($config['general']['nombre_clinica'])) {
                    $errors[] = 'El nombre de la clínica es requerido';
                }
                if (isset($config['general']['email']) && !filter_var($config['general']['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'El email no es válido';
                }
            }

            // Validar configuración de seguridad
            if (isset($config['seguridad'])) {
                if (isset($config['seguridad']['sesion_timeout']) && $config['seguridad']['sesion_timeout'] < 5) {
                    $errors[] = 'El tiempo de sesión debe ser al menos 5 minutos';
                }
                if (isset($config['seguridad']['intentos_login']) && $config['seguridad']['intentos_login'] < 1) {
                    $errors[] = 'Los intentos de login deben ser al menos 1';
                }
            }

            return response()->json([
                'success' => count($errors) === 0,
                'errors' => $errors,
                'message' => count($errors) === 0 ? 'Configuración válida' : 'Errores en la configuración'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios
     */
    public function getHistory()
    {
        try {
            // Simulamos un historial para desarrollo
            $history = [
                [
                    'fecha' => now()->subHours(2)->toISOString(),
                    'usuario' => 'admin@phisyomar.com',
                    'categoria' => 'general',
                    'cambios' => 'Actualización de información de contacto',
                    'ip' => '192.168.1.100'
                ],
                [
                    'fecha' => now()->subDays(1)->toISOString(),
                    'usuario' => 'admin@phisyomar.com',
                    'categoria' => 'seguridad',
                    'cambios' => 'Modificación de políticas de contraseña',
                    'ip' => '192.168.1.100'
                ],
                [
                    'fecha' => now()->subDays(3)->toISOString(),
                    'usuario' => 'admin@phisyomar.com',
                    'categoria' => 'notificaciones',
                    'cambios' => 'Configuración de notificaciones por email',
                    'ip' => '192.168.1.100'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener configuración desde archivo o crear por defecto
     */
    private function getConfig()
    {
        if (Storage::exists($this->configPath)) {
            $content = Storage::get($this->configPath);
            return json_decode($content, true) ?? $this->getDefaultConfig();
        }
        
        return $this->getDefaultConfig();
    }

    /**
     * Guardar configuración
     */
    private function saveConfig($config)
    {
        Storage::put($this->configPath, json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Obtener configuración por defecto
     */
    private function getDefaultConfig()
    {
        return [
            'general' => [
                'nombre_clinica' => 'PhisyoMar',
                'descripcion' => 'Sistema de gestión médica para fisioterapia',
                'direccion' => 'Av. Revolución 1234, CDMX',
                'telefono' => '555-0100',
                'email' => 'contacto@phisyomar.com',
                'timezone' => 'America/Mexico_City',
                'idioma' => 'es'
            ],
            'notificaciones' => [
                'email_citas' => true,
                'sms_recordatorios' => true,
                'notif_pagos' => true,
                'notif_vencimientos' => true,
                'email_reportes' => false,
                'frecuencia_reportes' => 'semanal'
            ],
            'seguridad' => [
                'sesion_timeout' => 30,
                'intentos_login' => 3,
                'bloqueo_tiempo' => 15,
                'requerir_2fa' => false,
                'complejidad_password' => 'media',
                'cambio_password_dias' => 90
            ],
            'sistema' => [
                'backup_automatico' => true,
                'frecuencia_backup' => 'diario',
                'hora_backup' => '02:00',
                'retener_backups' => 30,
                'mantenimiento_auto' => true,
                'logs_nivel' => 'info'
            ]
        ];
    }

    /**
     * Validar estructura de configuración
     */
    private function validateConfig($config)
    {
        $requiredSections = ['general', 'notificaciones', 'seguridad', 'sistema'];
        
        foreach ($requiredSections as $section) {
            if (!isset($config[$section])) {
                throw new \Exception("Sección '{$section}' es requerida en la configuración");
            }
        }
    }
}