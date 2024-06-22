<?php

namespace Modules\FcmCommun\Commands;

use Illuminate\Console\Command;

use Modules\Transformation\Entities\Fonction;
use Modules\FcmCommun\DataObjects\FonctionDto;

class TestParcoursArchitecture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skeletor:test-parcours-architecture';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests new parcours architecture.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $f=Fonction::with('stages')->with('compagnonages.taches.objectifs.sous_objectifs')->find(1);

        $dto = FonctionDto::from($f);
        //dd($dto);

        $arr = $dto->toArray();

        $dtoback = FonctionDto::from($arr);
        dd($dtoback);

    }
}
