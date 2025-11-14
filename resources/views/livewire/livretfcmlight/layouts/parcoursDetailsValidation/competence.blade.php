@props(['parcoursId', 'competence', 'couleurLigne'])

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'      => $parcoursId,
        'type'            => 'competence',
        'id'              => $competence['id'],
        'uuid'            => $competence['uuid'],
        'label'           => $competence['libelle_long'],
        'libelleLong'     => $this->escapeForTooltip($competence['libelle_long'] ?? ''),
        'libelleLongJs'   => $this->escapeForJs($competence['libelle_long'] ?? ''),
        'libelleCourt'    => $competence['libelle_court'],
        'etatValid'       => $competence['etat_valid'] ?? false,
        'dateValidation'  => $competence['date_validation'] ?? '',
        'dateProposition' => $competence['date_proposition'] ?? '',
        'valideur'        => $competence['valideur'] ?? '',
        'commentaire'     => $competence['commentaire'] ?? '',
        'couleurLigne'    => $competence['color'] ,
        'infos'           => [
            'competenceSavoirfaires' => $competence['competenceSavoirfaires'] ?? [],
        ],
        'finDeCourse'     => empty($competence['competenceSavoirfaires']),
        
    ])

    @if (!empty($competence['competenceSavoirfaires']))
        <ul class="collapsible ml2 mb1 transition-all duration-300"
            x-show="isOpen('competence_{{ $competence['uuid'] }}')"
            x-transition.duration.300ms>
            @foreach ($competence['competenceSavoirfaires'] as $csf)
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.savoirfaire', [
                    'parcoursId'   => $parcoursId,
                    'savoirfaire'  => $csf,
                    
                    
                ])
            @endforeach
        </ul>
    @endif
</li>
