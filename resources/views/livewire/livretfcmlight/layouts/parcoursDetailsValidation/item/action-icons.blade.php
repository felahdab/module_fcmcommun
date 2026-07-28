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
