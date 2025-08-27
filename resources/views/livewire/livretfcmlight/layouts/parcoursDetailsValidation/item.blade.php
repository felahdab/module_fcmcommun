@props([
    'parcoursId', 'type', 'id', 'uuid', 'label', 'libelleLong', 'libelleCourt', 
    'etatValid', 'dateValidation', 'dateProposition', 'valideur', 
    'commentaire', 'infos'
])

@php
   $indentation = match($type){
    'fonction' => 'ml-0',
    'competence' => 'ml-4',
    'savoirfaire' => 'ml-8',
    'activite' => 'ml-12',
    'default' =>'',
   };

   $isChecked = $etatValid ?? false;

   $isProposChecked = $dateProposition ?? false;
   
   $duree = (($type === 'activite')||($type === 'savoirfaire') ? $infos['coeff'] :'');
   
   $sectionId = $type.'_'.$uuid;
@endphp

<div class="flex items-center justify-between mt1 mb1 {{ $indentation }}" 
    x-data="{
        initial_checked : {{ $etatValid ? 'true':'false' }},
        checked: {{ $etatValid ? 'true':'false' }},
        initial_proposed : {{ $isProposChecked ? 'true' : 'false'}},

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
                @if($isCurrentUser)
                    <div x-show="! checked && ! initial_proposed">
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                @endcan
            </div>

            {{--  L'indicateur de proposition --}}
            <div x-show="! checked && initial_proposed">
                <x-filament::icon-button
                    size="lg"
                    color='success'
                    icon="heroicon-s-chat-bubble-left-right"
                    tooltip="Proposition de validation faite le {{ $dateProposition }} avec le commentaire: {{ $commentaire }}"/>
            </div>

        @endif

        <strong  class="ml1">{{ $label }} </strong>
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