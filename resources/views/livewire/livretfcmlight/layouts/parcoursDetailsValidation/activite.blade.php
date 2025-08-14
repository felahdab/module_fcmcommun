@props(['parcoursId', 'activite'])

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'activite',
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
        'finDeCourse'   => true,
        'infos'         => [
            'coeff' => $activite['pivot']['coeff'] ?? null,
            'duree' => $activite['pivot']['duree'] ?? null,
        ],
    ])
</li>
