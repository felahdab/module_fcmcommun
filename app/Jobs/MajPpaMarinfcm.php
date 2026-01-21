<?php

namespace Modules\FcmCommun\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\FcmCommun\Services\MigrationParcoursMarinService;

class MajPpaMarinfcm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $parcoursserialise_uuid;

    /**
     * Create a new job instance.
     */
    public function __construct($parcoursserialise_uuid)
    {
        $this->parcoursserialise_uuid = $parcoursserialise_uuid;
    }

    /**
     * Execute the job.
     */
    public function handle(MigrationParcoursMarinService $service): void
    {
        $service->migrerVersNouvelleVersion($this->parcoursserialise_uuid);
    }
}
