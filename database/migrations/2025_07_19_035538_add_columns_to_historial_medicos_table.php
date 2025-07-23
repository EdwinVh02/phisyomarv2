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
            // Agregar columnas faltantes para el historial médico completo
            $table->text('alergias')->nullable()->after('observacion_general');
            $table->text('medicamentos_actuales')->nullable()->after('alergias');
            $table->text('antecedentes_familiares')->nullable()->after('medicamentos_actuales');
            $table->text('cirugias_previas')->nullable()->after('antecedentes_familiares');
            $table->text('lesiones_previas')->nullable()->after('cirugias_previas');
            
            // Agregar timestamps si no existen
            if (!Schema::hasColumn('historial_medicos', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historial_medicos', function (Blueprint $table) {
            // Eliminar las columnas agregadas
            $table->dropColumn([
                'alergias',
                'medicamentos_actuales', 
                'antecedentes_familiares',
                'cirugias_previas',
                'lesiones_previas'
            ]);
        });
    }
};
