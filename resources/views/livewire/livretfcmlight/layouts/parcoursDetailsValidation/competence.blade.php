@props(['parcoursId', 'competence'])

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'competence',
        'id'            => $competence['id'],
        'uuid'          => $competence['uuid'],
        'label'         => $competence['libelle_court'],
        //'libelleLong'   => $competence['libelle_long'],
        'libelleLong'   => $this->escapeForTooltip($competence['libelle_long'] ?? ''),
        'libelleLongJs' => $this->escapeForJs($competence['libelle_long'] ?? ''),
        'libelleCourt'  => $competence['libelle_court'],
        'etatValid'     => $competence['etat_valid'],
        'dateValidation'=> $competence['date_validation'],
        'dateProposition'=> $competence['date_proposition'],
        'valideur'      => $competence['valideur'],
        'commentaire'   => $competence['commentaire'],
        'infos'         => [],
    ])

    @if (!empty($competence['savoirfaires']))
        {{-- <ul class="collapsible highlight" style="display:{{ isset($openSections['competences_'.$competence['id']]) ? 'block' : 'none' }}"> --}}
            <ul
            class="collapsible ml2 mb1 transition-all duration-300"
            x-show="isOpen('competence_{{ $competence['uuid'] }}')"
            x-transition.duration.300ms
            >
            @foreach ($competence['savoirfaires'] as $savoirFaire)
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.savoirfaire', [
                    'parcoursId' => $parcoursId,
                    'savoirFaire' => $savoirFaire,
                ])
            @endforeach
        </ul>
    @endif
</li>
