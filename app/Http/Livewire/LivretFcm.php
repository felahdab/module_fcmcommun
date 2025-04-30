<?php

namespace Modules\FcmCommun\Http\Livewire;

use Livewire\Component;
use Modules\FcmCommun\Traits\LoadsModelsDynamically;
use Filament\Notifications\Notification;
use Modules\FcmCommun\Services\EventTriggerService;


class LivretFcm extends Component
{

    use LoadsModelsDynamically;

    public $fcmMarinId;             // Id de FcmMarin
    public $tablePrefix;            // Prefixe de la table
    public $fcmMarinModel;     // Modele chagé dynamique
    public $parcours;
 
    public $selectedFonctions = [];
    public $selectedCompetences = [];
    public $selectedSavoirFaires = [];
    public $selectedActivites = [];
    public $comment ='';




    public function mount($fcmMarinId, $tablePrefix)
    {
        $this->fcmMarinId = $fcmMarinId;
        $this->tablePrefix = $tablePrefix;

        // Charger dynamiquement MarinPArcours
        $this->fcmMarinModel = $this->getModelClass($this->tablePrefix, 'FcmMarin');
        $this->loadParcoursSerialise();
    }

    protected function loadParcoursSerialise()
    {


        $fcmMarin = $this->fcmMarinModel::with('parcoursSerialises')
            ->find($this->fcmMarinId);



        if ($fcmMarin &&  $fcmMarin->parcoursSerialises->isNotEmpty()) {

            $this->parcours = $fcmMarin->parcoursSerialises->map(function ($parcours) {
                return [
                    'id' => $parcours->id,
                    'libelle_court' => $parcours->libelle_court,
                    'parcours' => $parcours->parcours, // Champ JSON
                    'taux_global' => $parcours->pivot->taux_global,
                    'taux_stage' => $parcours->pivot->taux_stage,
                    'taux_activite' => $parcours->pivot->taux_activite,
                ];
            })->toArray();
            
        } else {
            // Gérer le cas où les données ne sont pas trouvées
            $this->parcours = [];
        }
    }

   public function openModal(){
    $this->dispatch('open-modal', id:'comment');
   }

    public function saveForm()
    {
        // Sauvegarder les données sélectionnées et le commentaire
        $selectedData = [];
        foreach ($this->selectedFonctions as $parcoursId => $fonctions){
            $selectedData[] = [
                'parcours_id' =>$parcoursId,
                'fonctions' => $fonctions,
                'competences' => $this->selectedCompetences[$parcoursId]??[],
                'savoirFaires' => $this->selectedSavoirFaires[$parcoursId]??[],
                'activites' => $this->selectedActivites[$parcoursId]??[],
    

            ];
        }

        $data= [
            'comment' => $this->comment,
            'parcours' => $selectedData,
        ];

        dd($data);
        
        // $marinParcoursClass= $this->getModelClass($this->tablePrefix, 'MarinParcours');
        // $record = $marinParcoursClass::with('parcoursSerialises')
        // ->find($this->fcmMarinId);
        // dd($record);

        // // Logique de sauvegarde ici...
        // EventTriggerService::triggerEvent($data, $record);


        // Réinitialiser les données
        $this->reset(['selectedFonctions', 'selectedCompetences', 'selectedSavoirFaires', 'selectedActivites', 'comment']);

        // Fermer le modal
        $this->closeModal();

       
    }

    






//     public function handleCheckboxUpdate($parcoursId, $fonctionIndex = null, $competenceIndex = null, $savoirFaireIndex = null, $activiteIndex = null)
//     {


       

//         // ⚠️ Ne sauvegarde pas ici, mais émet un event Laravel
//         // event(new \Modules\FcmCentral\Events\ParcoursUpdated(
//         //     $this->marin->id,
//         //     $this->parcours
//         // ));

//         // $this->dispatchBrowserEvent('notify', ['message' => 'Parcours envoyé pour mise à jour']);
      
//     // Construire un message pour le débogage
//     $message = "Checkbox mise à jour pour : Parcours ID = {$parcoursId}";

//     if (!is_null($fonctionIndex)) {
//         $message .= ", Fonction Index = {$fonctionIndex}";
//     }

//     if (!is_null($competenceIndex)) {
//         $message .= ", Compétence Index = {$competenceIndex}";
//     }

//     if (!is_null($savoirFaireIndex)) {
//         $message .= ", Savoir-Faire Index = {$savoirFaireIndex}";
//     }

//     if (!is_null($activiteIndex)) {
//         $message .= ", Activité Index = {$activiteIndex} ";
//     }

//     // Afficher une notification ou effectuer une action
//    // $this->dispatchBrowserEvent('notify', ['message' => $message]);


//         //$message = "Checkbox mise à jour : {$key} - Nouvelle valeur : " . ($value ? 'Cochée' : 'Décochée');
//         //$this->dispatchBrowserEvent('notify', ['message' => $message]);
//         Notification::make()
//         ->title('Mise à jour du suivi FCM')
//         ->success()
//         ->body($message)
//         ->send();
//     }




    public function render()
    {
        //return view('fcmcommun::livewire.livret-fcm',[
        return view('fcmcentral::filament.fcmcentral.livewire.parcours.parcours-details-validation', [
            'parcours' => $this->parcours,
        ]);
    }
}
