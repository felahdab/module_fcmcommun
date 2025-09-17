@props(['parcoursId', 'savoirFaire'])

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'    => $parcoursId,
        'type'          => 'savoirfaire',
        'id'            => $savoirFaire['id'],
        'uuid'          => $savoirFaire['uuid'],
        'label'         => $savoirFaire['libelle_court'],
        //'libelleLong'   => $savoirFaire['libelle_long'],
        'libelleLong'   => $this->escapeForTooltip($savoirFaire['libelle_long'] ?? ''),
        'libelleLongJs' => $this->escapeForJs($savoirFaire['libelle_long'] ?? ''),
        'libelleCourt'  => $savoirFaire['libelle_court'],
        'etatValid'     => $savoirFaire['etat_valid'],
        'dateValidation'=> $savoirFaire['date_validation'],
        'dateProposition'=> $savoirFaire['date_proposition'],
        'valideur'      => $savoirFaire['valideur'],
        'commentaire'   => $savoirFaire['commentaire'],
        'infos'         => [
            'coeff' => $savoirFaire['coeff'] ?? null,
            'duree' => $savoirFaire['duree'] ?? null,
        ],
    ])

    @if (!empty($savoirFaire['activites']))
        <ul class="collapsible ml2 mb1">
            @foreach ($savoirFaire['activites'] as $activite)
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.activite', [
                    'parcoursId' => $parcoursId,
                    'activite' => $activite,
                ])
            @endforeach
        </ul>
    @endif
</li>
