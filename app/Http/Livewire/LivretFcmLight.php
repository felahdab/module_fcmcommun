<?php
// filepath: Modules/FcmCommun/Http/Livewire/LivretFcmLight.php

namespace Modules\FcmCommun\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Services\OutilsMarinParcoursService;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Traits\FcmAccessControl;

class LivretFcmLight extends Component
{
    use FcmAccessControl;

    #[Locked]
    public $marinId;

    private OutilsMarinParcoursService $service;

    public string $search = '';
    public string $statutFilter = 'tous'; // tous, valides, proposes, non_valides

    protected $queryString = [
        'search' => ['except' => ''],
        'statutFilter' => ['except' => 'tous'],
    ];

    public function mount(Marin $marin)
    {
        $this->marinId = $marin->id;
    }

    public function boot()
    {
        $this->service = new OutilsMarinParcoursService();
    }

    #[Computed]
    public function marin()
    {
        return Marin::select(['id', 'uuid', 'nom', 'prenom'])
            ->with(['complementsFcm:id,rh_marin_id'])
            ->find($this->marinId);
    }

    #[Computed]
    public function parcours()
    {
        if (!$this->marin->complements_fcm?->donnees_du_parcours?->parcoursmarin) {
            return null;
        }
       
        return $this->marin->complements_fcm->donnees_du_parcours->parcoursmarin;
    }

    #[Computed]
    public function canPropose()
    {
        return $this->canAccessMarinLivret();
    }

    #[Computed]
    public function savoirFaireChildren()
    {
        if (!$this->parcours) {
            return [];
        }

        $mapping = [];
       
        foreach ($this->parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    $mapping[$savoirfaire['uuid']] = array_column(
                        $savoirfaire['activites'] ?? [],
                        'uuid'
                    );
                }
            }
        }

        return $mapping;
    }

    #[Computed]
    public function isCurrentUser()
    {
        return $this->marin->isCurrentUser();
    }

    #[Computed]
    public function parcoursFiltre()
    {
        if (!$this->parcours) {
            return null;
        }

        return $this->appliquerFiltres($this->parcours);
    }

    #[Computed]
    public function statistiques()
    {
        if (!$this->parcours) {
            return [
                'total' => 0,
                'valides' => 0,
                'proposes' => 0,
                'non_valides' => 0
            ];
        }

        return $this->calculerStatistiques();
    }

    public function filtrerParStatut(string $statut)
    {
        $this->statutFilter = $statut;
        Log::info("=== FILTRE PAR STATUT: {$statut} ===");
    }

    // ✅ LOGIQUE DE FILTRAGE ENTIÈREMENT REFAITE
    private function appliquerFiltres(array $parcours): array
    {
        $parcoursFiltre = $parcours;

        if (isset($parcoursFiltre['fonctions'])) {
            $fonctionsFiltrees = [];
           
            foreach ($parcoursFiltre['fonctions'] as $fonction) {
                $fonctionFiltre = $this->filtrerFonction($fonction);
                if ($fonctionFiltre !== null) {
                    $fonctionsFiltrees[] = $fonctionFiltre;
                }
            }
           
            $parcoursFiltre['fonctions'] = $fonctionsFiltrees;
        }

        return $parcoursFiltre;
    }

    private function filtrerFonction(array $fonction): ?array
    {
        $fonctionFiltre = $fonction;
        $competencesFiltrees = [];

        if (isset($fonction['competences'])) {
            foreach ($fonction['competences'] as $competence) {
                $competenceFiltre = $this->filtrerCompetence($competence);
                if ($competenceFiltre !== null) {
                    $competencesFiltrees[] = $competenceFiltre;
                }
            }
        }

        // ✅ Si aucune compétence n'a passé les filtres, ne pas afficher la fonction
        if (empty($competencesFiltrees)) {
            return null;
        }

        $fonctionFiltre['competences'] = $competencesFiltrees;
        return $fonctionFiltre;
    }

    private function filtrerCompetence(array $competence): ?array
    {
        $competenceFiltre = $competence;
        $savoirfairesFiltres = [];

        if (isset($competence['savoirfaires'])) {
            foreach ($competence['savoirfaires'] as $savoirfaire) {
                $savoirfaireFiltre = $this->filtrerSavoirFaire($savoirfaire);
                if ($savoirfaireFiltre !== null) {
                    $savoirfairesFiltres[] = $savoirfaireFiltre;
                }
            }
        }

        // ✅ Si aucun savoir-faire n'a passé les filtres, ne pas afficher la compétence
        if (empty($savoirfairesFiltres)) {
            return null;
        }

        $competenceFiltre['savoirfaires'] = $savoirfairesFiltres;
        return $competenceFiltre;
    }

    private function filtrerSavoirFaire(array $savoirfaire): ?array
    {
        // ✅ VÉRIFIER D'ABORD LE SAVOIR-FAIRE LUI-MÊME
        $savoirfaireCorrespond = $this->elementCorrespondAuxFiltres($savoirfaire, 'savoirfaire');
       
        // ✅ FILTRER LES ACTIVITÉS
        $activitesFiltrees = [];
        if (isset($savoirfaire['activites'])) {
            foreach ($savoirfaire['activites'] as $activite) {
                if ($this->elementCorrespondAuxFiltres($activite, 'activite')) {
                    $activitesFiltrees[] = $activite;
                }
            }
        }

        // ✅ LOGIQUE : garder le savoir-faire si LUI ou ses ACTIVITÉS correspondent
        if (!$savoirfaireCorrespond && empty($activitesFiltrees)) {
            return null;
        }

        $savoirfaireFiltre = $savoirfaire;
        $savoirfaireFiltre['activites'] = $activitesFiltrees;

        return $savoirfaireFiltre;
    }

    // ✅ VÉRIFICATION UNIQUEMENT POUR SAVOIRFAIRES ET ACTIVITÉS
    private function elementCorrespondAuxFiltres(array $element, string $type): bool
    {
        // ✅ Recherche SEULEMENT dans savoir-faires et activités
        if (!empty($this->search)) {
            if (!in_array($type, ['savoirfaire', 'activite'])) {
                return true; // Ne pas filtrer les fonctions/compétences par recherche
            }
           
            if (!$this->correspondRecherche($element)) {
                return false;
            }
        }

        // ✅ Statut SEULEMENT pour savoir-faires et activités
        if (in_array($type, ['savoirfaire', 'activite']) && $this->statutFilter !== 'tous') {
            return $this->verifierStatut($element);
        }

        return true;
    }

    private function correspondRecherche(array $element): bool
    {
        $recherche = strtolower(trim($this->search));
        $texteElement = strtolower(
            ($element['libelle_court'] ?? '') . ' ' 
            .($element['libelle_long'] ?? '') . ' ' 
            //.($element['description'] ?? '')
        );
       
        return str_contains($texteElement, $recherche);
    }

    // ✅ VÉRIFICATION DE STATUT AVEC LOGS DÉTAILLÉS
    private function verifierStatut(array $element): bool
    {
        $etatValid = ($element['etat_valid'] ?? 0);
        $dateProposition = $element['date_proposition'] ?? null;
       
        // ✅ Debug complet
        $libelle = $element['libelle_court'] ?? 'Element';
        Log::info("=== VÉRIF STATUT ===");
        Log::info("Element: {$libelle}");
        Log::info("etat_valid: " . json_encode($etatValid));
        Log::info("date_proposition: " . json_encode($dateProposition));
        Log::info("Filtre demandé: {$this->statutFilter}");

        // Valeur
        $estValide = ($etatValid === 1 || $etatValid === '1' || $etatValid === true);
        $estPropose = !empty($dateProposition);
        $nonValide = $estPropose && !$estValide;

        $resultat = false;
        switch ($this->statutFilter) {
            case 'valides':
                $resultat = $estValide;
                Log::info("Test validé (etat_valid == 1): " . ($resultat ? 'OUI' : 'NON'));
                break;
               
            case 'proposes':
                $resultat = $estPropose;
                Log::info("Test proposé (etat_valid != 1) && (!empty date_proposition): " . ($resultat ? 'OUI' : 'NON'));
                break;
               
            case 'non_valides':
                $resultat = $nonValide;
                Log::info("Test non validé (etat_valid != 1) && (empty date_proposition): " . ($resultat ? 'OUI' : 'NON'));
                break;
               
            default:
                $resultat = true;
        }
       
        Log::info("RÉSULTAT FINAL: " . ($resultat ? 'GARDÉ' : 'EXCLU'));
        Log::info("===================");
       
        return $resultat;
    }

    private function calculerStatistiques(): array
    {
        $total = 0;
        $valides = 0;
        $proposes = 0;

        foreach ($this->parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    $total++;
                   
                    $etatValid = ($savoirfaire['etat_valid'] ?? 0) == 1;
                    $dateProposition = !empty($savoirfaire['date_proposition']);
                   
                    if ($etatValid) {
                        $valides++;
                    }
                    if ($dateProposition) {
                        $proposes++;
                    }

                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $total++;
                       
                        $etatValidActivite = ($activite['etat_valid'] ?? 0) == 1;
                        $datePropositionActivite = !empty($activite['date_proposition']);
                       
                        if ($etatValidActivite) {
                            $valides++;
                        }
                        if ($datePropositionActivite) {
                            $proposes++;
                        }
                    }
                }
            }
        }

        return [
            'total' => $total,
            'valides' => $valides,
            'proposes' => $proposes,
            'non_valides' => $total - $valides
        ];
    }







    // ========================================
    // FONCTIONS
    // ========================================


    public function devalider(array $element_a_devalider)
    {
        /**
         * [ 'type' => 'activite',
         *   'uuid' => '06a569b8-23a7-4c3f-b57a-51ca841894a7' 
         * ]
         */
        logger()->info("Dévalider: ");
        logger()->info($element_a_devalider);

        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::ANNUL_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $element_a_devalider['type'], 
                "uuid_element" => $element_a_devalider['uuid'], 
                "date" => Carbon::now(), 
                
            ]
        );
        event($event);

        $this->marin->refresh();

        $this->js('window.location.reload()');
    }

    public function valider(array $elements_a_valider, string $commentaire)
    {
        logger()->info($elements_a_valider);

        if (array_key_exists("savoirfaire", $elements_a_valider)){
            foreach($elements_a_valider["savoirfaire"] as $savoirfaire_uuid => $bool) {
                if (!$bool) continue; // Ignorer si non sélectionné
                
                $event = new UserGeneratedEvent(
                    event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    user_uuid: auth()->user()->uuid,
                    object_class: ObjectClasses::MARIN,
                    object_uuid: $this->marin->uuid,
                    detail: [
                        "type_element" => "savoirfaire", 
                        "uuid_element" => $savoirfaire_uuid, 
                        "date" => Carbon::now(), 
                        "commentaire" => $commentaire,
                    ]
                );
                event($event);
            }
        }

        if (array_key_exists("activite", $elements_a_valider)){
            foreach($elements_a_valider["activite"] as $activite_uuid => $bool) {
                if (!$bool) continue; // Ignorer si non sélectionné
                
                $event = new UserGeneratedEvent(
                    event_type: UserGeneratedEventTypes::VALID_ELMT_MARIN_PARCOURS,
                    user_uuid: auth()->user()->uuid,
                    object_class: ObjectClasses::MARIN,
                    object_uuid: $this->marin->uuid,
                    detail: [
                        "type_element" => "activite", 
                        "uuid_element" => $activite_uuid, 
                        "date" => Carbon::now(), 
                        "commentaire" => $commentaire,
                    ]
                );
                event($event);
            }
        }

        $this->marin->refresh();

// Vider le cache
//unset($this->marin);  
//$this->reset();     
// Rafraîchir le composant Livewire
//$this->dispatch('$refresh');
// Recharger la page 
$this->js('window.location.reload()');

    }

    public function proposer(array $element_a_proposer, string $commentaire)
    {
        /**
         * [ 'type' => 'activite',
         *   'uuid' => '06a569b8-23a7-4c3f-b57a-51ca841894a7' 
         * ]
         */
        logger()->info("Proposer: ");
        logger()->info($element_a_proposer);

        $event = new UserGeneratedEvent(
            event_type: UserGeneratedEventTypes::PROPOS_ELMT_MARIN_PARCOURS,
            user_uuid: auth()->user()->uuid,
            object_class: ObjectClasses::MARIN,
            object_uuid: $this->marin->uuid,
            detail: [
                "type_element" => $element_a_proposer['type'], 
                "uuid_element" => $element_a_proposer['uuid'], 
                "date" => Carbon::now(), 
                "commentaire" => $commentaire
            ]
        );
        event($event);

        $this->marin->refresh();

        $this->js('window.location.reload()');
    }


    // ========================================
    // HELPER
    // ========================================

    private function escapeForTooltip($text): string
{
    if (empty($text)) {
        return '';
    }
   
    // Échapper les caractères spéciaux pour éviter les bugs JavaScript/HTML
    return htmlspecialchars(
        str_replace(["\r\n", "\n", "\r", "'", '"'], [' ', ' ', ' ', "&#39;", "&#34;"], $text),
        ENT_QUOTES,
        'UTF-8'
    );
}

private function escapeForJs($text): string
{
    if (empty($text)) {
        return '';
    }
   
    return addslashes(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
}





    // ========================================
    // RENDU OPTIMISÉ
    // ========================================

    public function render()
    {
        return view('fcmcommun::livewire.livretfcmlight.livretfcmlight', [
            'marin' => $this->marin,
            'parcours' => $this->parcoursFiltre,
            'isCurrentUser' => $this->isCurrentUser,
            'savoirFaireChildren' => $this->savoirFaireChildren,
            'canPropose' => $this->canPropose,
            'statistiques' => $this->statistiques,
        ]);
    }



    

   
}
