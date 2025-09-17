@props(['parcoursId', 'fonction'])

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'fonction',
        'id'            => $fonction['id'],
        'uuid'          => $fonction['uuid'],
        'label'         => $fonction['libelle_court'],
        //'libelleLong'   => $fonction['libelle_long'],
        'libelleLong'   => $this->escapeForTooltip($fonction['libelle_long'] ?? ''),
        'libelleLongJs' => $this->escapeForJs($fonction['libelle_long'] ?? ''),
        'libelleCourt'  => $fonction['libelle_court'],
        'etatValid'     => $fonction['etat_valid'],
        'dateValidation'=> $fonction['date_validation'],
        'dateProposition'=> $fonction['date_proposition'],
        'valideur'      => $fonction['valideur'],
        'commentaire'   => $fonction['commentaire'],
        'infos'         => [],
    ])

    @if (!empty($fonction['competences']))
        {{-- <ul class="collapsible ml2 mb1" style="display:{{ isset($openSections['fonctions_'.$fonction['id']]) ? 'block' : 'none' }}"> --}}
            <ul
            class="collapsible ml2 mb1 transition-all duration-300"
            x-show="isOpen('fonction_{{ $fonction['uuid'] }}')"
            x-transition.duration.300ms
            >
            @foreach ($fonction['competences'] as $competence)
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.competence', [
                    'parcoursId' => $parcoursId,
                    'competence' => $competence,
                ])
            @endforeach
        </ul>
    @endif
</li>
