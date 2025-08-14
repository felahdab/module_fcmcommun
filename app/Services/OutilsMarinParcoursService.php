<?php

namespace Modules\FcmCommun\Services;

use Closure;

class OutilsMarinParcoursService
{
    public $ppa= null;
    public $fonction= null;
    public $competence= null;
    public $savoirfaire= null;

    public function activites_du_parcours_respectant_une_condition(array $ppa = [], Closure $predicat)
    {
        $result=[];
        foreach($ppa["fonctions"] as $fonction)
        {
            foreach($fonction["competences"] as $competence)
            {
                foreach($competence["savoirfaires"] as $savoirfaire)
                {
                    foreach($savoirfaire["activites"] as $activite)
                    {
                        if ($predicat($activite))
                        {
                            $result[] = $activite;
                        }

                    }
                }
            }
        }
        return $result;
    }

    public function liste_des_activites_du_parcours_respectant_une_condition(array $ppa = [], Closure $predicat)
    {
        $result = [];

        $activites = $this->activites_du_parcours_respectant_une_condition($ppa, $predicat);
        foreach($activites as $activite)
        {
            $result[] = $activite['uuid'];
        }

        return $result;
    }


    public function liste_activites_valid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->liste_des_activites_du_parcours_respectant_une_condition($ppa, function ($activite){
            return $activite["etat_valid"];
        });
    }

    public function activites_valid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        return $this->activites_du_parcours_respectant_une_condition($ppa, function ($activite){
            return $activite["etat_valid"];
        });

        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function activites_nonvalid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function activites_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function stages_valid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function stages_nonvalid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function stages_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function taches_valid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function taches_nonvalid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function taches_propos_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function taches_du_parcours(array $ppa = [], string $fonction = null, string $competence = null, string $savoirfaire = null)
    {
        $result=[];
        foreach ($this->savoirfaires_du_parcours($ppa, $fonction, $competence) as $sf){
            if ( $savoirfaire==null or $sf['uuid'] == $savoirfaire ){
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
    public function savoirfaires_valid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp['uuid'] == $competence ){
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
    public function savoirfaires_nonvalid_du_parcours(array $ppa = [], string $fonction = null, string $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp['uuid'] == $competence ){
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
    public function savoirfaires_propos_du_parcours(array $ppa = [], string $fonction = null, string $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp['uuid'] == $competence ){
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
    public function savoirfaires_du_parcours(array $ppa = [], string $fonction = null, string $competence = null)
    {
        $result=[];
        foreach ($this->competences_du_parcours($ppa, $fonction) as $comp){
            if ( $competence==null or $comp['uuid'] == $competence ){
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
    public function competences_valid_du_parcours(array $ppa = [], string $fonction = null)
    {
        $result=[];
        foreach ($this->fonctions_du_parcours($ppa) as $fonc){
            if ( $fonction==null or $fonc['uuid'] == $fonction ){
                foreach($fonc['competences'] as $competence){
                    if($competence['etat_valid'] == true){
                        $result[] = ['uuid' => $competence['uuid']];
                    }
                }
            }                
        }
        return array_unique($result);
    }
    public function competences_nonvalid_du_parcours(array $ppa = [], string $fonction = null)
    {
        $result=[];
        foreach ($this->fonctions_du_parcours($ppa) as $fonc){
            if ( $fonction == null or $fonc['uuid'] == $fonction ){
                foreach($fonc['competences'] as $competence){
                    if($competence['etat_valid'] != true){
                        $result[] = ['uuid' => $competence['uuid']];
                    }
                }
            }                
        }
        return array_unique($result);
    }

    public function competences_du_parcours(array $ppa = [], string $fonction = null)
    {
        $result=[];
        foreach ($this->fonctions_du_parcours($ppa) as $fonc){
            if ( $fonction==null or $fonc['uuid'] == $fonction ){
                foreach($fonc['competences'] as $competence){
                    $result[] = ['uuid' => $competence['uuid']];
                }
            }                
        }
        return array_unique($result);
    }
    
    public function fonctions_valid_du_parcours(array $ppa = null)
    {
        $result=[];
        foreach ($ppa['fonctions'] as $fonction){
            if($fonction['etat_valid'] == true){
                $result[] = ['uuid' => $fonction['uuid']];
            }
        }
        return array_unique($result);
    }

    public function fonctions_nonvalid_du_parcours(array $ppa = null)
    {
        $result=[];
        foreach ($ppa['fonctions'] as $fonction){
            if($fonction['etat_valid'] != true){
                $result[] = ['uuid' => $fonction['uuid']];
            }
        }
        return array_unique($result);
    }

    public function fonctions_du_parcours(array $ppa = null)
    {
        $result=[];
        foreach ($ppa['fonctions'] as $fonction){
           $result[] = ['uuid' => $fonction['uuid']];
        }
        return array_unique($result);
    }
}
