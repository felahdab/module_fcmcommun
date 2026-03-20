<?php

namespace Modules\FcmCommun\Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

use Modules\FcmCommun\Models\Cohorte;

class CohorteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		

        // Creer une cohorte
        Cohorte::firstOrCreate([
            'uuid'      =>  (string) Str::uuid(),
            'libelle_court' => '22.1',
            'libelle_long' => 'BAT DEASM 22.1',
        ]);

        // Creer une cohorte
        Cohorte::firstOrCreate([
            'uuid'      =>  (string) Str::uuid(),
            'libelle_court' => '22.2',
            'libelle_long' => 'BAT DEASM 22.2',
        ]);

        // Creer une cohorte
        Cohorte::firstOrCreate([
            'uuid'      =>  (string) Str::uuid(),
            'libelle_court' => '23.1',
            'libelle_long' => 'BAT DEASM 23.1',
        ]);


         // Creer une cohorte
         Cohorte::firstOrCreate([
            'uuid'      =>  (string) Str::uuid(),
            'libelle_court' => '23.2',
            'libelle_long' => 'BAT DEASM 23.2',
        ]);

    }
}