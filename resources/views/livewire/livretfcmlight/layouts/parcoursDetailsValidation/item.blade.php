@php
   $indentation = match($type){
    'fonction' => 'ml-0',
    'competence' => 'ml-4',
    'savoirfaire' => 'ml-8',
    'activite' => 'ml-12',
    'default' =>'',
   };

   // Codes couleur par type
   $colorClasses = match($type) {
    'fonction' => 'fcm-fonction',
    'competence' => 'fcm-competence',
    'savoirfaire' => 'fcm-savoirfaire',
    'activite' => 'fcm-activite',
    default => ''
   };

   $textColorClasses = match($type) {
    'fonction' => 'fcm-fonction-text',
    'competence' => 'fcm-competence-text',
    'savoirfaire' => 'fcm-savoirfaire-text',
    'activite' => 'fcm-activite-text',
    default => ''
   };

   $isChecked = $etatValid ?? false;
   $isProposed = !empty($dateProposition); // Correction: vérifier si une date existe
   
   $duree = (($type === 'activite')||($type === 'savoirfaire') ? $infos['coeff'] :'');
   
   $sectionId = $type.'_'.$uuid;

   // Utiliser libelle_long pour le tooltip s'il existe, sinon libelle_court
   $tooltipText = !empty($libelleLong) ? $libelleLong : $label;

  
@endphp

<div class="flex items-center justify-between mt1 mb1 {{ $indentation }} {{ $colorClasses }}" 

    x-data="{
        initial_checked : {{ $isChecked ? 'true':'false' }},
        checked: {{ $isChecked ? 'true':'false' }},
        initial_proposed : {{ $isProposed ? 'true' : 'false'}},

        isSelected() {
            return selectedItems['{{$type}}'] && selectedItems['{{$type}}']['{{$uuid}}'];
        }
    }"
    
>

    <div class="flex items-center space-x-2">
       
        <div x-show="initial_checked">
            <x-filament::icon-button
            size="lg"
            color='success'
            icon="heroicon-c-shield-check"
            tooltip="Validation le {{$dateValidation }} par {{$valideur}}"
            class="tree-checkbox"/>
        </div>

        @if(($type === 'savoirfaire')||($type === 'activite'))
            {{--  Ce qui s'affiche quand l'élément n'est pas encore validé --}}
            <div x-show="!checked">
                {{--  Le bouton pour proposer l'élément à la validation --}}
                @if($canPropose)
                    <div x-show="!checked && !initial_proposed">
                        <x-filament::icon-button 
                            size="lg"
                            icon="heroicon-o-chat-bubble-left-right"
                            x-on:click="a_proposer = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' }; 
                                message_du_modal = '{{ $label }}';
                                $dispatch('open-modal', { id: 'propos' })"
                            tooltip="Proposer à la validation"/>
                    </div>
                @endif

                {{--  Le bouton pour sélectionner l'élément en vue de la validation. --}}
                @can('fcmcentral::valider_element_parcours')
                    <button
                    type="button"
                    class="tree-checkbox"
                    x-on:click="toggleCheck('{{$sectionId}}')"
                    x-bind:class="selectedItems['{{$type}}'] && selectedItems['{{$type}}']['{{$uuid}}'] ? 'text-success' : 'text-gray-600' "
                    x-bind:title="selectedItems['{{$type}}'] && selectedItems['{{$type}}']['{{$uuid}}'] ? 'Validé' : 'À valider'">
                        <svg xmlns=http://www.w3.org/2000/svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </button>
                @endcan
            </div>

            {{--  L'indicateur de proposition (en attente de validation) --}}
            <div x-show="!checked && initial_proposed">
                <x-filament::icon-button
                    size="lg"
                    color='warning'
                    icon="heroicon-s-clock"
                    tooltip="En attente de validation depuis le {{ $dateProposition }}. Commentaire: {{ $commentaire }}"/>
            </div>

        @endif

        <strong  class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}">{{ $label }} </strong>
    </div>
       
    <div class="flex items-center space-x-2">

        @if(($type === 'activite')||($type === 'savoirfaire'))
            <x-filament::icon-button
                size="lg"
                color="{{ $isChecked ? 'success':'gray' }}"
                icon="heroicon-c-information-circle"
                tooltip="Voir les infos "
                x-on:click="infos = {'type'  : '{{$type}}', 
                            'uuid'           : '{{$uuid}}', 
                            'libelleCourt'   : '{{ $libelleCourt }}', 
                            'libelleLong'    : '{{ $libelleLong }}' ,
                            'duree'          : '{{$duree}}',
                            'dateValidation' : '{{ $dateValidation ?? '' }}',
                            'valideur'       : '{{ $valideur ?? 'Inconnu' }}',
                            'commentaire'    : '{{ $commentaire ?? '' }}'
                        };
                        $dispatch('open-modal', { id: 'infosModal'});"
            />
            {{--  Le bouton pour désactiver l'élément --}}
            @can('fcmcentral::valider_element_parcours')
                <x-filament::icon-button
                    size="lg"
                    color="danger"
                    icon="heroicon-s-x-circle"
                    tooltip="Dévalider "
                    x-show='initial_checked || initial_proposed'
                    x-on:click="a_devalider = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' }; 
                                message_du_modal = '{{ $label }}';
                                $dispatch('open-modal', { id: 'annul' });"
                />
            @endcan
        @endif

        @if(($type === 'fonction')||($type === 'competence'))
            {{--  Le bouton pour développer l'élément --}}
            <x-filament::icon-button
                size="lg"
                color="black"
                icon='heroicon-o-chevron-right'
                x-show="! isOpen('{{ $sectionId }}')"
                x-on:click="toggleSection('{{ $sectionId }}')"
            />
            {{--  Le bouton pour refermer l'élément --}}
            <x-filament::icon-button
                size="lg"
                color="black"
                icon='heroicon-o-chevron-down'
                x-show="isOpen('{{ $sectionId }}')"
                x-on:click="toggleSection('{{ $sectionId }}')"
            />
        @endif
    </div>
</div>
