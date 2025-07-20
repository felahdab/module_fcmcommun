@props(['parcoursId', 'activite'])

<li>
    @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'activites',
        'id'            => $activite['id'],
        'uuid'          => $activite['uuid'],
        'label'         => 'Activite : ' . $activite['libelle_court'],
        //'libelleLong'   => $activite['libelle_long'],
        'libelleLong'   => '',
        'libelleCourt'  => $activite['libelle_court'],
        'etatValid'     => $activite['etat_valid'],
        'dateValidation'=> $activite['date_validation'],
        'dateProposition'=> $activite['date_proposition'],
        'valideur'      => $activite['valideur'],
        'commentaire'   => $activite['commentaire'],
        'commentaireProposition' => $activite['commentaire_proposition'],
        'finDeCourse'   => true,
        'infos'         => [
            'coeff' => $activite['pivot']['coeff'] ?? null,
            'duree' => $activite['pivot']['duree'] ?? null,
        ],
    ])
</li>
