<?php

namespace Modules\FcmCommun\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        Cohorte::create([
            'uuid'      =>  (string) \Illuminate\Support\Str::uuid(),
            'libelle_court' => '22.1',
            'libelle_long' => 'BAT DEASM 22.1',
        ]);

        // Creer une cohorte
        Cohorte::create([
            'uuid'      =>  (string) \Illuminate\Support\Str::uuid(),
            'libelle_court' => '32.1',
            'libelle_long' => 'BAT DEASM 32.1',
        ]);

        // Creer une cohorte
        Cohorte::create([
            'uuid'      =>  (string) \Illuminate\Support\Str::uuid(),
            'libelle_court' => '49.3',
            'libelle_long' => 'BAT MAN 2.1',
        ]);

    }
}