<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;

class FixPasswordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix double-hashed passwords';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ACTUALIZANDO CONTRASEÑAS ===');

        // Terapeutas
        $terapeutas = [
            'ana.hernandez@phisyomar.com',
            'carlos.rodriguez@phisyomar.com', 
            'maria.garcia@phisyomar.com',
            'jose.martinez@phisyomar.com',
            'lucia.ramirez@phisyomar.com'
        ];

        foreach($terapeutas as $email) {
            $user = Usuario::where('correo_electronico', $email)->first();
            if ($user) {
                $user->contraseña = 'terapeuta123';
                $user->save();
                $this->info("Actualizado: $email");
            }
        }

        // Recepcionista
        $recep = Usuario::where('correo_electronico', 'sandra.moreno@phisyomar.com')->first();
        if ($recep) {
            $recep->contraseña = 'recepcionista123';
            $recep->save();
            $this->info("Recepcionista actualizada");
        }

        $this->info('=== CONTRASEÑAS ACTUALIZADAS ===');
        
        return 0;
    }
}
