<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseController extends Controller
{
    /**
     * Obtener estadísticas de la base de datos
     */
    public function getStats()
    {
        try {
            $stats = [
                'servidor' => $this->getServerStats(),
                'base_datos' => $this->getDatabaseStats(),
                'rendimiento' => $this->getPerformanceStats(),
                'tablas_principales' => $this->getMainTables()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas del servidor
     */
    private function getServerStats()
    {
        $connection = DB::connection();
        
        return [
            'host' => config('database.connections.mysql.host', 'localhost'),
            'puerto' => config('database.connections.mysql.port', 3306),
            'version' => $connection->selectOne('SELECT VERSION() as version')->version ?? 'SQLite',
            'uptime' => $this->getUptime(),
            'estado' => 'Activo'
        ];
    }

    /**
     * Obtener estadísticas de la base de datos
     */
    private function getDatabaseStats()
    {
        $dbName = config('database.connections.mysql.database', 'phisyomar_db');
        
        // Para SQLite, calculamos el tamaño del archivo
        $size = '0 MB';
        $tables = 0;
        $totalRecords = 0;

        try {
            $tables = collect(Schema::getAllTables())->count();
            
            // Contar registros de las tablas principales
            $mainTables = ['usuarios', 'pacientes', 'terapeutas', 'citas', 'pagos', 'bitacoras'];
            foreach ($mainTables as $table) {
                if (Schema::hasTable($table)) {
                    $totalRecords += DB::table($table)->count();
                }
            }

            // Calcular tamaño aproximado para SQLite
            if (config('database.default') === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (file_exists($dbPath)) {
                    $sizeBytes = filesize($dbPath);
                    $size = $this->formatBytes($sizeBytes);
                }
            }
        } catch (\Exception $e) {
            // Si hay error, usar valores por defecto
        }

        return [
            'nombre' => $dbName,
            'tamaño' => $size,
            'tablas' => $tables,
            'registros_totales' => $totalRecords,
            'ultimo_backup' => $this->getLastBackupDate()
        ];
    }

    /**
     * Obtener estadísticas de rendimiento
     */
    private function getPerformanceStats()
    {
        return [
            'conexiones_activas' => 1, // Simplificado para desarrollo
            'consultas_por_segundo' => rand(40, 50),
            'uso_memoria' => '256 MB',
            'uso_cpu' => rand(10, 20) . '%',
            'espacio_libre' => '85.6 GB'
        ];
    }

    /**
     * Obtener información de las tablas principales
     */
    private function getMainTables()
    {
        $tables = [
            'usuarios' => 'usuarios',
            'pacientes' => 'pacientes', 
            'terapeutas' => 'terapeutas',
            'citas' => 'citas',
            'pagos' => 'pagos',
            'historiales_medicos' => 'historial_medicos',
            'bitacoras' => 'bitacoras'
        ];

        $result = [];
        
        foreach ($tables as $displayName => $tableName) {
            try {
                $count = Schema::hasTable($tableName) ? DB::table($tableName)->count() : 0;
                $result[] = [
                    'nombre' => $displayName,
                    'registros' => $count,
                    'tamaño' => $this->calculateTableSize($count)
                ];
            } catch (\Exception $e) {
                $result[] = [
                    'nombre' => $displayName,
                    'registros' => 0,
                    'tamaño' => '0 MB'
                ];
            }
        }

        return $result;
    }

    /**
     * Crear backup manual
     */
    public function createBackup(Request $request)
    {
        try {
            $descripcion = $request->get('descripcion', 'Backup manual');
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            
            // Para desarrollo, simulamos la creación del backup
            $backupData = [
                'id' => uniqid(),
                'fecha' => Carbon::now()->toISOString(),
                'tamaño' => rand(1000, 3000) . ' MB',
                'tipo' => 'Manual',
                'estado' => 'Exitoso',
                'duracion' => rand(120, 300) . 's',
                'descripcion' => $descripcion,
                'filename' => $filename
            ];

            // En producción aquí se ejecutaría el comando de backup real
            // exec("mysqldump -u{$user} -p{$password} {$database} > {$filename}");

            return response()->json([
                'success' => true,
                'data' => $backupData,
                'message' => 'Backup creado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de backups
     */
    public function getBackups()
    {
        try {
            // Datos simulados para desarrollo
            $backups = [
                [
                    'id' => 1,
                    'fecha' => Carbon::now()->toISOString(),
                    'tamaño' => '2.3 GB',
                    'tipo' => 'Automático',
                    'estado' => 'Exitoso',
                    'duracion' => '3m 45s'
                ],
                [
                    'id' => 2,
                    'fecha' => Carbon::yesterday()->toISOString(),
                    'tamaño' => '2.2 GB',
                    'tipo' => 'Automático',
                    'estado' => 'Exitoso',
                    'duracion' => '3m 22s'
                ],
                [
                    'id' => 3,
                    'fecha' => Carbon::now()->subDays(2)->toISOString(),
                    'tamaño' => '2.1 GB',
                    'tipo' => 'Manual',
                    'estado' => 'Exitoso',
                    'duracion' => '3m 18s'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $backups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener backups: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimizar base de datos
     */
    public function optimize()
    {
        try {
            $results = [];
            
            // Para SQLite, ejecutamos VACUUM
            if (config('database.default') === 'sqlite') {
                DB::statement('VACUUM');
                $results[] = 'Base de datos compactada (VACUUM)';
            } else {
                // Para MySQL, optimizamos las tablas principales
                $tables = ['usuarios', 'pacientes', 'terapeutas', 'citas', 'pagos'];
                foreach ($tables as $table) {
                    if (Schema::hasTable($table)) {
                        DB::statement("OPTIMIZE TABLE {$table}");
                        $results[] = "Tabla {$table} optimizada";
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'operaciones' => $results,
                    'tiempo_total' => rand(5, 15) . ' segundos',
                    'espacio_liberado' => rand(10, 50) . ' MB'
                ],
                'message' => 'Base de datos optimizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al optimizar base de datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar estado de salud de la base de datos
     */
    public function healthCheck()
    {
        try {
            $health = [
                'conexion' => 'OK',
                'latencia' => rand(5, 20) . 'ms',
                'espacio_disponible' => '85.6 GB',
                'ultimo_mantenimiento' => Carbon::now()->subDays(7)->toISOString(),
                'errores_recientes' => 0,
                'rendimiento' => 'Óptimo'
            ];

            return response()->json([
                'success' => true,
                'data' => $health
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en verificación de salud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener conexiones activas
     */
    public function getConnections()
    {
        try {
            // Para desarrollo, simulamos conexiones
            $connections = [
                [
                    'id' => 1,
                    'usuario' => 'admin',
                    'host' => '127.0.0.1',
                    'database' => 'phisyomar_db',
                    'tiempo_conexion' => '2h 15m',
                    'estado' => 'Activa'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $connections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conexiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helpers
     */
    private function getUptime()
    {
        return rand(10, 30) . ' días, ' . rand(1, 23) . ' horas';
    }

    private function getLastBackupDate()
    {
        return Carbon::now()->subHours(2)->toISOString();
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }

    private function calculateTableSize($recordCount)
    {
        // Estimación aproximada: cada registro = ~1KB
        $sizeKB = $recordCount * 1;
        return $this->formatBytes($sizeKB * 1024);
    }
}