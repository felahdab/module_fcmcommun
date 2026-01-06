@props(['parcoursId', 'objectif', 'couleurLigne'])

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'      => $parcoursId,
        'type'            => 'objectif',
        'id'              => $objectif['id'] ?? null,
        'uuid'            => $objectif['uuid'] ?? null, // ✅ Utiliser l'UUID passé ou l'ID
        'label'           => $objectif['libelle_long'] ?? $objectif['libelle_court'] ?? 'Objectif sans nom',
        'libelleLong'     => $this->escapeForTooltip($objectif['libelle_long'] ?? $objectif['libelle_court'] ?? 'Objectif sans nom'),
        'libelleLongJs'   => $this->escapeForJs($objectif['libelle_long'] ?? $objectif['libelle_court'] ?? 'Objectif sans nom'),
        'libelleCourt'    => $objectif['libelle_court'] ?? '',
        'etatValid'       => $objectif['etat_valid'] ?? false,
        'dateValidation'  => $objectif['date_validation'] ?? '',
        'dateProposition' => $objectif['date_proposition'] ?? '',
        'valideur'        => $objectif['valideur'] ?? '',
        'commentaire'     => $objectif['commentaire'] ?? '',
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
