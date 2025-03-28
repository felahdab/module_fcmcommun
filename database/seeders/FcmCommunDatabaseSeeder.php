<?php

namespace Modules\FcmCommun\Database\Seeders;

use Illuminate\Database\Seeder;

class FcmCommunDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $this->call([
            CohorteSeeder::class,
         ]);
    }
}
