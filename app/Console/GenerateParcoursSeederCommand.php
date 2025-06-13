<?php


namespace Modules\FcmCommun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateParcoursSeederCommand extends Command
{
    protected $signature = 'julien:parcours-seeder 
                            {parcours-id : ID du parcours à transformer}
                            {--name= : Nom du seeder (optionnel)}';

    protected $description = '[FcmCommun] Génère des seeders à partir d\'un parcours existant dans le module FcmCentral';

    protected $seederPath;
    protected $parcoursId;
    protected $parcoursName;
 
    public function handle()
    {
        $this->parcoursId = $this->argument('parcours-id');
        $this->seederPath = base_path('/Modules/Fcmcentral/database/seeders/Parcours');
        
        // Créer le dossier si nécessaire
        if (!File::exists($this->seederPath)) {
            File::makeDirectory($this->seederPath, 0755, true);
        }
 
        $this->info("🔄 Génération des seeders pour le parcours {$this->parcoursId}");
 
        try {
            // Récupérer le parcours
            $parcours = DB::table('fcmcentral_parcours')
                ->where('id', $this->parcoursId)
                ->first();
 
            if (!$parcours) {
                $this->error("❌ Parcours {$this->parcoursId} introuvable");
                return 1;
            }
 
            $this->parcoursName = $this->option('name') ?? Str::studly(Str::slug($parcours->libelle_long, '_'));
            $this->info("📋 Parcours: {$parcours->libelle_long}");
 
            // Analyser la structure complète
            $structure = $this->analyzeParcoursStructure($this->parcoursId);
            $this->displayStructure($structure);
 
            // Générer tous les seeders
            $this->generateAllSeeders($structure);
 
            // Générer le seeder principal
            $this->generateMasterSeeder();
 
            $this->info('✅ Seeders générés avec succès !');
            $this->info("📁 Dossier: {$this->seederPath}");
            $this->info("🚀 Pour utiliser: php artisan db:seed --class=\"Modules\\FcmCentral\\Database\\Seeders\\Parcours\\{$this->parcoursName}MasterSeeder\"");
 
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
 
        return 0;
    }
 
    protected function analyzeParcoursStructure($parcoursId)
    {
        $this->info("🔍 Analyse de la structure du parcours...");
 
        // 1. Parcours
        $parcours = DB::table('fcmcentral_parcours')
            ->where('id', $parcoursId)
            ->first();
 
        // 2. Fonctions du parcours
        $fonctions = DB::table('fcmcentral_fonctions as f')
            ->join('fcmcentral_fonction_parcours as fp', 'f.id', '=', 'fp.fonction_id')
            ->where('fp.parcours_id', $parcoursId)
            ->select('f.*')
            ->get();
 
        $fonctionIds = $fonctions->pluck('id')->toArray();
 
        // 3. Compétences des fonctions
        $competences = collect();
        if (!empty($fonctionIds)) {
            $competences = DB::table('fcmcentral_competences as c')
                ->join('fcmcentral_competence_fonction as cf', 'c.id', '=', 'cf.competence_id')
                ->whereIn('cf.fonction_id', $fonctionIds)
                ->select('c.*')
                ->distinct()
                ->get();
        }
 
        $competenceIds = $competences->pluck('id')->toArray();
 
        // 4. Savoir-faires des compétences
        $savoirfaires = collect();
        if (!empty($competenceIds)) {
            $savoirfaires = DB::table('fcmcentral_savoirfaires as s')
                ->join('fcmcentral_competence_savoirfaire as cs', 's.id', '=', 'cs.savoirfaire_id')
                ->whereIn('cs.competence_id', $competenceIds)
                ->select('s.*')
                ->distinct()
                ->get();
        }
 
        $savoirfaireIds = $savoirfaires->pluck('id')->toArray();
 
        // 5. Activités des savoir-faires
        $activites = collect();
        if (!empty($savoirfaireIds)) {
            $activites = DB::table('fcmcentral_activites as a')
                ->join('fcmcentral_activite_savoirfaire as asf', 'a.id', '=', 'asf.activite_id')
                ->whereIn('asf.savoirfaire_id', $savoirfaireIds)
                ->select('a.*')
                ->distinct()
                ->get();
        }
 
        // 6. Relations (tables pivot)
        $relations = [
            'fonction_parcours' => DB::table('fcmcentral_fonction_parcours')
                ->where('parcours_id', $parcoursId)
                ->get(),
            'competence_fonction' => empty($fonctionIds) ? collect() : DB::table('fcmcentral_competence_fonction')
                ->whereIn('fonction_id', $fonctionIds)
                ->get(),
            'competence_savoirfaire' => empty($competenceIds) ? collect() : DB::table('fcmcentral_competence_savoirfaire')
                ->whereIn('competence_id', $competenceIds)
                ->get(),
            'activite_savoirfaire' => empty($savoirfaireIds) ? collect() : DB::table('fcmcentral_activite_savoirfaire')
                ->whereIn('savoirfaire_id', $savoirfaireIds)
                ->get(),
        ];
 
        return [
            'parcours' => $parcours,
            'fonctions' => $fonctions,
            'competences' => $competences,
            'savoirfaires' => $savoirfaires,
            'activites' => $activites,
            'relations' => $relations,
        ];
    }
 
    protected function displayStructure($structure)
    {
        $this->info("📊 Structure trouvée :");
        $this->info("  📋 Parcours: 1");
        $this->info("  📁 Fonctions: " . $structure['fonctions']->count());
        $this->info("  🎯 Compétences: " . $structure['competences']->count());
        $this->info("  📚 Savoir-faires: " . $structure['savoirfaires']->count());
        $this->info("  ⚡ Activités: " . $structure['activites']->count());
        $this->info("  🔗 Relations fonction-parcours: " . $structure['relations']['fonction_parcours']->count());
        $this->info("  🔗 Relations compétence-fonction: " . $structure['relations']['competence_fonction']->count());
        $this->info("  🔗 Relations compétence-savoirfaire: " . $structure['relations']['competence_savoirfaire']->count());
        $this->info("  🔗 Relations activité-savoirfaire: " . $structure['relations']['activite_savoirfaire']->count());
    }
 
    protected function generateAllSeeders($structure)
    {
        // 1. Parcours principal
        $this->createSeeder('ParcoursSeeder', 'fcmcentral_parcours', [
            (array) $structure['parcours']
        ]);
 
        // 2. Fonctions
        if ($structure['fonctions']->isNotEmpty()) {
            $this->createSeeder('FonctionsSeeder', 'fcmcentral_fonctions', 
                $structure['fonctions']->map(fn($f) => (array) $f)->toArray()
            );
        }
 
        // 3. Compétences
        if ($structure['competences']->isNotEmpty()) {
            $this->createSeeder('CompetencesSeeder', 'fcmcentral_competences', 
                $structure['competences']->map(fn($c) => (array) $c)->toArray()
            );
        }
 
        // 4. Savoir-faires
        if ($structure['savoirfaires']->isNotEmpty()) {
            $this->createSeeder('SavoirfairesSeeder', 'fcmcentral_savoirfaires', 
                $structure['savoirfaires']->map(fn($s) => (array) $s)->toArray()
            );
        }
 
        // 5. Activités
        if ($structure['activites']->isNotEmpty()) {
            $this->createSeeder('ActivitesSeeder', 'fcmcentral_activites', 
                $structure['activites']->map(fn($a) => (array) $a)->toArray()
            );
        }
 
        // 6. Relations
        $this->createRelationSeeders($structure['relations']);
    }
 
    protected function createRelationSeeders($relations)
    {
        // Fonction-Parcours
        if ($relations['fonction_parcours']->isNotEmpty()) {
            $this->createSeeder('FonctionParcoursSeeder', 'fcmcentral_fonction_parcours', 
                $relations['fonction_parcours']->map(fn($r) => (array) $r)->toArray()
            );
        }
 
        // Compétence-Fonction
        if ($relations['competence_fonction']->isNotEmpty()) {
            $this->createSeeder('CompetenceFonctionSeeder', 'fcmcentral_competence_fonction', 
                $relations['competence_fonction']->map(fn($r) => (array) $r)->toArray()
            );
        }
 
        // Compétence-Savoirfaire
        if ($relations['competence_savoirfaire']->isNotEmpty()) {
            $this->createSeeder('CompetenceSavoirfaireSeeder', 'fcmcentral_competence_savoirfaire', 
                $relations['competence_savoirfaire']->map(fn($r) => (array) $r)->toArray()
            );
        }
 
        // Activité-Savoirfaire
        if ($relations['activite_savoirfaire']->isNotEmpty()) {
            $this->createSeeder('ActiviteSavoirfaireSeeder', 'fcmcentral_activite_savoirfaire', 
                $relations['activite_savoirfaire']->map(fn($r) => (array) $r)->toArray()
            );
        }
    }
 
    protected function createSeeder($seederName, $tableName, $data)
    {
        if (empty($data)) {
            $this->warn("⚠️  Aucune donnée pour $seederName");
            return;
        }
 
        $className = $this->parcoursName . $seederName;
        $fileName = $className . '.php';
        $filePath = $this->seederPath . '/' . $fileName;
 
        $content = $this->buildSeederContent($className, $tableName, $data);
        File::put($filePath, $content);
 
        $this->info("✅ $fileName (" . count($data) . " enregistrements)");
    }
 
    protected function buildSeederContent($className, $tableName, $data)
    {
        $namespace = 'Modules\\FcmCentral\\Database\\Seeders\\Parcours';
        $dataString = var_export($data, true);
 
        return <<<PHP
<?php
 
namespace {$namespace};
 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
 
class {$className} extends Seeder
{
    /**
     * Seeder généré automatiquement pour {$tableName}
     * Parcours ID: {$this->parcoursId}
     * Généré le: {\Carbon\Carbon::now()->format('Y-m-d H:i:s')}
     */
    public function run()
    {
        // Désactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        \$data = {$dataString};
        
        // Utiliser insertOrIgnore pour éviter les doublons
        collect(\$data)->chunk(50)->each(function (\$chunk) {
            DB::table('{$tableName}')->insertOrIgnore(\$chunk->toArray());
        });
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        \$this->command->info('✅ {$tableName}: ' . count(\$data) . ' enregistrements traités');
    }
}
PHP;
    }
 
    protected function generateMasterSeeder()
    {
        $className = $this->parcoursName . 'MasterSeeder';
        $fileName = $className . '.php';
        $filePath = $this->seederPath . '/' . $fileName;
 
        // Liste des seeders possibles dans l'ordre de dépendance
        $possibleSeeders = [
            'ParcoursSeeder',
            'FonctionsSeeder',
            'FonctionParcoursSeeder',
            'CompetencesSeeder',
            'CompetenceFonctionSeeder',
            'SavoirfairesSeeder',
            'CompetenceSavoirfaireSeeder',
            'ActivitesSeeder',
            'ActiviteSavoirfaireSeeder',
        ];
 
        // Ne garder que les seeders qui existent
        $existingSeeders = collect($possibleSeeders)
            ->filter(function($seederName) {
                $fullClassName = $this->parcoursName . $seederName;
                return File::exists($this->seederPath . '/' . $fullClassName . '.php');
            })
            ->map(function($seederName) {
                return $this->parcoursName . $seederName;
            });
 
        $seederCalls = $existingSeeders
            ->map(function($seeder) {
                return "            \\Modules\\FcmCentral\\Database\\Seeders\\Parcours\\{$seeder}::class,";
            })
            ->implode("\n");
 
        $content = <<<PHP
<?php
 
namespace Modules\FcmCentral\Database\Seeders\Parcours;
 
use Illuminate\Database\Seeder;
 
class {$className} extends Seeder
{
    /**
     * Seeder principal pour le parcours {$this->parcoursName}
     * Parcours ID: {$this->parcoursId}
     * Généré le: {\Carbon\Carbon::now()->format('Y-m-d H:i:s')}
     * 
     * Hiérarchie: Parcours → Fonctions → Compétences → Savoir-faires → Activités
     * Seeders générés: {$existingSeeders->count()}
     */
    public function run()
    {
        \$this->command->info('🚀 Import du parcours {$this->parcoursName}...');
        \$this->command->info('📋 Parcours ID: {$this->parcoursId}');
        
        \$this->call([
{$seederCalls}
        ]);
        
        \$this->command->info('✅ Parcours {$this->parcoursName} importé avec succès !');
    }
}
PHP;
 
        File::put($filePath, $content);
        $this->info("✅ Seeder principal: $fileName");
        $this->info("📋 Seeders inclus: " . $existingSeeders->implode(', '));
    }
}
