@props(['parcoursId', 'objectif', 'couleurLigne'])

<li>
    @php

        $dateV =!empty($objectif['date_validation'])
        ? \Carbon\Carbon::parse($objectif['date_validation'])->translatedFormat('d F Y')
        //? date('d/m/Y', strtotime())
        : '';

        $dateP =!empty( $objectif['date_proposition'])
        ? \Carbon\Carbon::parse( $objectif['date_proposition'])->translatedFormat('d F Y')
        //? date('d/m/Y', strtotime())
        : '';
        
    @endphp
        
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'      => $parcoursId,
        'type'            => 'objectif',
        //'id'              => $objectif['id'] ?? null,
        'uuid'            => $objectif['uuid'] ?? null, // ✅ Utiliser l'UUID passé ou l'ID
        'label'           => $objectif['libelle_long'] ?? $objectif['libelle_court'] ?? 'Objectif sans nom',
        'libelleLong'     => $this->escapeForTooltip($objectif['libelle_long'] ?? $objectif['libelle_court'] ?? 'Objectif sans nom'),
        'libelleLongJs'   => $this->escapeForJs($objectif['libelle_long'] ?? $objectif['libelle_court'] ?? 'Objectif sans nom'),
        'libelleCourt'    => $objectif['libelle_court'] ?? '',
        'etatValid'       => $objectif['etat_valid'] ?? false,
        'dateValidation'  => $dateV,
        'dateProposition' => $dateP,
        'valideur'        => $objectif['valideur'] ?? '',
        'commentaire'     => $objectif['commentaire'] ?? '',
        'commentaireProposition'     => $objectif['commentaire_proposition'] ?? '',
        'code'            => $objectif['code'] ?? '',
        'couleurLigne'    => $couleurLigne,
        'finDeCourse'     => true,
        'infos'           => [
            'description' => $objectif['description'] ?? '',
            'ordre'       => $objectif['ordre'] ?? 0,
            'coeff'       => $objectif['coeff'] ?? '',
            'code'        => $objectif['code'] ?? '',
        ],
        
    ])
</li>
