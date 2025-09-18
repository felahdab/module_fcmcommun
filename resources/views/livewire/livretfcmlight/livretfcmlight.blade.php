<x-filament::card>
    <div x-data="parcoursManager()" x-init="initLivret()" x-cloak>
        
        {{-- ✅ BARRE DE RECHERCHE AVEC BOUTON VALIDER --}}
        <div class="mb-6 space-y-4">
            
            {{-- Barre de recherche avec bouton --}}
            <div class="flex items-center space-x-2">
                <div class="flex-1">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model="search"
                            placeholder="Rechercher dans le parcours..."
                            class="w-full"
                            wire:keydown.enter="$refresh"
                        >
                            <x-slot name="prefix">
                                <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-5 h-5 text-gray-400" />
                            </x-slot>
                        </x-filament::input>
                    </x-filament::input.wrapper>
                </div>
                
                <x-filament::button
                    color="primary"
                    wire:click="$refresh"
                >
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-4 h-4 mr-2" />
                    Rechercher
                </x-filament::button>
                
                @if(!empty($search))
                    <x-filament::button
                        color="gray"
                        wire:click="$set('search', '')"
                    >
                        <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                    </x-filament::button>
                @endif
            </div>

            {{-- ✅ BOUTONS DE FILTRE PAR STATUT --}}
            <div class="flex flex-wrap gap-2">
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'tous' ? 'primary' : 'gray' }}"
                    wire:click="filtrerParStatut('tous')"
                >
                    <x-filament::icon icon="heroicon-o-document-text" class="w-4 h-4 mr-1" />
                    Tous ({{ $statistiques['total'] }})
                </x-filament::button>
                
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'valides' ? 'success' : 'gray' }}"
                    wire:click="filtrerParStatut('valides')"
                >
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 mr-1" />
                    Validés ({{ $statistiques['valides'] }})
                </x-filament::button>
                
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'proposes' ? 'warning' : 'gray' }}"
                    wire:click="filtrerParStatut('proposes')"
                >
                    <x-filament::icon icon="heroicon-o-paper-airplane" class="w-4 h-4 mr-1" />
                    Proposés ({{ $statistiques['proposes'] }})
                </x-filament::button>
                
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'non_valides' ? 'info' : 'gray' }}"
                    wire:click="filtrerParStatut('non_valides')"
                >
                    <x-filament::icon icon="heroicon-o-x-circle" class="w-4 h-4 mr-1" />
                    Non validés ({{ $statistiques['non_valides'] }})
                </x-filament::button>
            </div>

            {{-- Indicateur de filtres actifs --}}
            @if(!empty($search) || $statutFilter !== 'tous')
                <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center space-x-2 text-sm text-blue-800">
                        <x-filament::icon icon="heroicon-o-funnel" class="w-4 h-4" />
                        <span>Filtres actifs :</span>
                        @if(!empty($search))
                            <span class="px-2 py-1 bg-blue-200 rounded text-xs">
                                Recherche: "{{ $search }}"
                            </span>
                        @endif
                        @if($statutFilter !== 'tous')
                            <span class="px-2 py-1 bg-blue-200 rounded text-xs">
                                Statut: {{ $statutFilter }}
                            </span>
                        @endif
                    </div>
                    <x-filament::button
                        color="gray"
                        size="sm"
                        wire:click="$set('search', ''); $set('statutFilter', 'tous')"
                    >
                        <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4 mr-1" />
                        Effacer filtres
                    </x-filament::button>
                </div>
            @endif

            {{-- Barre de progression --}}
            @if($statistiques['total'] > 0)
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-blue-900">
                            Progression du parcours
                        </span>
                        <span class="text-sm font-bold text-blue-900">
                            {{ $statistiques['total'] > 0 ? round(($statistiques['valides'] / $statistiques['total']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="w-full bg-blue-200 rounded-full h-2">
                        <div 
                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            style="width: {{ $statistiques['total'] > 0 ? round(($statistiques['valides'] / $statistiques['total']) * 100, 1) : 0 }}%"
                        ></div>
                    </div>
                    <div class="flex justify-between text-xs text-blue-700 mt-1">
                        <span>{{ $statistiques['valides'] }}/{{ $statistiques['total'] }} validés</span>
                        <span>{{ $statistiques['proposes'] }} proposés</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- CONTENU PRINCIPAL --}}
        @if($parcours)
            <div class="flex items-center justify-between mt-1 mb-1">
                <h3 class="text-2xl font-bold mt-1 mb-1">{{ $parcours['libelle_long'] }}</h3>
            </div>
               
            @if(!empty($parcours['fonctions']))
                <ul>
                    @foreach ($parcours['fonctions'] as $fonction)
                        @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.fonction', [
                            'parcoursId' => $parcours['uuid'],
                            'fonction' => $fonction,
                        ])
                    @endforeach
                </ul>
            @else
                <div class="text-center py-12 text-gray-500">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                    <p class="text-lg font-medium mb-2">Aucun résultat trouvé</p>
                    <p class="text-sm mb-4">
                        @if(!empty($search))
                            Aucun élément ne correspond à votre recherche "{{ $search }}"
                        @elseif($statutFilter !== 'tous')
                            Aucun élément ne correspond au filtre "{{ $statutFilter }}"
                        @else
                            Aucun élément trouvé
                        @endif
                    </p>
                    <x-filament::button 
                        color="gray" 
                        size="sm" 
                        wire:click="$set('search', ''); $set('statutFilter', 'tous')"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4 mr-2" />
                        Tout afficher
                    </x-filament::button>
                </div>
            @endif
        @else
            <div class="text-center py-12 text-gray-500">
                <x-filament::icon icon="heroicon-o-document-text" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                <p class="text-lg font-medium mb-2">Aucun parcours disponible</p>
                <p class="text-sm">Ce marin n'a pas encore de parcours défini</p>
            </div>
        @endif
           
        {{-- BOUTON DE VALIDATION FLOTTANT --}}
        <div class="mt-6">
            @can('fcmcentral::valider_element_parcours')
                <div
                    x-data="{ hasSelectedItems: false }"
                    x-init="$watch('selectedItems', () => {
                        hasSelectedItems = Object.keys(selectedItems.activite || {}).length > 0 ||
                                          Object.keys(selectedItems.savoirfaire || {}).length > 0;
                    })"
                    x-show="hasSelectedItems"
                    style="position: fixed; top: 10rem; left: 1rem; z-index: 1000;"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-90"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-90"
                > 
                    <x-filament::button
                        color="success"
                        size="lg"
                        class="relative shadow-lg"
                        x-on:click="$dispatch('open-modal', { id: 'sauve' })"
                    >
                        <div class="flex items-center space-x-2">
                            <x-filament::icon
                                icon="heroicon-o-check-circle"
                                class="w-5 h-5"
                            />
                            <span class="text-sm leading-tight">
                                Valider<br> les éléments cochés
                            </span>
                        </div>
                    </x-filament::button>
                </div>
            @endcan
        </div>

        @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.modal')
    </div>

    @push('styles')
        @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.styles')
    @endpush
</x-filament::card>

@push('scripts')
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.scripts')
@endpush
