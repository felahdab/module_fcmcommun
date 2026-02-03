<?php

namespace Modules\FcmCommun\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Modules\FcmCentral\Models\Marin;
use Illuminate\Support\Facades\View;

class LivretPdfService
{
    private $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('debugKeepTemp', false);
        
        $this->dompdf = new Dompdf($options);
    }

    public function generatePdf(Marin $marin): string
    {
        $data = $this->prepareMarinData($marin);
        $html = View::make('fcmcommun::pdf.livret-fcm', $data)->render();
        
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        
        return $this->dompdf->output();
    }

    private function prepareMarinData(Marin $marin): array
    {
        $complementsFcm = $marin->complements_fcm;
        $donneesParcours = $complementsFcm->donnees_du_parcours ?? null;
        $parcoursMarin = $donneesParcours->parcoursmarin ?? [];

        // Calculer les statistiques détaillées
        $stats = $this->calculerStatistiques($parcoursMarin);

        return [
            'marin' => $marin,
            'grade' => $marin->grade->libelle_court ?? 'N/A',
            'nom_complet' => $marin->nom . ' ' . $marin->prenom,
            'specialite' => $marin->specialite->libelle_court ?? 'N/A',
            'unite' => $marin->unite->libelle_long ?? 'N/A',
            'cohorte' => $complementsFcm->cohorte->libelle_long ?? 'Non assigné',
            'mentor' => $complementsFcm->mentor ? $complementsFcm->mentor->nom . ' ' . $complementsFcm->mentor->prenom : 'Non assigné',
            'parcours' => $donneesParcours->parcours_serialise->libelle_court ?? 'Non assigné',
            'taux_global' => $donneesParcours->taux_global ?? 0,
            'taux_stage' => $donneesParcours->taux_stage ?? 0,
            'taux_activite' => $donneesParcours->taux_activite ?? 0,
            'parcours_data' => $parcoursMarin,
            'stats' => $stats,
            'date_generation' => now()->format('d/m/Y à H:i'),
            'jalons' => [
                'sedimentation' => $complementsFcm->date_sedimentation ? \Carbon\Carbon::parse($complementsFcm->date_sedimentation)->format('d/m/Y') : 'Non renseigné',
                'fcm' => $complementsFcm->date_fcm ? \Carbon\Carbon::parse($complementsFcm->date_fcm)->format('d/m/Y') : 'Non renseigné',
                'competence' => $complementsFcm->date_competence ? \Carbon\Carbon::parse($complementsFcm->date_competence)->format('d/m/Y') : 'Non renseigné',
            ],
        ];
    }

    private function calculerStatistiques($parcours_data): array
    {
        $totalSF = 0;
        $sfValides = 0;
        $totalObj = 0;
        $objValides = 0;
        $totalStages = 0;
        $stagesValides = 0;

        if (isset($parcours_data['affectations'])) {
            foreach ($parcours_data['affectations'] as $affectation) {
                foreach ($affectation['competences'] ?? [] as $competence) {
                    foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                        $totalSF++;
                        if (($csf['etat_valid'] ?? false) === true) {
                            $sfValides++;
                        }
                        
                        // Détecter les stages
                        if (($csf['savoirfaire_type']['code'] ?? null) === 'S') {
                            $totalStages++;
                            if (($csf['etat_valid'] ?? false) === true) {
                                $stagesValides++;
                            }
                        }
                        
                        foreach ($csf['objectifs'] ?? [] as $objectif) {
                            $totalObj++;
                            if (($objectif['etat_valid'] ?? false) === true) {
                                $objValides++;
                            }
                        }
                    }
                }
            }
        }

        return [
            'total_sf' => $totalSF,
            'sf_valides' => $sfValides,
            'total_obj' => $totalObj,
            'obj_valides' => $objValides,
            'total_stages' => $totalStages,
            'stages_valides' => $stagesValides,
        ];
    }

    public function getParcoursDetaille($parcours_data): array
    {
        $detailles = [];

        if (!isset($parcours_data['affectations'])) {
            return $detailles;
        }

        foreach ($parcours_data['affectations'] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $detailles[] = [
                        'affectation' => $affectation['libelle_court'] ?? 'N/A',
                        'competence' => $competence['libelle_court'] ?? 'N/A',
                        'savoirfaire' => $csf['libelle_court'] ?? 'N/A',
                        'savoirfaireType' => $csf['savoirfaire']['libelle_court'] ?? 'N/A',
                        'type_sf' => $csf['savoirfaire_type']['code'] ?? 'A',
                        'sf_valide' => ($csf['etat_valid'] ?? false) === true,
                        'objectifs' => array_map(function($obj) {
                            return [
                                'libelle' => $obj['libelle_court'] ?? 'N/A',
                                'valide' => ($obj['etat_valid'] ?? false) === true,
                                'date_validation' => $obj['date_validation'] ?? null,
                                'commentaire' => $obj['commentaire'] ?? '',
                            ];
                        }, $csf['objectifs'] ?? []),
                    ];
                }
            }
        }

        return $detailles;
    }
}
