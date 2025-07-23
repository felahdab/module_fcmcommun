@props(['parcoursId', 'fonction', 'openSections'])

<li>
    @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'fonctions',
        'id'            => $fonction['id'],
        'uuid'          => $fonction['uuid'],
        'label'         => 'Fonction : ' . $fonction['libelle_court'],
        //'libelleLong'   => $fonction['libelle_long'],
        'libelleLong'   => '',
        'libelleCourt'  => $fonction['libelle_court'],
        'etatValid'     => $fonction['etat_valid'],
        'dateValidation'=> $fonction['date_validation'],
        'dateProposition'=> $fonction['date_proposition'],
        'valideur'      => $fonction['valideur'],
        'commentaire'   => $fonction['commentaire'],
        'commentaireProposition' => $fonction['commentaire_proposition'],
        'finDeCourse'   => empty($fonction['competences']),
        'infos'         => [],
    ])

    @if (!empty($fonction['competences']))
        {{-- <ul class="collapsible ml2 mb1" style="display:{{ isset($openSections['fonctions_'.$fonction['id']]) ? 'block' : 'none' }}"> --}}
            <ul
            class="collapsible ml2 mb1 transition-all duration-300"
            x-show="isOpen('fonctions_{{ $fonction['uuid'] }}')"
            x-transition.duration.300ms
            >
            @foreach ($fonction['competences'] as $competence)
                @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.competence', [
                    'parcoursId' => $parcoursId,
                    'competence' => $competence,
                    'openSections' => $openSections,
                ])
            @endforeach
        </ul>
    @endif
</li>
