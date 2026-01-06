<?php
namespace Modules\FcmCommun\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ParcoursMarinBackupService
{
    public static function backup($marinUuid, $parcoursJson, $version, $module, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d_His');
        $annee = now()->format('Y');
        $mois = now()->format('m');
       
        // Chemin absolu vers le module
        $baseDir = base_path("Modules/{$module}/storage/backups/parcours_marin");
        $subDir = "{$annee}/{$mois}/{$marinUuid}";
        $fullDir = "{$baseDir}/{$subDir}";
        $filename = "version_{$version}_{$date}_{$marinUuid}.json";
        $fullPath = "{$fullDir}/{$filename}";

        Log::info('💾 DEBUT BACKUP', [
            'marin_uuid' => $marinUuid,
            'version' => $version,
            'full_path' => $fullPath,
            'base_exists' => file_exists($baseDir),
        ]);

        // Décoder si c'est déjà une chaîne JSON
        if (is_string($parcoursJson)) {
            $decoded = json_decode($parcoursJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $parcoursJson = $decoded;
            }
        }

        // Créer tous les dossiers nécessaires
        if (!File::isDirectory($fullDir)) {
            Log::info('📁 CREATION DOSSIERS', ['dir' => $fullDir]);
            File::makeDirectory($fullDir, 0755, true, true);
        }

        // Encoder en JSON
        $jsonContent = json_encode($parcoursJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        // Sauvegarder le fichier
        $bytes = File::put($fullPath, $jsonContent);

        if ($bytes !== false) {
            Log::info('✅ BACKUP REUSSI', [
                'path' => $fullPath,
                'size_bytes' => $bytes,
                'file_exists' => file_exists($fullPath),
            ]);
        } else {
            Log::error('❌ BACKUP ECHOUE', [
                'path' => $fullPath,
                'dir_exists' => file_exists($fullDir),
                'dir_writable' => is_writable($fullDir),
            ]);
        }

        return $fullPath;
    }
}
