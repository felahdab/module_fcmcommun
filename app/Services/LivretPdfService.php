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
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('debugKeepTemp', true);
        
         $this->dompdf = new Dompdf($options);

       // $this->dompdf = new Dompdf();

      
    }
 
    public function generatePdf(Marin $marin): string
    {
        // Préparer les données du marin
        $data = $this->prepareMarinData($marin);
        
        // Générer le HTML à partir du template
        $html = View::make('fcmcommun::pdf.livret-fcm', $data)->render();
        // Configurer et générer le PDF
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        
        return $this->dompdf->output();
    }
 
    private function prepareMarinData(Marin $marin): array
    {
        $complementsFcm = $marin->complements_fcm;
        $donneesParcours = $complementsFcm->donnees_du_parcours ?? null;
        $parcoursMarin = $donneesParcours->parcoursmarin;
 
        return [
            'marin' => $marin,
            'grade' => $marin->grade->libelle_court ?? 'N/A',
            'nom_complet' => $marin->nom . ' ' . $marin->prenom,
            'specialite' => $marin->specialite->libelle_court ?? 'N/A',
            'unite' => $marin->unite->libelle_court ?? 'N/A',
            'cohorte' => $complementsFcm->cohorte->libelle_long ?? 'Non assigné',
            'mentor' => $complementsFcm->mentor ? $complementsFcm->mentor->nom . ' ' . $complementsFcm->mentor->prenom : 'Non assigné',
            'parcours' => $donneesParcours->parcours_serialise->libelle_court ?? 'Non assigné',
            'taux_global' => $donneesParcours->taux_global ?? 0,
            'taux_stage' => $donneesParcours->taux_stage ?? 0,
            'taux_activite' => $donneesParcours->taux_activite ?? 0,
            'parcours_data' => $parcoursMarin,
            'date_generation' => now()->format('d/m/Y H:i'),
        ];
    }
 
    public function getActivitesValidees($parcours_data): array
    {
        $activites = [];
        
        if (!isset($parcours_data['fonctions'])) {
            return $activites;
        }
 
        foreach ($parcours_data['fonctions'] as $fonction) {
            if (!isset($fonction['competences'])) continue;
            
            foreach ($fonction['competences'] as $competence) {
                if (!isset($competence['savoirfaires'])) continue;
                
                foreach ($competence['savoirfaires'] as $savoirfaire) {
                    if (!isset($savoirfaire['activites'])) continue;
                    
                    foreach ($savoirfaire['activites'] as $activite) {
                        $activites[] = [
                            'fonction' => $fonction['libelle_court'] ?? 'N/A',
                            'competence' => $competence['libelle_court'] ?? 'N/A',
                            'savoirfaire' => $savoirfaire['libelle_court'] ?? 'N/A',
                            'activite' => $activite['libelle_court'] ?? 'N/A',
                            'validee' => $activite['etat_valid'] ?? false,
                            'commentaire' => $activite['commentaire'] ?? '',
                            'date_validation' => $activite['date_validation'] ?? null,
                            'valideur' => $activite['valideur'] ?? 'N/A'
                        ];
                    }
                }
            }
        }
        
        return $activites;
    }
}
