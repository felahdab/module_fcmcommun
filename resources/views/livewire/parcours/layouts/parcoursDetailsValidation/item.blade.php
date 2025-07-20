@props([
    'parcoursId', 'type', 'id', 'uuid', 'label', 'libelleLong', 'libelleCourt', 
    'etatValid', 'dateValidation', 'dateProposition', 'valideur', 
    'commentaire', 'commentaireProposition', 'finDeCourse', 'infos'
])


@php
   
   $indentation = match($type){
    'fonctions' => 'ml-0',
    'competences' => 'ml-4',
    'savoirfaires' => 'ml-8',
    'activites' => 'ml-12',
    'default' =>'',
   };

   $isOpen = isset($openSections[$type.'_'.$uuid]) && $openSections[$type.'_'.$uuid];

  
   $isChecked = $etatValid?? false;

   $isProposChecked = $dateProposition?? false;

   
   $duree = (($type === 'activites')||($type === 'savoirfaires') ? $infos['coeff'] :'');

   // Auth 

   
   $sectionId = $type.'_'.$uuid;


@endphp
<div class="flex items-center justify-between mt1 mb1 {{$indentation}}" 


>



    <div class="flex items-center space-x-2">
       
       
           
                {{-- 
                     //trop instable que des soucis lors des cliques
                    <x-filament::input.checkbox
                        wire:click="toggleCheck('{{$type}}','{{$uuid}}');"
                        :checked="isset($this->selectedItems[$type][$uuid])"
                        data-id="{{$type}}-'{{$uuid}}'"
                        onchange="checkboxEtat(event);"
                        class="mr1"
                />  --}}

                

                
            

        
                        
            {{-- Si pas checke --}}
            @if (!$isChecked)

                {{-- Boucle Etat bouton --}}
                @if (isset($selectedItems['gestion'][$type][$uuid]) && $selectedItems['gestion'][$type][$uuid])

                    <x-filament::icon-button
                        size="lg"
                        color="success"
                        {{-- icon="heroicon-c-x-circle" --}}
                        icon="heroicon-o-check-circle"
                        tooltip="Tout décocher"
                        wire:click="toggleCheck('{{$type}}', '{{$uuid}}');"
                       
                        class="tree-checkbox"
                        {{-- x-on:click="setTimeout(() => enforceCheckedState(@entangle('selectedItems').defer), 300)" --}}
                    />

                @else

                    <x-filament::icon-button
                        size="lg"
                        color="gray"
                        icon="heroicon-o-check-circle"
                        tooltip="Tout cocher"
                        wire:click="toggleCheck('{{$type}}', '{{$uuid}}');"
                      
                        
                    />

                @endif
                {{-- Fin boucle etat --}}

                @if(($type === 'activites')||($type === 'savoirfaires'))
                    @if (!$isProposChecked)
                       
                            <x-filament::icon-button
                                size="lg"
                                color="gray"
                                icon="heroicon-c-document-plus"
                                tooltip="Proposition"
                                wire:click="proposModal('{{$type}}', '{{$uuid}}')"
                            /> 
                      
                    @else
                        <x-filament::icon-button
                            size="lg"
                            color="success"
                            icon="heroicon-c-document-plus"
                            tooltip="Proposition le {{$dateProposition }} : {{$commentaireProposition}}"
                        />
                    @endif
                @endif
                
            @else
            
                <x-filament::icon-button
                    size="lg"
                    color="success"
                    icon="heroicon-c-shield-check"
                    tooltip="Validation le {{$dateValidation }} par {{$valideur}}"
                    class="tree-checkbox"
                />

        @if(($type === 'activites')||($type === 'savoirfaires'))
                <x-filament::icon-button
                    size="lg"
                    color="warning"
                    icon="heroicon-s-x-circle"
                    tooltip="Décochez"
                    wire:click="annulModal('{{$type}}', '{{$uuid}}')"
                />

           

            @endif
            @endif
            {{-- Fin Si pas checke --}}
            <strong  class="ml1">{{ $label }} </strong>
     

            
              
              
               
                
        </div>
       
        <div class="flex items-center space-x-2">
            
           

            
            @if(($type === 'activites')||($type === 'savoirfaires'))
            <x-filament::icon-button
                size="lg"
                color="{{ $isChecked ?'success':'gray' }}"
                {{-- icon="heroicon-c-check-badge" --}}
                icon="heroicon-c-information-circle"
                tooltip="Voir les infos "
                wire:click="infosModal('{{$type}}', '{{$uuid}}', '{{ $dateValidation ?? '' }}', '{{ $valideur ?? 'Inconnu' }}', '{{ $commentaire ?? '' }}','{{ $libelleCourt }}','{{ $libelleLong }}','{{$duree}}')"
            />
            @endif

            @if(($type === 'fonctions')||($type === 'competences'))
            {{-- <x-filament::icon-button
                size="lg"
                 color="{{ $isOpen ?'warning':'info' }}"
                tooltip="{{ $isOpen ?'Réduire':'Développer' }}"
                icon="{{ $isOpen ?'heroicon-c-chevron-up':'heroicon-c-chevron-down' }}"
                
                wire:click="toggleSection('{{ $type.'_'.$uuid }}')" 
                

                /> --}}
                <button
                type="button"
                class="inline-flex items-center justify-center w-10 h-10 rounded-full transition-colors duration-200"
                :class="isOpen('{{ $sectionId }}') ? 'success' : 'gray'"
                @click="toggleSection('{{ $sectionId }}')"
                :title="isOpen('{{ $sectionId }}') ? 'Réduire' : 'Développer'"
            >
                <svg 
                    class="w-5 h-5 transition-transform duration-200"
                    :class="isOpen('{{ $sectionId }}') ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

                @endif


            @if (!$finDeCourse)
           
                @endif
        </div>
       
    </div>


    
    
    