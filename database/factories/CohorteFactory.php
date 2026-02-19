<?php

namespace Modules\FcmCommun\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use Modules\FcmCommun\Models\Cohorte;

/**
 * @extends Factory<\App\Models\TransformationHistory>
 */
class CohorteFactory extends Factory
{
    protected $model = Cohorte::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     // Compteur
     private static $counter = 1;
     // Libelle
     private static $libelle = "Cohorte";


    public function definition()
    {
        //$libelle = fake()->text();
        $libcourt   =  strtoupper(Str::limit(self::$libelle, 3,' ')).' '.self::$counter;
        $liblong    =  self::$libelle.' '.self::$counter++.' : n°'.$this->faker->numberBetween(1,11).',  '.$this->faker->sentence(6);

        return [
            'uuid'          =>  $this->faker->uuid,
            "libelle_court" => $libcourt,
            "libelle_long"  => $liblong,
       
        ];
    }
}