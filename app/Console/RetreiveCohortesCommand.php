<?php

namespace Modules\FcmCommun\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Models\Setting;

use Modules\FcmCommun\Http\Integrations\Connectors\CentralFcmServerConnector;
use Modules\FcmCommun\Http\Integrations\Requests\RetreiveCohorteListRequest;
use Modules\FcmCommun\Models\Cohorte;

class RetreiveCohortesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fcmcommun:update-cohortes';

    /**
     * The console command description.
     */
    protected $description = 'Récupère une liste de cohortes sur un serveur disposant du module FCM Central';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activated = Setting::forKey('fcmcommun')->get('retreive_cohortes_from_distant_fcmcentral_module');
        if (! $activated)
        {
            $this->info("Remote retreival of cohortes is not activated.");
            return;
        }
        
        $remoteurl = Setting::forKey('fcmcommun')->get('cohortes_url_of_remote_fcmcentral_instance');
        $token = Setting::forKey('fcmcommun')->get('cohorte_token_for_remote_fcmcentral_instance');
        $this->info("Retreiving cohortes from remote instance: " . $remoteurl);

        $connection = new CentralFcmServerConnector($remoteurl, $token);
        $request = new RetreiveCohorteListRequest();

        $response = $connection->send($request);

        if ($response->successful())
        {
            $json = $response->json();

            foreach($json as $cohorte_array)
            {
                $this->info($cohorte_array["uuid"]);
                Cohorte::firstOrCreate(
                    [
                        "uuid" => $cohorte_array["uuid"]
                    ],
                    [
                        ...$cohorte_array
                    ]);
            }

            $this->info("Récupération terminée avec succès.");
            return;
        }
        
        $this->fail("Le serveur RH distant a répondu avec une erreur.");
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            
        ];
    }
}
