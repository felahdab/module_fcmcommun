@props(['parcoursId', 'savoirfaire', 'couleurLigne'])

<li class="mb-2">
    @php
        $pivotId = $savoirfaire['pivot']['id'];
        $pivotUuid = $savoirfaire['uuid'].'_'.$savoirfaire['pivot']['uuid'];
       
        // ✅ Récupérer directement les objectifs depuis les données sérialisées
        $objectifsList = $savoirfaire['objectifs'] ?? [];
    @endphp

    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'      => $parcoursId,
        'type'            => 'savoirfaire',
        'id'              => $savoirfaire['id'].'_'.$savoirfaire['pivot']['id'],
        'uuid'            => $pivotUuid,
        'label'           => $savoirfaire['libelle_long'] ?? $savoirfaire['libelle_court'] ?? 'Savoir-faire sans nom',
        'libelleLong'     => $this->escapeForTooltip($savoirfaire['libelle_long'] ?? ''),
        'libelleLongJs'   => $this->escapeForJs($savoirfaire['libelle_long'] ?? ''),
        'libelleCourt'    => $savoirfaire['libelle_court'] ?? '',
        'etatValid'       => $savoirfaire['etat_valid'] ?? false,
        'dateValidation'  => $savoirfaire['date_validation'] ?? '',
        'dateProposition' => $savoirfaire['date_proposition'] ?? '',
        'valideur'        => $savoirfaire['valideur'] ?? '',
        'commentaire'     => $savoirfaire['commentaire'] ?? '',
        'couleurLigne'    => $couleurLigne,
        'infos'           => [
            'code'                => $savoirfaire['code'] ?? '',
            'competence_code'     => $this->getCompetenceCode($savoirfaire),
            'competence_color'    => $this->getCompetenceColor($savoirfaire),
            'savoirfaire_type_code'    => $this->getSavoirfaireTypeCode($savoirfaire),
            'savoirfaire_type_color'   => $this->getSavoirfaireTypeColor($savoirfaire),
            'objectifs'           => $pivotId, // ✅ Garde l'ID pour le JavaScript
            'objectifs_list'      => $objectifsList, // ✅ Liste complète des objectifs
            'heures'              => $savoirfaire['heures'] ?? null,
            'jours'               => $savoirfaire['jours'] ?? null,
            'intitule'            => $savoirfaire['intitule'] ?? '',
        ],
        'canPropose'      => true,
    ])

    {{-- ✅ AFFICHAGE DES OBJECTIFS --}}
    @if (!empty($objectifsList))
        <ul class="collapsible ml4 mb1 mt2 space-y-1 transition-all duration-300 border-l-2 border-gray-200"
            x-show="isOpen('savoirfaire_{{ $pivotUuid }}')"
            x-transition.duration.300ms
            style="padding-left: 1rem;">
            @foreach ($objectifsList as $objectif)
                @php
                    // ✅ Créer un UUID cohérent pour l'objectif
                    $objectifUuid = $objectif['uuid'] ?? ('obj_' . $pivotId . '_' . $objectif['id']);
                @endphp
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.objectif', [
                    'parcoursId' => $parcoursId,
                    'objectif' => array_merge($objectif, ['uuid' => $objectifUuid]),
                    'couleurLigne' => $couleurLigne,
                ])
            @endforeach
        </ul>
    @endif
</li>
