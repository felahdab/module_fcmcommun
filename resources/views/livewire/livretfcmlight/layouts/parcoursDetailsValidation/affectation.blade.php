@props(['parcoursId', 'affectation'])

@php
    // ✅ Récupération des heures calculées
    $heuresTotal = $affectation['heures_total'] ?? 0;
    $heuresValidees = $affectation['heures_validees'] ?? 0;
    $heuresRestantes = $affectation['heures_restantes'] ?? 0;
    $tauxHeures = $affectation['taux_heures'] ?? 0;
@endphp

<li>
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item', [
        'parcoursId'      => $parcoursId,
        'type'            => 'affectation',
        //'id'              => $affectation['id'],
        'uuid'            => $affectation['uuid'],
        'code'            => $affectation['code'],
        'label'           => $affectation['libelle_court'],
        'libelleLong'     => $this->escapeForTooltip($affectation['libelle_long'] ?? ''),
        'libelleLongJs'   => $this->escapeForJs($affectation['libelle_long'] ?? ''),
        'libelleCourt'    => $affectation['libelle_court'],
        'etatValid'       => $affectation['etat_valid'] ?? false,
        'dateValidation'  => $affectation['date_validation'] ?? '',
        'dateProposition' => $affectation['date_proposition'] ?? '',
        'valideur'        => $affectation['valideur'] ?? '',
        'commentaire'     => $affectation['commentaire'] ?? '',
        'couleurLigne'    => $affectation['color'] ?? 'gray',
        'heuresTotal'     => $heuresTotal,
        'heuresValidees'  => $heuresValidees,
        'heuresRestantes' => $heuresRestantes,
        'tauxHeures'      => $tauxHeures,
        'infos'           => [
            'competences' => $affectation['competences'] ?? [],
        ],
        
    ])

    @if (!empty($affectation['competences']))
      {{--  <ul class=" ml2 mb1 transition-all duration-300" --}}
          
                 <ul class="collapsible ml2 mb1 transition-all duration-300"
               
            x-transition.duration.300ms>
            @foreach ($affectation['competences'] as $competence)
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.competence', [
                    'parcoursId'   => $parcoursId,
                    'competence'   => $competence,
                    
                ])
            @endforeach
        </ul>
    @endif
</li>
