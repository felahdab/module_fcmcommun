<?php

namespace Modules\FcmCommun\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RefreshDbSeeder extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'julien:refresh-db {module?}';

    /**
     * The console command description.
     */
    protected $description = '[FcmCommun] Remettre à plat les bases de données de développement et de test + Seeder.';

    /**
     * Liste des Modules a Seeder
     */
    protected $modules = ['RH', 'FcmCommun', 'FcmCentral'];

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
        $this->info('Remise à plat des bases de données de développement et de test...');

        // Remettre à plat la base de données de développement
        $this->info('Remise à plat de la base de données de développement...');
        $this->call('migrate:fresh');
        $this->call('db:seed');

        // Boucle Module Seeders
        foreach ($this->modules as $module){
            $this->info("Exécution des seeders pour le module : $module " );
            $this->call('module:seed', ['module'=> $module]);
        }


        // Remettre à plat la base de données de test
        $this->info('Remise à plat de la base de données de test...');
        $this->call('migrate:fresh', ['--database' => 'testmysql']);
        $this->call('db:seed', ['--database' => 'testmysql']);
        
        // Boucle Module Seeders
        foreach ($this->modules as $module){
            $this->info("Exécution des seeders pour le module (testing) : $module " );
            $this->call('module:seed', ['module'=> $module, '--database' => 'testmysql']);
        }
        
        // Si un module est spécifié en argument, exécuter les seeders de ce module
        // $module = $this->argument('module');
        // if ($module) {
        //     $this->call('module:seed', ['module' => $module]);
        //     $this->call('module:seed', ['module' => $module, '--env' => 'testing']);
        // }

        $this->info('SUCCESS : Bases de données de développement et de test remises à plat');
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::OPTIONAL, 'Le nom du Module à Seeder.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
