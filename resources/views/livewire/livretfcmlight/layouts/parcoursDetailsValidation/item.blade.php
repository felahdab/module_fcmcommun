@php
   $indentation = match($type){
    'fonction' => 'ml-0',
    'competence' => 'ml-4',
    'savoirfaire' => 'ml-8',
    'activite' => 'ml-12',
    'default' =>'',
   };

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
   $isProposed = !empty($dateProposition);
   $duree = (($type === 'activite')||($type === 'savoirfaire') ? $infos['coeff'] :'');
   $sectionId = $type.'_'.$uuid;
   $tooltipText = !empty($libelleLong) ? $libelleLong : $label;
@endphp

{{-- ✅ CLÉ UNIQUE QUI CHANGE SELON L'ÉTAT --}}
<div class="flex items-center justify-between mt1 mb1 {{ $indentation }} {{ $colorClasses }}"
    wire:key="item-{{ $uuid }}-{{ $isChecked ? 'checked' : 'unchecked' }}-{{ $isProposed ? 'proposed' : 'normal' }}"
    x-data="{
        isSelected() {
            return selectedItems['{{$type}}'] && selectedItems['{{$type}}']['{{$uuid}}'];
        }
    }">

    <div class="flex items-center space-x-2">
       
        {{-- ✅ ICÔNE DE VALIDATION (condition PHP directe) --}}
        @if($isChecked)
            <x-filament::icon-button
                size="lg"
                color='success'
                icon="heroicon-c-shield-check"
                tooltip="Validé le {{ $dateValidation }} par {{ $valideur }}"
                class="tree-checkbox"/>
        @endif

        @if(($type === 'savoirfaire')||($type === 'activite'))
           
            {{-- ✅ BOUTON PROPOSER (si pas validé et pas proposé) --}}
            @if(!$isChecked && !$isProposed && $canPropose)
                <x-filament::icon-button
                    size="lg"
                    icon="heroicon-o-chat-bubble-left-right"
                    x-on:click="a_proposer = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                        message_du_modal = '{{ $label }}';
                        $dispatch('open-modal', { id: 'propos' })"
                    tooltip="Proposer à la validation"/>
            @endif

            {{-- ✅ BOUTON SÉLECTIONNER (si pas validé et pas proposé) --}}
            @if(!$isChecked && !$isProposed)
                @can('fcmcentral::valider_element_parcours')
                    <button
                        type="button"
                        class="tree-checkbox"
                        x-on:click="toggleCheck('{{$sectionId}}')"
                        x-bind:class="isSelected() ? 'text-success' : 'text-gray-600'"
                        x-bind:title="isSelected() ? 'Sélectionné pour validation' : 'Cliquer pour sélectionner'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </button>
                @endcan
            @endif

            {{-- ✅ INDICATEUR EN ATTENTE (si proposé mais pas validé) --}}
            @if(!$isChecked && $isProposed)
            @can('fcmcentral::valider_element_parcours')
            <x-filament::icon-button
            size="lg"
            color='info'
            icon="heroicon-s-clock"
            x-on:click="toggleCheck('{{$sectionId}}')"
            tooltip="En attente depuis le {{ $dateProposition }}. Commentaire: {{ $commentaire }}"/>
                    @else
                <x-filament::icon-button
                    size="lg"
                    color='warning'
                    icon="heroicon-s-clock"
                    tooltip="En attente depuis le {{ $dateProposition }}. Commentaire: {{ $commentaire }}"/>
                    @endcan
                    @endif

        @endif

        <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}">{{ $label }}</strong>
    </div>
       
    <div class="flex items-center space-x-2">
        @if(($type === 'activite')||($type === 'savoirfaire'))
            {{-- BOUTON INFOS --}}
            <x-filament::icon-button
                size="lg"
                color="{{ $isChecked ? 'success':'gray' }}"
                icon="heroicon-c-information-circle"
                tooltip="Voir les infos"
                x-on:click="infos = {
                            'type': '{{$type}}',
                            'uuid': '{{$uuid}}',
                            'libelleCourt': '{{ $libelleCourt }}',
                            'libelleLong': '{{ $libelleLong }}',
                            'duree': '{{$duree}}',
                            'dateValidation': '{{ $dateValidation ?? '' }}',
                            'valideur': '{{ $valideur ?? 'Inconnu' }}',
                            'commentaire': '{{ $commentaire ?? '' }}'
                        };
                        $dispatch('open-modal', { id: 'infosModal'});"
            />
           
            {{-- BOUTON DÉVALIDER (si validé ou proposé) --}}
            @if($isChecked || $isProposed)
                @can('fcmcentral::valider_element_parcours')
                    <x-filament::icon-button
                        size="lg"
                        color="danger"
                        icon="heroicon-s-x-circle"
                        tooltip="Dévalider"
                        x-on:click="a_devalider = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                                    message_du_modal = '{{ $label }}';
                                    $dispatch('open-modal', { id: 'annul' });"
                    />
                @endcan
            @endif
        @endif

        @if(($type === 'fonction')||($type === 'competence'))
            <x-filament::icon-button
                size="lg"
                color="black"
                icon='heroicon-o-chevron-right'
                x-show="! isOpen('{{ $sectionId }}')"
                x-on:click="toggleSection('{{ $sectionId }}')"
            />
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
