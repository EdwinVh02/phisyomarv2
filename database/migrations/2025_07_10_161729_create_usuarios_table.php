<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('apellido_paterno', 50);
            $table->string('apellido_materno', 50);
            $table->string('correo_electronico', 100)->unique();
            $table->string('contraseña', 100);
            $table->string('telefono', 20);
            $table->string('direccion', 150)->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['Masculino', 'Femenino', 'Otro']);
            $table->string('curp', 18)->unique();
            $table->string('ocupacion', 50)->nullable();
            $table->enum('estatus', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->unsignedBigInteger('rol_id');
            $table->timestamps();
            $table->foreign('rol_id')->references('id')->on('rols');
            $table->index('telefono');
            $table->index('curp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
