@props(['parcoursId', 'annee'])

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'annee',
        'id'            => $annee['id'],
        'uuid'          => $annee['uuid'],
        'label'         => $annee['libelle_court'],
        'libelleLong'   => $this->escapeForTooltip($annee['libelle_long'] ?? ''),
        'libelleLongJs' => $this->escapeForJs($annee['libelle_long'] ?? ''),
        'libelleCourt'  => $annee['libelle_court'],
        'etatValid'     => $annee['etat_valid'],
        'dateValidation'=> $annee['date_validation'],
        'dateProposition'=> $annee['date_proposition'],
        'valideur'      => $annee['valideur'],
        'commentaire'   => $annee['commentaire'],
        'couleurLigne'  => $annee['color'] ?? 'gray', // ✅ Variable uniformisée
        'infos'         => [
            'savoirfaires' => $annee['savoirfaires'] ?? [], // ✅ Pour le chevron
        ],
    ])

    @if (!empty($annee['savoirfaires']))
        <ul class="collapsible ml2 mb1 transition-all duration-300"
            x-show="isOpen('annee_{{ $annee['uuid'] }}')"
            x-transition.duration.300ms>
            @foreach ($annee['savoirfaires'] as $savoirfaire)
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.savoirfaire', [
                    'parcoursId' => $parcoursId,
                    'savoirfaire' => $savoirfaire,
                    'couleurLigne' => $annee['color'] ?? 'gray' // ✅ Variable uniformisée
                ])
            @endforeach
        </ul>
    @endif
</li>
