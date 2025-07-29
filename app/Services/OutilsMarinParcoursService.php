<?php

namespace Modules\FcmCommun\Services;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Activite;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\Fonction;
use Modules\FcmCommun\Models\MarinParcours;

class OutilsMarinParcoursService
{
    public $ppa= null;
    public $fonction= null;
    public $competence= null;
    public $savoirfaire= null;

    // activites (stages+taches)
    public function activites_valid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['etat_valid'] == true){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function activites_nonvalid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['etat_valid'] != true){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function activites_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    // $result[] = ['uuid' => $activite['uuid']];
                    $result[] = [$activite['uuid']=>
                                            ['etat_valid' => $activite['etat_valid'], 
                                            'valideur '=> $activite['valideur'],
                                            'commentaire '=> $activite['commentaire'],
                                            'date_validation '=> $activite['date_validation'],
                                            'commentaire_proposition '=> $activite['commentaire_proposition'],
                                            'date_proposition '=> $activite['date_proposition'],
                                            ]
                                ];
                }
            }                
        }
        return array_unique($result);
    }

    //uniquement les activites de type stage
    public function stages_valid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['etat_valid'] == true && $activite['type_activite'] == 'STAGE'){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function stages_nonvaliddu_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['etat_valid'] != true && $activite['type_activite'] == 'STAGE'){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function stages_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['type_activite'] == 'STAGE'){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }

    //uniquement les activites de type tache
    public function taches_valid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['etat_valid'] == true && $activite['type_activite'] == 'TACHE'){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function taches_nonvalid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['etat_valid'] != true && $activite['type_activite'] == 'TACHE'){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function taches_propos_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['type_activite'] == 'TACHE' && $activite['date_proposition'] != NULL){
                        // $result[] = ['uuid' => $activite['uuid']];
                        $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function taches_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null, Savoirfaire $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf->id == $savoirfaire->id ){
                foreach($sf['activite'] as $activite){
                    if($activite['type_activite'] == 'TACHE'){
                         // $result[] = ['uuid' => $activite['uuid']];
                         $result[] = [$activite['uuid']=>
                                                ['etat_valid' => $activite['etat_valid'], 
                                                'valideur '=> $activite['valideur'],
                                                'commentaire '=> $activite['commentaire'],
                                                'date_validation '=> $activite['date_validation'],
                                                'commentaire_proposition '=> $activite['commentaire_proposition'],
                                                'date_proposition '=> $activite['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }

    //savoir-faire
    public function savoirfaires_valid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp->id == $competence->id ){
                foreach($comp['savoirfaire'] as $savoirfaire){
                    if($savoirfaire['etat_valid'] == true){
                         // $result[] = ['uuid' => $savoirfaire['uuid']];
                         $result[] = [$savoirfaire['uuid']=>
                                                ['etat_valid' => $savoirfaire['etat_valid'], 
                                                'valideur '=> $savoirfaire['valideur'],
                                                'commentaire '=> $savoirfaire['commentaire'],
                                                'date_validation '=> $savoirfaire['date_validation'],
                                                'commentaire_proposition '=> $savoirfaire['commentaire_proposition'],
                                                'date_proposition '=> $savoirfaire['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function savoirfaires_nonvalid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp->id == $competence->id ){
                foreach($comp['savoirfaire'] as $savoirfaire){
                    if($savoirfaire['etat_valid'] != true){
                         // $result[] = ['uuid' => $savoirfaire['uuid']];
                         $result[] = [$savoirfaire['uuid']=>
                                                ['etat_valid' => $savoirfaire['etat_valid'], 
                                                'valideur '=> $savoirfaire['valideur'],
                                                'commentaire '=> $savoirfaire['commentaire'],
                                                'date_validation '=> $savoirfaire['date_validation'],
                                                'commentaire_proposition '=> $savoirfaire['commentaire_proposition'],
                                                'date_proposition '=> $savoirfaire['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function savoirfaires_propos_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp->id == $competence->id ){
                foreach($comp['savoirfaire'] as $savoirfaire){
                    if($savoirfaire['date_proposition'] != NULL){
                         // $result[] = ['uuid' => $savoirfaire['uuid']];
                         $result[] = [$savoirfaire['uuid']=>
                                                ['etat_valid' => $savoirfaire['etat_valid'], 
                                                'valideur '=> $savoirfaire['valideur'],
                                                'commentaire '=> $savoirfaire['commentaire'],
                                                'date_validation '=> $savoirfaire['date_validation'],
                                                'commentaire_proposition '=> $savoirfaire['commentaire_proposition'],
                                                'date_proposition '=> $savoirfaire['date_proposition'],
                                                ]
                                    ];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function savoirfaires_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null, Competence $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp->id == $competence->id ){
                foreach($comp['savoirfaire'] as $savoirfaire){
                         // $result[] = ['uuid' => $savoirfaire['uuid']];
                         $result[] = [$savoirfaire['uuid']=>
                                                ['etat_valid' => $savoirfaire['etat_valid'], 
                                                'valideur '=> $savoirfaire['valideur'],
                                                'commentaire '=> $savoirfaire['commentaire'],
                                                'date_validation '=> $savoirfaire['date_validation'],
                                                'commentaire_proposition '=> $savoirfaire['commentaire_proposition'],
                                                'date_proposition '=> $savoirfaire['date_proposition'],
                                                ]
                                    ];
                }
            }                
        }
        return array_unique($result);
    }

    //compétence
    public function competences_valid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null)
    {
        $result=[];
        foreach ($this->fonctions_du_parcours($ppa) as $fonc){
            if ( $fonction==null or $fonc->id == $fonction->id ){
                foreach($fonc['competence'] as $competence){
                    if($competence['etat_valid'] == true){
                        $result[] = ['uuid' => $competence['uuid']];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function competences_nonvalid_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null)
    {
        $result=[];
        foreach ($this->fonctions_du_parcours($ppa) as $fonc){
            if ( $fonction==null or $fonc->id == $fonction->id ){
                foreach($fonc['competence'] as $competence){
                    if($competence['etat_valid'] != true){
                        $result[] = ['uuid' => $competence['uuid']];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function competences_du_parcours(MarinParcours $ppa = null, Fonction $fonction = null)
    {
        $result=[];
        foreach ($this->fonctions_du_parcours($ppa) as $fonc){
            if ( $fonction==null or $fonc->id == $fonction->id ){
                foreach($fonc['competence'] as $competence){
                    $result[] = ['uuid' => $competence['uuid']];
                }
            }                
        }
        return array_unique($result);
    }
    //fonction
    public function fonctions_valid_du_parcours(ParcoursSerialise $ppa = null)
    {
        $result=[];
        foreach ($ppa['fonction'] as $fonction){
            if($fonction['etat_valid'] == true){
                $result[] = ['uuid' => $fonction['uuid']];
            }
        }
        return array_unique($result);
    }
    public function fonctions_nonvalid_du_parcours(ParcoursSerialise $ppa = null)
    {
        $result=[];
        foreach ($ppa['fonction'] as $fonction){
            if($fonction['etat_valid'] != true){
                $result[] = ['uuid' => $fonction['uuid']];
            }
        }
        return array_unique($result);
    }
    public function fonctions_du_parcours(ParcoursSerialise $ppa = null)
    {
        $result=[];
        foreach ($ppa['fonction'] as $fonction){
           $result[] = ['uuid' => $fonction['uuid']];
        }
        return array_unique($result);
    }
    
    
}
