@props(['parcoursId', 'savoirfaire', 'objectifsParSavoirfaire'])

<li class="mb-2">
    @php
       
        $pivotUuid = $savoirfaire['uuid'] ?? ('csf_' . $savoirfaire['id']);
        $savoirfaireId = $savoirfaire['savoirfaire']['id'] ?? null;
       
        // ✅ Récupérer tous les objectifs pour ce savoirfaire_id (Compagnonnage + Stage)
        $objectifsList = $savoirfaireId && isset($objectifsParSavoirfaire[$savoirfaireId])
            ? $objectifsParSavoirfaire[$savoirfaireId]
            : ($savoirfaire['objectifs'] ?? []);
            
        $dateV =!empty($savoirfaire['date_validation'])
        ? \Carbon\Carbon::parse($savoirfaire['date_validation'])->translatedFormat('d F Y')
        //? date('d/m/Y', strtotime())
        : '';

        $dateP =!empty( $savoirfaire['date_proposition'])
        ? \Carbon\Carbon::parse( $savoirfaire['date_proposition'])->translatedFormat('d F Y')
        //? date('d/m/Y', strtotime())
        : '';
        
    @endphp

    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'      => $parcoursId,
        'type'            => 'savoirfaire',
        //'id'              => $savoirfaire['id'],
        'uuid'            => $pivotUuid,
        'label'           => $savoirfaire['savoirfaire']['libelle_long'] ?? $savoirfaire['savoirfaire']['libelle_court'] ?? 'SF sans nom',
        'libelleLong'     => $this->escapeForTooltip($savoirfaire['savoirfaire']['libelle_long'] ?? ''),
        'libelleLongJs'   => $this->escapeForJs($savoirfaire['savoirfaire']['libelle_long'] ?? ''),
        'libelleCourt'    => $savoirfaire['savoirfaire']['libelle_court'] ?? '',
        'etatValid'       => $savoirfaire['etat_valid'] ?? false,
        'dateValidation'  => $dateV,
        'dateProposition' => $dateP,
        'valideur'        => $savoirfaire['valideur'] ?? '',
        'commentaire'     => $savoirfaire['commentaire'] ?? '',
        'commentaireProposition'     => $savoirfaire['commentaire_proposition'] ?? '',
        'pieceJointe'     => $savoirfaire['piece_jointe'] ?? null,
        'couleurLigne'    => $savoirfaire['savoirfaire']['color'],
        'heures'          => $savoirfaire['heures'] ?? null,
        'jours'           => $savoirfaire['jours'] ?? null,
        'intitule'        => $savoirfaire['libelle_court'] ?? '',
        'infos'           => [
            'code'                     => $savoirfaire['savoirfaire']['code'] ?? '',
            'competence_code'          => $this->getCompetenceCode($savoirfaire),
            'competence_color'         => $this->getCompetenceColor($savoirfaire),
            'savoirfaire_type_code'    => $savoirfaire['savoirfaire_type']['code'] ?? 'C',
            'savoirfaire_type_color'   => $savoirfaire['savoirfaire_type']['color'] ?? 'info',
            'objectifs_list'           => $objectifsList, // ✅ OBJECTIFS FUSIONNÉS
        ],
    ])

    @if (!empty($objectifsList))
        <ul class="collapsible ml4 mb1 mt2 space-y-1 transition-all duration-300 border-l-2 border-gray-200"
            x-show="isOpen('savoirfaire_{{ $pivotUuid }}')"
            x-transition.duration.300ms
            style="padding-left: 1rem;">
            @foreach ($objectifsList as $objectif)
                @php
                    $objectifUuid = $objectif['uuid'] ?? ('obj_' . $pivotUuid . '_' . $objectif['id']);
                @endphp
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.objectif', [
                    'parcoursId'   => $parcoursId,
                    'objectif'     => array_merge($objectif, ['uuid' => $objectifUuid]),
                    'couleurLigne' => $savoirfaire['savoirfaire']['color'],
                ])
            @endforeach
        </ul>
    @endif
</li>
