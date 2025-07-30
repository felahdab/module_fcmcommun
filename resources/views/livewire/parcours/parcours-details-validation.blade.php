<x-filament::card>
{{-- Initialisation Alpine.js pour le parcours --}}
<div x-data="parcoursManager()" x-init="initSections()">
    @if (!empty($parcours))
        <form>

            @foreach ($parcours as $parcoursItem)
        
                {{-- <h4 class="text-md font-bold">{{ $parcoursItem['libelle_court'] }}</h4>
                <p class="text-sm">Taux Global : {{ $parcoursItem['taux_global'] ?? '0' }}%</p>
                <p class="text-sm">Taux Stage : {{ $parcoursItem['taux_stage'] ?? 'N/A' }}</p>
                <p class="text-sm">Taux Activité : {{ $parcoursItem['taux_activite'] ?? 'N/A' }}</p>
                <p class="text-sm">Détails du parcours :</p>
                --}}
                <div class="flex items-center justify-between mt1 mb1 " >
                <h3 class="text-2xl font-bold  mt1 mb1">{{ $parcoursItem['parcours']['libelle_long'] }}</h3>
                <div class="flex items-center space-x-2">

                        {{-- Bouton AValiderProposition --}}
                        @if(!empty($selectedItems['aValiderPropos']))
                        <x-filament::button wire:click="$dispatch('open-modal', { id: 'aValiderModal' })" 
                        id="openAValiderModal" color="info" 
                        data-modal-target="aValiderModal"
                        >Voir les Propositions</x-filament::button>
                        @endif

                        {{-- Bouton Restore --}}
                        <x-filament::icon-button
                        icon="heroicon-m-arrow-uturn-left"
                        tooltip="Restore Data"
                        color="warning"
                        size="lg"
                        wire:click="$dispatch('open-modal', { id: 'restoreModal' })"
                        class="mr1"
                        />

                        {{-- Bouton Reboot --}}
                        <x-filament::icon-button
                        icon="heroicon-m-arrow-path"
                        tooltip="Reboot Data"
                        size="lg"
                        color="danger"
                        wire:click="$dispatch('open-modal', { id: 'rebootModal' })"
                        />




                    </div>
                </div>
                    
                <ul>
                    @foreach ($parcoursItem['parcours']['fonctions'] as $fonction)
                        @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.fonction', [
                            'parcoursId' => $parcoursItem['id'],
                            'fonction' => $fonction,
                            'openSections' => $openSections,
                        ])
                    @endforeach
                </ul>
                
            @endforeach

            <!-- Bouton Submit -->
            <div class="mt-6">
                <x-filament::button wire:click="$dispatch('open-modal', { id: 'sauve' })" >
               Sauvegarder
            </x-filament::button>
            </div>
        </form>
    @else
        <p>Aucun parcours Sérialisé trouvé pour ce marin</p>
    @endif

    {{-- <div class="mt-6">
        <pre class="bg-gray-400 p-4 rounded">
            {{ json_encode($selectedItems , JSON_PRETTY_PRINT)}}
           
        </pre>
    </div>  --}}
    
  
 
    <!-- Modal Sauve -->
    @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.modal')
</div>

    @push('styles')
    @include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.styles')
    @endpush

  
   


    
</x-filament::card>

{{-- @push('scripts')
@include('fcmcommun::livewire.parcours.layouts.parcoursDetailsValidation.scripts')
@endpush --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.getElementById('openAValiderModal')?.click();
        }, 0);
    });

   

    
    function parcoursManager() {
        return {
            sections: {},
           
            selectedItems:{},
            
            toggleSection(sectionId) {
                this.sections[sectionId] = !this.sections[sectionId];
                this.saveToStorage();
            },
            
            isOpen(sectionId) {
                return this.sections[sectionId] || false;
            },
            
            saveToStorage() {
                localStorage.setItem('parcours_sections_state', JSON.stringify(this.sections));
            },

            toggleCheck(sectionId) {
                const [type, uuid] = sectionId.split('_');
                if (!this.selectedItems[type]) {
                    this.selectedItems[type] = {};
                }

                // Cas savoirfaire : gestion enfants
                if (type === 'savoirfaires') {
                    const enfants = this.savoirfaireChildren && this.savoirfaireChildren[uuid] ? this.savoirfaireChildren[uuid] : [];
                    const isChecked = !!this.selectedItems[type][uuid];
                    /** 
                     * LE double !! convertit la valeur en booléen (true or false)
                    * si this.selectedItems[type][uuid] vaut true alors isChecked sera true
                    * si this.selectedItems[type][uuid] faut false or undefinde alors isChecked sera false
                    */


                    if (isChecked) {
                        // Décocher le savoirfaire => décocher tous ses enfants cochés
                        delete this.selectedItems[type][uuid];
                        enfants.forEach(childUuid => {
                            if (this.selectedItems['activites'] && this.selectedItems['activites'][childUuid]) {
                                delete this.selectedItems['activites'][childUuid];
                            }
                        });
                        // Nettoyage si plus rien
                        if (this.selectedItems['activites'] && Object.keys(this.selectedItems['activites']).length === 0) {
                            delete this.selectedItems['activites'];
                        }
                        if (Object.keys(this.selectedItems[type]).length === 0) {
                            delete this.selectedItems[type];
                        }
                    } else {
                        // Cocher le savoirfaire => cocher tous ses enfants non cochés
                        this.selectedItems[type][uuid] = true;
                        if (!this.selectedItems['activites']) this.selectedItems['activites'] = {};
                        enfants.forEach(childUuid => {
                            this.selectedItems['activites'][childUuid] = true;
                        });
                    }
                } else {
                    // Cas normal (activité, etc)
                    if (this.selectedItems[type][uuid]) {
                        delete this.selectedItems[type][uuid];
                        if (Object.keys(this.selectedItems[type]).length === 0) {
                            delete this.selectedItems[type];
                        }
                    } else {
                        this.selectedItems[type][uuid] = true;
                    }
                }

                console.log ('type : ',type, ' uuid', uuid);
                            console.log('selectedItems :',this.selectedItems);
            },


          
            loadFromStorage() {
                const saved = localStorage.getItem('parcours_sections_state');
                if (saved) {
                    try {
                        this.sections = JSON.parse(saved);
                    } catch (e) {
                        this.sections = {};
                    }
                }
            },
            
            initSections() {
                this.loadFromStorage();
            }
        }
    }
    </script>
    
