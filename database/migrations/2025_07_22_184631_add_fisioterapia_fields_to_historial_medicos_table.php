<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('historial_medicos', function (Blueprint $table) {
            // Motivo de consulta
            $table->text('motivo_consulta')->nullable()->after('observacion_general');
            
            // Evaluación física
            $table->text('inspeccion_general')->nullable()->after('lesiones_previas');
            $table->text('rango_movimiento')->nullable()->after('inspeccion_general');
            $table->text('fuerza_muscular')->nullable()->after('rango_movimiento');
            $table->text('pruebas_especiales')->nullable()->after('fuerza_muscular');
            $table->string('dolor_eva', 10)->nullable()->after('pruebas_especiales');
            
            // Diagnóstico fisioterapéutico
            $table->text('diagnostico_fisioterapeutico')->nullable()->after('dolor_eva');
            
            // Plan de tratamiento
            $table->text('frecuencia_sesiones')->nullable()->after('diagnostico_fisioterapeutico');
            $table->text('tecnicas_propuestas')->nullable()->after('frecuencia_sesiones');
            
            // Objetivos
            $table->text('objetivos_corto_plazo')->nullable()->after('tecnicas_propuestas');
            $table->text('objetivos_mediano_plazo')->nullable()->after('objetivos_corto_plazo');
            $table->text('objetivos_largo_plazo')->nullable()->after('objetivos_mediano_plazo');
            
            // Evolución y seguimiento
            $table->text('evolucion_notas_seguimiento')->nullable()->after('objetivos_largo_plazo');
            
            // Firmas
            $table->text('firma_fisioterapeuta')->nullable()->after('evolucion_notas_seguimiento');
            $table->text('firma_paciente')->nullable()->after('firma_fisioterapeuta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_medicos', function (Blueprint $table) {
            $table->dropColumn([
                'motivo_consulta',
                'inspeccion_general',
                'rango_movimiento',
                'fuerza_muscular',
                'pruebas_especiales',
                'dolor_eva',
                'diagnostico_fisioterapeutico',
                'frecuencia_sesiones',
                'tecnicas_propuestas',
                'objetivos_corto_plazo',
                'objetivos_mediano_plazo',
                'objetivos_largo_plazo',
                'evolucion_notas_seguimiento',
                'firma_fisioterapeuta',
                'firma_paciente'
            ]);
        });
    }
};
