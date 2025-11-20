@php
    // --- Normalisation des infos ---
    if (empty($infos) || !is_array($infos)) {
        $infos = [];
    }

    // Uniformiser les clés selon le type
    if ($type === 'affectation') {
        $infos['competences'] = $infos['competences'] ?? [];
    }
    if ($type === 'competence') {
        $infos['competenceSavoirfaires'] = $infos['competenceSavoirfaires'] ?? [];
    }
    if ($type === 'savoirfaire') {
        $infos['objectifs_list'] = $infos['objectifs_list'] ?? [];
    }

    // Variables communes
    $indentation = match($type){
        'affectation' => 'ml-0',
        'competence' => 'ml-4',
        'savoirfaire' => 'ml-8',
        'objectif' => 'ml-12',
        default => '',
    };
    $couleurActuelle = $couleurLigne ?? 'gray';
    $colorClasses = 'fcm-item-' . $couleurActuelle;
    $textColorClasses = 'fcm-text-' . $couleurActuelle;

    $isChecked = $etatValid ?? false;
    $isProposed = !empty($dateProposition);
    $sectionId = $type.'_'.$uuid;
    $tooltipText = !empty($libelleLong) ? $libelleLong : $label;

    // ✅ CODE BADGE POUR TOUS LES TYPES
    $codeElement = $code ?? match($type) {
        'affectation' => "AFF{$id}",
        'competence' => "C{$id}",
        'objectif' => "OBJ{$id}",
        default => $infos['code'] ?? ''
    };

    // Spécifique aux savoir-faire
    if ($type === 'savoirfaire') {
        $competenceCode = $infos['competence_code'] ?? 'COMP';
        $competenceColor = $infos['competence_color'] ?? 'gray';
        $savoirfaireTypeCode = $infos['savoirfaire_type_code'] ?? 'C';
        $savoirfaireTypeColor = $infos['savoirfaire_type_color'] ?? 'info';
        $isCompagnonnage = $savoirfaireTypeCode === 'C';

        $objectifs = $infos['objectifs_list'] ?? [];
        $totalObjectifs = count($objectifs);
        $objectifsValides = collect($objectifs)->where('etat_valid', true)->count();
        $pourcentage = $totalObjectifs > 0 ? round(($objectifsValides / $totalObjectifs) * 100) : 0;

        $libelleLongTronque = strlen($libelleLong ?? '') > 70 ? substr(str_replace(['&amp;#39;','$apos;'],"'",$libelleLong), 0, 67) . '...' : ($libelleLong ?? '');
        $hasObjectifs = $isCompagnonnage && $totalObjectifs > 0;
    } else {
        $hasObjectifs = false;
    }

    // Déterminer si on doit afficher le chevron
    $showChevron = (
        ($type === 'affectation' && !empty($infos['competences'])) ||
        ($type === 'competence' && !empty($infos['competenceSavoirfaires'])) ||
        ($type === 'savoirfaire' && $hasObjectifs)
    );
@endphp

<div class="flex items-center justify-between mt1 mb1 {{ $indentation }} {{ $colorClasses }}"
     wire:key="item-{{ $uuid }}-{{ $isChecked ? 'checked' : 'unchecked' }}-{{ $isProposed ? 'proposed' : 'normal' }}"
     x-data="{
         isSelected() {
             return selectedItems['{{$type}}'] && selectedItems['{{$type}}']['{{$uuid}}'];
         }
     }">

    <div class="flex items-center space-x-2">
        {{-- Icône validation --}}
        @if($isChecked)
            <x-filament::icon-button
                size="lg"
                color='success'
                icon="heroicon-c-shield-check"
                tooltip="Validé le {{ $dateValidation }} par {{ $valideur }}"
                class="tree-checkbox"/>
        @endif

        {{-- Boutons proposer / sélectionner / attente (savoirfaire + objectif uniquement) --}}
        @if(($type === 'savoirfaire')||($type === 'objectif'))
            @if(!$isChecked && !$isProposed  )
                @can('fcmcentral::livret.proposer')
                   <x-filament::icon-button
                        size="lg"
                        icon="heroicon-o-chat-bubble-left-right"
                        x-on:click="a_proposer = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                            message_du_modal = '{{ $label }}';
                            $dispatch('open-modal', { id: 'propos' })"
                        tooltip="Proposer à la validation"/>
                @endcan
            @endif

            @if(!$isChecked && !$isProposed )
                @can('fcmcentral::livret.valider')
    
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

            @if(!$isChecked && $isProposed )
                @can('fcmcentral::livret.valider')
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

        {{-- Libellé selon type --}}
        @if($type === 'savoirfaire')
            <x-filament::badge size="sm" color="{{ $couleurLigne }}" style="margin: 0px 4px 0px 4px;">
                {{ $codeElement }}
            </x-filament::badge>
            <x-filament::badge size="sm" color="{{ $savoirfaireTypeColor }}" style="margin: 0px 4px 0px 4px;">
                {{ $savoirfaireTypeCode }}
            </x-filament::badge>
            <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}">{{ $libelleLongTronque }}</strong>

            @if ($isCompagnonnage)
                <div class="flex items-center ml-2 space-x-2">
                    @if($totalObjectifs > 0)
                        <div class="w-20 bg-gray-200 rounded-full h-3 border border-gray-300">
                            <div class="h-3 rounded-full progress-fill-{{ $couleurActuelle }} transition-all duration-300"
                                 style="width: {{ $pourcentage }}%"></div>
                        </div>
                        <x-filament::badge
                            size="xs"
                            color="{{ $pourcentage == 100 ? 'success' : ($pourcentage > 50 ? 'warning' : 'danger') }}">
                            {{ $pourcentage }}%
                        </x-filament::badge>
                        <span class="text-xs text-gray-500 font-medium">({{ $objectifsValides }}/{{ $totalObjectifs }})</span>
                    @else
                        <x-filament::badge size="xs" color="gray">Aucun objectif</x-filament::badge>
                    @endif
                </div>
            @endif
        @else
            {{-- ✅ BADGE CODE POUR AFFECTATION / COMPETENCE / OBJECTIF --}}
            <x-filament::badge size="sm" color="{{ $couleurActuelle }}" style="margin: 0px 4px 0px 4px;">
                {{ $codeElement }}
            </x-filament::badge>
            <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}">
                {{ str_replace(['&amp;#39;','$apos;'],"'",$label) }} 
            </strong>

        @endif
    </div>



     {{-- ✅ A DROITE AFFICHAGE --}}
    <div class="flex items-center space-x-2">


            {{-- ✅ AFFICHAGE HEURES POUR AFFECTATION --}}
            @if($type === 'affectation')
                <div class="flex items-center ml-2 space-x-2">
                    <x-filament::badge size="xs" color="gray">
                        {{ $heuresValidees ?? 0 }}h / {{ $heuresTotal ?? 0 }}h
                    </x-filament::badge>
                    @if(($tauxHeures ?? 0) > 0)
                        <x-filament::badge
                            size="xs"
                            color="{{ $tauxHeures == 100 ? 'success' : ($tauxHeures > 50 ? 'warning' : 'danger') }}">
                            {{ $tauxHeures }}%
                        </x-filament::badge>
                    @endif
                </div>
            @endif

        {{-- Boutons infos / dévalider --}}
        @if(($type === 'objectif')||($type === 'savoirfaire'))
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
                            'dateValidation': '{{ $dateValidation ?? '' }}',
                            'valideur': '{{ $valideur ?? 'Inconnu' }}',
                            'commentaire': '{{ $commentaire ?? '' }}'
                        };
                        $dispatch('open-modal', { id: 'infosModal'});"
            />

            @if($isChecked || $isProposed )
                @can('fcmcentral::livret.annuler')
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

        {{-- Chevron --}}
        @if($showChevron)
            <x-filament::icon-button
                size="lg"
                color="gray"
                icon='heroicon-o-chevron-right'
                x-show="! isOpen('{{ $sectionId }}')"
                x-on:click="toggleSection('{{ $sectionId }}')"
                tooltip="Voir les éléments"
            />
            <x-filament::icon-button
                size="lg"
                color="gray"
                icon='heroicon-o-chevron-down'
                x-show="isOpen('{{ $sectionId }}')"
                x-on:click="toggleSection('{{ $sectionId }}')"
                tooltip="Masquer les éléments"
            />
        @endif
    </div>
</div>
