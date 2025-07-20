@props(['parcoursId', 'competence', 'openSections'])

<li>
    @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'competences',
        'id'            => $competence['id'],
        'uuid'          => $competence['uuid'],
        'label'         => 'Competence : ' . $competence['libelle_court'],
        //'libelleLong'   => $competence['libelle_long'],
        'libelleLong'   => '',
        'libelleCourt'  => $competence['libelle_court'],
        'etatValid'     => $competence['etat_valid'],
        'dateValidation'=> $competence['date_validation'],
        'dateProposition'=> $competence['date_proposition'],
        'valideur'      => $competence['valideur'],
        'commentaire'   => $competence['commentaire'],
        'commentaireProposition' => $competence['commentaire_proposition'],
        'finDeCourse'   => empty($competence['savoirfaires']),
        'infos'         => [],
    ])

    @if (!empty($competence['savoirfaires']))
        {{-- <ul class="collapsible highlight" style="display:{{ isset($openSections['competences_'.$competence['id']]) ? 'block' : 'none' }}"> --}}
            <ul
            class="collapsible ml2 mb1 transition-all duration-300"
            x-show="isOpen('competences_{{ $competence['id'] }}')"
            x-transition.duration.300ms
            >
            @foreach ($competence['savoirfaires'] as $savoirFaire)
                @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.savoirfaire', [
                    'parcoursId' => $parcoursId,
                    'savoirFaire' => $savoirFaire,
                    'openSections' => $openSections,
                ])
            @endforeach
        </ul>
    @endif
</li>
