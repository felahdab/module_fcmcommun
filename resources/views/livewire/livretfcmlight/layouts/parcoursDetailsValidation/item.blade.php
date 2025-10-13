@php
   $indentation = match($type){
    'annee' => 'ml-0',
    'savoirfaire' => 'ml-8',
    'objectif' => 'ml-12',
    'default' =>'',
   };

   // ✅ UNIFORMISER LES VARIABLES couleurLigne (pas couleurligne)
   $couleurActuelle = $couleurLigne ?? $couleurligne ?? 'gray';

   // ✅ Classes couleur selon couleurLigne uniformisée
   $colorClasses = match($type) {
    'annee' => 'fcm-item-' . $couleurActuelle,
    'savoirfaire' => 'fcm-item-' . $couleurActuelle,
    'objectif' => 'fcm-item-' . $couleurActuelle,
    default => ''
   };

   $textColorClasses = match($type) {
    'annee' => 'fcm-text-' . $couleurActuelle,
    'savoirfaire' => 'fcm-text-' . $couleurActuelle,
    'objectif' => 'fcm-text-' . $couleurActuelle,
    default => ''
   };

   $isChecked = $etatValid ?? false;
   $isProposed = !empty($dateProposition);
   $duree = (($type === 'objectif') ? $infos['coeff'] :'');
   $sectionId = $type.'_'.$uuid;
   $tooltipText = !empty($libelleLong) ? $libelleLong : $label;

   $codeElement ='';
   if ($type === 'objectif') {
    $codeElement= $code ?? $infos['code'] ?? '';
   }

   // ✅ DONNÉES SPÉCIFIQUES AUX SAVOIR-FAIRE
   if ($type === 'savoirfaire') {
       $competenceCode = $infos['competence_code'] ?? 'COMP';
       $competenceColor = $infos['competence_color'] ?? 'gray';
       $savoirfaireTypeCode = $infos['savoirfaire_type_code'] ?? 'C';
       $savoirfaireTypeColor = $infos['savoirfaire_type_color'] ?? 'info';
       $isCompagnonnage = $savoirfaireTypeCode === 'C';

       // ✅ RÉCUPÉRER LES VRAIS OBJECTIFS DEPUIS LES INFOS PASSÉES
       $objectifs = $infos['objectifs_list'] ?? []; // ✅ Nouvelle clé pour la liste
       $totalObjectifs = count($objectifs);
       $objectifsValides = collect($objectifs)->where('etat_valid', true)->count();
       $pourcentage = $totalObjectifs > 0 ? round(($objectifsValides / $totalObjectifs) * 100) : 0;

       // Libellé court tronqué
       $libelleLongTronque = strlen($libelleLong ?? '') > 50 ? substr($libelleLong, 0, 47) . '...' : ($libelleLong ?? '');

       // ✅ Vérifier s'il y a des objectifs
       $hasObjectifs = $isCompagnonnage && $totalObjectifs > 0;
   } else {
       $hasObjectifs = false;
   }
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

        {{-- ✅ ICÔNE DE VALIDATION --}}
        @if($isChecked)
            <x-filament::icon-button
                size="lg"
                color='success'
                icon="heroicon-c-shield-check"
                tooltip="Validé le {{ $dateValidation }} par {{ $valideur }}"
                class="tree-checkbox"/>
        @endif

        @if(($type === 'savoirfaire')||($type === 'objectif'))

            {{-- ✅ BOUTON PROPOSER - UNIQUEMENT POUR LES UTILISATEURS (PAS LES ADMINS) --}}
            @if(!$isChecked && !$isProposed && ($canPropose ?? true))
                @cannot('fcmcentral::valider_element_parcours')
                    <x-filament::icon-button
                        size="lg"
                        icon="heroicon-o-chat-bubble-left-right"
                        x-on:click="a_proposer = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                            message_du_modal = '{{ $label }}';
                            $dispatch('open-modal', { id: 'propos' })"
                        tooltip="Proposer à la validation"/>
                @endcannot
            @endif

            {{-- ✅ BOUTON SÉLECTIONNER - UNIQUEMENT POUR LES ADMINS --}}
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

            {{-- ✅ INDICATEUR EN ATTENTE --}}
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
                @endcannot
            @endif
        @endif

        {{-- ✅ AFFICHAGE SPÉCIAL POUR SAVOIR-FAIRE AVEC BADGES FILAMENT --}}
        @if($type === 'savoirfaire')
            {{-- ✅ Badge compétence Filament --}}
            {{-- <x-filament::badge
                size="sm"
                color="{{ $competenceColor }}"
                style="margin: 0px 4px 0px 4px;">
                {{ $competenceCode }}
            </x-filament::badge> --}}

            {{-- ✅ Badge code savoir-faire --}}
            <x-filament::badge
                size="sm"
                {{-- color="{{ $couleurActuelle }}" --}}
                color="{{ $competenceColor }}"
                style="margin: 0px 4px 0px 4px;">
                {{ $infos['code'] ?? 'SF-' . $uuid }}
               
            </x-filament::badge>

            {{-- ✅ Badge type savoir-faire Filament --}}
            <x-filament::badge
                size="sm"
                color="{{ $savoirfaireTypeColor }}"
                style="margin: 0px 4px 0px 4px;">
                {{ $savoirfaireTypeCode }}
            </x-filament::badge>

            {{-- Libellé tronqué --}}
            <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}">
                {{ $libelleLongTronque }}
            </strong>

            {{-- ✅ Pourcentage d'objectifs validés CORRIGÉ --}}
            @if ( $isCompagnonnage)
            <div class="flex items-center ml-2 space-x-2">
                @if($totalObjectifs > 0)
                    {{-- ✅ Barre de progression avec couleur de l'année --}}
                    <div class="w-20 bg-gray-200 rounded-full h-3 border border-gray-300">
                        <div class="h-3 rounded-full progress-fill-{{ $couleurActuelle }} transition-all duration-300"
                             style="width: {{ $pourcentage }}%"></div>
                    </div>
                   
                    {{-- ✅ Badge avec le pourcentage --}}
                    <x-filament::badge
                        size="xs"
                        color="{{ $pourcentage == 100 ? 'success' : ($pourcentage > 50 ? 'warning' : 'danger') }}">
                        {{ $pourcentage }}%
                    </x-filament::badge>
                   
                    {{-- ✅ Détail (validés/total) --}}
                    <span class="text-xs text-gray-500 font-medium">
                        ({{ $objectifsValides }}/{{ $totalObjectifs }})
                    </span>
                @else
                    {{-- ✅ Cas où il n'y a pas d'objectifs --}}
                    <x-filament::badge
                        size="xs"
                        color="gray">
                        Aucun objectif
                    </x-filament::badge>
                @endif
            </div>
            @endif

        @elseif($type === 'annee')
            {{-- ✅ Badge année avec couleur SANS border doublée --}}
            <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}">
                {{ $label }}
            </strong>

        @else
            {{-- Affichage normal pour les objectifs --}}
            <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}"> {{  $codeElement }} {{ $label }}</strong>
        @endif

    </div>

    <div class="flex items-center space-x-2">
        @if(($type === 'objectif')||($type === 'savoirfaire'))
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

            {{-- ✅ BOUTON DÉVALIDER - UNIQUEMENT POUR LES ADMINS --}}
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

        {{-- ✅ CHEVRON POUR ANNÉES ET SAVOIR-FAIRE AVEC CONTENU --}}
        @if(
            ($type === 'annee' && !empty($infos['savoirfaires'] ?? [])) ||
            ($type === 'savoirfaire' && $hasObjectifs)
        )
            <x-filament::icon-button
                size="lg"
                color="gray"
                icon='heroicon-o-chevron-right'
                x-show="! isOpen('{{ $sectionId }}')"
                x-on:click="toggleSection('{{ $sectionId }}')"
                tooltip="{{ $type === 'annee' ? 'Voir les savoir-faire' : 'Voir les objectifs' }} ({{ $type === 'annee' ? count($infos['savoirfaires'] ?? []) : $totalObjectifs }})"
            />
            <x-filament::icon-button
                size="lg"
                color="gray"
                icon='heroicon-o-chevron-down'
                x-show="isOpen('{{ $sectionId }}')"
                x-on:click="toggleSection('{{ $sectionId }}')"
                tooltip="{{ $type === 'annee' ? 'Masquer les savoir-faire' : 'Masquer les objectifs' }}"
            />
        @endif
       
    </div>
</div>
