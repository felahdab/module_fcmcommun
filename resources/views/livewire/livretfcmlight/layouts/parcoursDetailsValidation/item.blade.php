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

    // Bug pour les badges
    $customStyle="margin: 0px 4px 0px 4px;";
    if ($couleurLigne == "purple"){
        $customStyle .= "border-color: #800080;  
        background-color: rgba(128, 0, 128, 0.15); 
        color:#800080;";
    }
    if ($couleurLigne == "brown"){
        $customStyle .= "border-color: #8B4513;  
        background-color: rgba(139, 69, 19, 0.15); 
        color:#8B4513;;";
    }

    $isChecked = $etatValid ?? false;
    $isProposed = !empty($dateProposition);
    $sectionId = $type.'_'.$uuid;
    $tooltipText = !empty($libelleLong) ? $libelleLong : $label;


    // ✅ CODE BADGE POUR TOUS LES TYPES
    $codeElement = $code ?? match($type) {
        'affectation' => "AFF{$uuid}",
        'competence' => "C{$uuid}",
        'objectif' => "OBJ{$uuid}",
        default => $infos['code'] ?? ''
    };

    // PopUp Infos
   
    $popupInfos = $libelleLong;

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

        $libelleLongTronque = (str_replace(['&amp;#39;','$apos;'],"'",$libelleLong) ?? '');
        $hasObjectifs = $isCompagnonnage && $totalObjectifs > 0;

        $popupInfos = ($infos['savoirfaire_type_code'] <> 'C')
        ? $intitule
        : $libelleLong
        ;
    

    } else {
        $hasObjectifs = false;
    }

    // Vérifier si le marin est gelé/sedimentation
   
    $marinBloque = !$this->marinPeutProposerOuValider();


     // VÉRIFIER SI LE SAVOIR-FAIRE DOIT ÊTRE GRISÉ
     if ($type === 'savoirfaire') {
        $estGrise = in_array($uuid, $savoirfairesAGriser ?? []);
    } elseif ($type === 'objectif') {
        $estGrise = in_array($uuid, $objectifsAGriser ?? []);
    }


         //  Pour les savoir-faires grisés, vérifier s'ils ont des objectifs à afficher
    $hasObjectifsVisibles = false;
    if ($type === 'savoirfaire' && $hasObjectifs) {
        //  FIX: Vérifier qu'il y a bien des objectifs dans $infos
        $hasObjectifsVisibles = !empty($infos['objectifs']) || !empty($infos['objectifs_list']);
    }


    // Déterminer si on doit afficher le chevron
    $showChevron = (
        ($type === 'affectation' && !empty($infos['competences'])) ||
        ($type === 'competence' && !empty($infos['competenceSavoirfaires'])) ||
        ($type === 'savoirfaire' && $hasObjectifs)
    );


   // Les ACCENTS et apostrophes

    $commentairePropositionClean =$commentaireProposition ?? '';
    
    $commentaireClean = $commentaire ?? '';
    $tooltipTextClean = $tooltipText ?? '';
    $labelClean = $label ?? '';
    $popupInfosClean = $popupInfos ?? '';

    
    $tooltipPropositionAttente = 'En attente de validation depuis le ' . ($dateProposition ?? '') .
    '. Commentaire:' .  $this->cleanForTooltip($commentairePropositionClean);
   
    $tooltipPropositionModifie = 'Modifier votre proposition du ' . ($dateProposition ?? '') .
     '. Commentaire: ' .  $this->cleanForTooltip($commentairePropositionClean);

     $labelForJs = addslashes($labelClean);
    
@endphp




<div class="flex items-center justify-between mt1 mb1 {{ $indentation }} 
@if(($type === 'savoirfaire')||($type === 'objectif'))
{{ $isChecked ?  $colorClasses : ($estGrise ? 'fcm-item-grayed' : 'fcm-item-gray')  }}
@else
{{  $colorClasses }}
@endif
"
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
                {{-- tooltip="Validé le {{ $dateValidation }} par {{ $valideur }}" --}}
                class="tree-checkbox"/>
        @endif

        {{-- Boutons proposer / sélectionner / attente (désactivés si grisé) --}}
        @if(($type === 'savoirfaire')||($type === 'objectif'))
        {{-- BOUTON PROPOSER (si pas validé, pas proposé, pas grisé, pas bloqué) --}}
            @if(!$isChecked && !$isProposed && !$estGrise && !$marinBloque)
                @if($this->canProposer()) 
                   <x-filament::icon-button
                        size="lg"
                        {{-- color='fi-color-jaunepale'
                        style="color: #FFFF00;" --}}
                        color='gray'
                        icon="heroicon-o-shield-check"
                        x-on:click="a_proposer = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                            message_du_modal = '{{  $labelForJs }}';
                            $dispatch('open-modal', { id: 'propos' })"
                        tooltip="Proposer à la validation"/>
                @endif
            @endif
        {{-- BOUTON VALIDER MENTOR (si pas validé, pas proposé, pas grisé, pas bloqué) --}}
            @if(!$isChecked && !$isProposed && !$estGrise && !$marinBloque)
                @if($this->canValider()) 
                    <button
                        type="button"
                        class="tree-checkbox"
                        x-on:click="toggleCheck('{{$sectionId}}');modification_proposition = false;"
                        x-bind:class="isSelected() ? 'text-success' : 'text-gray-600'"
                        x-bind:title="isSelected() ? 'Dévalider' : 'Valider'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </button>
                @endif
            @endif
        {{-- ✅ BOUTON EN ATTENTE (si proposé mais pas validé) --}}           
        @if(!$isChecked && $isProposed)
        {{-- Mentor peut valider/refuser --}}
        @if($this->canValider() && !$marinBloque)
           <x-filament::icon-button
                size="lg"
                color='fi-color-orangefort'
                style="color: #FF6F00;"
                icon="heroicon-c-shield-check"
                x-on:click="a_proposition_mentor = { 
                    'type' : '{{ $type }}', 
                    'uuid' : '{{ $uuid }}' 
                };
                message_du_modal = '{{  $labelForJs }}';
                commentaire_proposition_existant = '{{ $commentaireProposition }}';
                commentaire = '';
                $dispatch('open-modal', { id: 'validMentor' })"
                tooltip="{{ ($tooltipPropositionAttente) }}"
                />
        
        {{-- Marin peut modifier sa proposition --}}
        @elseif($this->userEstLeMarin() && !$marinBloque)
            <x-filament::icon-button
                size="lg"
                color='fi-color-orangefort'
                style="color: #FF6F00;"
                icon="heroicon-c-shield-check"
                x-on:click="a_proposer = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                    message_du_modal = '{{  $labelForJs }}';
                    commentaire = '{{ $commentairePropositionClean}}';
                    modification_proposition = true;
                    $dispatch('open-modal', { id: 'propos' })"
                tooltip="{{ ($tooltipPropositionModifie) }}" />
        
        {{-- Sinon juste afficher l'icône --}}
        @else
            <x-filament::icon-button
                size="lg"
                color='fi-color-orangefort'
                style="color: #FF6F00;"
                icon="heroicon-c-shield-check"
                tooltip="{{ ($tooltipPropositionModifie) }}"/>
        @endif
    @endif
    
        @endif


        {{-- Libellé selon type --}}
        @if($type === 'savoirfaire')
           {{-- ✅ BADGE CODE POUR SAVOIR FAIRE ET TYPE STAGE ANCIEN --}}
           
           {{-- ✅ BADGE CODE POUR TYPE STAGE  --}}
           <x-filament::badge size="sm" color="{{ $savoirfaireTypeColor }}" 
           style="margin: 0px 4px 0px 4px;
          
           ">
                  {{ $savoirfaireTypeCode }}
              </x-filament::badge>
      
           
  
            <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}"> {{ $libelleLongTronque }}</strong>

            
        @else
            {{-- ✅ BADGE CODE POUR AFFECTATION / COMPETENCE / OBJECTIF ANCIEN --}}
           
            <strong class="ml1 {{ $textColorClasses }}" title="{{ $tooltipText }}">
                {{ str_replace(['&amp;#39;','$apos;'],"'",$label) }} 
            </strong>

        @endif
    </div>



     {{-- ✅ A DROITE AFFICHAGE --}}
    <div class="flex items-center space-x-2">

         {{-- Code --}}
         {{-- ✅ BADGE CODE  --}}
         <x-filament::badge  color="{{ $couleurLigne }}" style="{{ $customStyle }}">
            {{ $codeElement }} 
        </x-filament::badge>

        @if($type === 'savoirfaire')
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
        
            @endif


            {{-- ✅ AFFICHAGE HEURES POUR AFFECTATION --}}
            {{-- 
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
            --}}

            

        {{-- Boutons infos / dévalider --}}
        @if(($type === 'objectif')||($type === 'savoirfaire'))
            <x-filament::icon-button
                size="lg"
                color="{{ $isChecked ? 'success':'gray' }}"
                icon="heroicon-c-information-circle"
                tooltip="Voir les infos"
                x-on:click="infos = {
                            
                            
                            'intitule': '{{ $popupInfos }}',
                            'dateValidation': '{{ $dateValidation ?? '' }}',
                            'dateProposition': '{{ $dateProposition ?? '' }}',
                            'valideur': '{{ $valideur ?? 'Inconnu' }}',
                            'commentaire': '{{ $commentaire ?? '' }}',
                            'commentaireProposition': '{{ $commentaireProposition ?? '' }}'
                        };
                        $dispatch('open-modal', { id: 'infosModal'});"
            />

            {{-- @if($isChecked || $isProposed )
                @if($this->canAnnuler())
                    <x-filament::icon-button
                        size="lg"
                        color="danger"
                        icon="heroicon-s-x-circle"
                        tooltip="Dévalider"
                        x-on:click="a_devalider = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                                    message_du_modal = '{{ $labelForJs }}';
                                    $dispatch('open-modal', { id: 'annul' });"
                    />
                @endif
            @endif --}}

            {{-- ✅ ANNULER PROPOSITION (marin uniquement, si proposé mais pas validé) --}}
                @if($isProposed && !$isChecked)
                    @if($this->userEstLeMarin() || $this->canAnnuler())
                        <x-filament::icon-button
                            size="lg"
                            color="warning"
                            icon="heroicon-s-x-circle"
                            tooltip="Annuler ma proposition"
                            x-on:click="a_devalider = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                                        message_du_modal = '{{ $labelForJs }}';
                                        $dispatch('open-modal', { id: 'annul' });"
                        />
                    @endif
                @endif

                {{-- ✅ DÉVALIDER (mentor/admin uniquement, si validé) --}}
                @if($isChecked)
                    @if($this->canDevalider())
                        <x-filament::icon-button
                            size="lg"
                            color="danger"
                            icon="heroicon-s-x-circle"
                            tooltip="Dévalider"
                            x-on:click="a_devalider = { 'type' : '{{ $type }}', 'uuid' : '{{ $uuid }}' };
                                        message_du_modal = '{{ $labelForJs }}';
                                        $dispatch('open-modal', { id: 'annul' });"
                        />
                    @endif
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
