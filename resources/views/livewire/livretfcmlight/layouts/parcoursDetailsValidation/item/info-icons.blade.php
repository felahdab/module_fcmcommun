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
                       'type': '{{ $type }}',
                       'uuid': '{{ $uuid }}',
                       'estValide': {{ $isChecked ? 'true' : 'false' }},
                       'intitule': '{{ $popupInfos }}',
                       'dateValidation': '{{ $dateValidation ?? '' }}',
                       'dateProposition': '{{ $dateProposition ?? '' }}',
                       'valideur': '{{ $valideur ?? 'Inconnu' }}',
                       'commentaire': '{{ $commentaire ?? '' }}',
                       'commentaireProposition': '{{ $commentaireProposition ?? '' }}',
                       'pieceJointeUrl': '{{ $pieceJointeUrl }}'
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

