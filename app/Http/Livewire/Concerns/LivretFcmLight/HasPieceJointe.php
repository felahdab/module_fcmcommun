<?php

namespace Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight;

use Carbon\Carbon;

trait HasPieceJointe
{
    public $pieceJointe = null;

    public function updatedPieceJointe(): void
    {
        $this->validate([
            'pieceJointe' => ['nullable', 'file', 'mimes:pdf', 'max:5120'], // 5 Mo, PDF uniquement
        ], [
            'pieceJointe.mimes' => 'La pièce jointe doit être un fichier PDF.',
            'pieceJointe.max' => 'La pièce jointe ne doit pas dépasser 5 Mo.',
        ]);
    }

    /**
     * Stocke la pièce jointe uploadée (diplôme, justificatif) lors d'une validation
     * et retourne son chemin de stockage, ou null si aucun fichier n'a été fourni.
     *
     * Classement : fcmcommun/livretfcm/pieces-jointes/{savoirfaire|objectif}/{annee}/{mois}
     * pour éviter d'accumuler des milliers de fichiers dans un seul dossier.
     */
    private function stockerPieceJointe(array $elements_a_valider = [], ?Carbon $date = null): ?string
    {
        if (!$this->pieceJointe) {
            return null;
        }

        $this->validate([
            'pieceJointe' => ['file', 'mimes:pdf', 'max:5120'], // 5 Mo, PDF uniquement
        ], [
            'pieceJointe.mimes' => 'La pièce jointe doit être un fichier PDF.',
            'pieceJointe.max' => 'La pièce jointe ne doit pas dépasser 5 Mo.',
        ]);

        $date = $date ?? Carbon::now();
        $categorie = array_key_exists('savoirfaire', $elements_a_valider) ? 'savoirfaire' : 'objectif';

        $directory = sprintf(
            'fcmcommun/livretfcm/pieces-jointes/%s/%s/%s',
            $categorie,
            $date->format('Y'),
            $date->format('m')
        );

        return $this->pieceJointe->store($directory, 's3-public');
    }
}
