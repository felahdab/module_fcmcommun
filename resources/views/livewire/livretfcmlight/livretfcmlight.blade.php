<x-filament::card>

    <div x-data="parcoursManager()" x-init="initLivret()" x-cloak>

        {{-- ✅ BARRE DE RECHERCHE ET FILTRES --}}
        <div class="mb-6 space-y-4">
           
            {{-- Barre de recherche avec bouton --}}
            <div class="flex items-center space-x-2">
                <div class="flex-1">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="text"
                            wire:model.live.debounce.500ms="search"
                            placeholder="Rechercher dans le parcours..."
                            class="w-full"
                        >
                            <x-slot name="prefix">
                                <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-5 h-5 text-gray-400" />
                            </x-slot>
                        </x-filament::input>
                    </x-filament::input.wrapper>
                </div>
               
                @if(!empty($search))
                    <x-filament::button
                        color="gray"
                        wire:click="$set('search', '')"
                        title="Effacer la recherche"
                    >
                        <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4" />
                    </x-filament::button>
                @endif
            </div>

            {{-- ✅ FILTRES PAR CATÉGORIE --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
               
                {{-- Filtre par Affectation --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <x-filament::icon icon="heroicon-o-briefcase" class="w-4 h-4 inline mr-1" />
                        Filtrer par affectation
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="affectationFilter">
                            <option value="toutes">Toutes les affectations</option>
                            @foreach($this->affectationsDisponibles as $affectation)
                                <option value="{{ $affectation['id'] }}">{{ $affectation['libelle_court'] }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
           

                {{-- Filtre par Compétence --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <x-filament::icon icon="heroicon-o-academic-cap" class="w-4 h-4 inline mr-1" />
                        Filtrer par compétence
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="competenceFilter">
                            <option value="toutes">Toutes les compétences</option>
                            @forelse($this->competencesDisponibles as $competenceId => $competence)
                                <option value="{{ $competenceId }}">
                                    {{ $competence['code'] }} - {{ $competence['libelle_court'] }}
                                </option>
                            @empty
                                <option disabled>Aucune compétence trouvée</option>
                            @endforelse
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
           

                {{-- ✅ Filtre par Type de Formation --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <x-filament::icon icon="heroicon-o-clipboard-document-list" class="w-4 h-4 inline mr-1" />
                        Filtrer par type de formation
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="savoirfaireTypeFilter">
                            <option value="tous">Tous les types</option>
                            @forelse($this->savoirfaireTypesDisponibles as $typeCode => $typeInfo)
                                <option value="{{ $typeCode }}">
                                    {{ $typeInfo['code'] }} - {{ $typeInfo['libelle'] }}
                                </option>
                            @empty
                                <option disabled>Aucun type trouvé</option>
                            @endforelse
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
           

            {{-- ✅ BOUTONS DE FILTRE PAR STATUT --}}
            <div class="flex flex-wrap gap-2">
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'tous' ? 'primary' : 'gray' }}"
                    wire:click="filtrerParStatut('tous')"
                >
                    <x-filament::icon icon="heroicon-o-document-text" class="w-4 h-4 mr-1" />
                    Tous 
                    {{-- ({{ $this->statistiques['total'] }}) --}}
                </x-filament::button>
               
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'valides' ? 'success' : 'gray' }}"
                    wire:click="filtrerParStatut('valides')"
                >
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 mr-1" />
                    Validés 
                    {{-- ({{ $this->statistiques['valides'] }}) --}}
                </x-filament::button>
               
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'proposes' ? 'warning' : 'gray' }}"
                    wire:click="filtrerParStatut('proposes')"
                >
                    <x-filament::icon icon="heroicon-o-paper-airplane" class="w-4 h-4 mr-1" />
                    Proposés 
                    {{-- ({{ $this->statistiques['proposes'] }}) --}}
                </x-filament::button>
               
                <x-filament::button
                    size="sm"
                    color="{{ $statutFilter === 'non_valides' ? 'info' : 'gray' }}"
                    wire:click="filtrerParStatut('non_valides')"
                >
                    <x-filament::icon icon="heroicon-o-x-circle" class="w-4 h-4 mr-1" />
                    Non validés 
                    {{-- ({{ $this->statistiques['non_valides'] }}) --}}
                </x-filament::button>
            </div>

            {{-- ✅ INDICATEUR DE FILTRES ACTIFS --}}
            @if($this->hasActiveFilters)
                <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center flex-wrap gap-2 text-sm text-blue-800">
                        <div class="flex items-center">
                            <x-filament::icon icon="heroicon-o-funnel" class="w-4 h-4 mr-1" />
                            <span class="font-medium">Filtres actifs :</span>
                        </div>
                       
                        @if(!empty($search))
                            <span class="px-2 py-1 bg-blue-200 rounded text-xs font-medium">
                                🔍 "{{ Str::limit($search, 20) }}"
                            </span>
                        @endif
                       
                        @if($statutFilter !== 'tous')
                            <span class="px-2 py-1 bg-blue-200 rounded text-xs font-medium">
                                📊 {{ ucfirst($statutFilter) }}
                            </span>
                        @endif
                       
                        @if($affectationFilter !== 'toutes')
                            @php
                                $affectationLibelle = collect($this->affectationsDisponibles)->firstWhere('id', $affectationFilter)['libelle_court'] ?? "Affectation {$affectationFilter}";
                            @endphp
                            <span class="px-2 py-1 bg-green-200 rounded text-xs font-medium">
                                💼 {{ $affectationLibelle }}
                            </span>
                        @endif
                       
                        @if($competenceFilter !== 'toutes')
                            <span class="px-2 py-1 bg-yellow-200 rounded text-xs font-medium">
                                🎯 {{ $this->competencesDisponibles[$competenceFilter]['libelle_court'] }}
                            </span>
                        @endif
                       
                        @if($savoirfaireTypeFilter !== 'tous')
                            <span class="px-2 py-1 bg-purple-200 rounded text-xs font-medium">
                                📚 {{ $this->savoirfaireTypesDisponibles[$savoirfaireTypeFilter]['libelle'] }}
                            </span>
                        @endif
                    </div>
                   
                    <x-filament::button
                        color="gray"
                        size="sm"
                        wire:click="resetAllFilters"
                        title="Effacer tous les filtres"
                    >
                        <x-filament::icon icon="heroicon-o-x-mark" class="w-4 h-4 mr-1" />
                        Tout effacer
                    </x-filament::button>
                </div>
            @endif

            {{-- ✅ BARRE DE PROGRESSION AMÉLIORÉE --}}
            {{-- @if($this->statistiques['total'] > 0)
                <div class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5 text-blue-600 mr-2" />
                            <span class="text-sm font-semibold text-blue-900">
                                Progression du parcours
                            </span>
                        </div>
                        <span class="text-lg font-bold text-blue-900">
                            {{ $this->statistiques['total'] > 0 ? round(($this->statistiques['valides'] / $this->statistiques['total']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                   
                    <div class="w-full bg-blue-200 rounded-full h-3 mb-2">
                        <div
                            class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 ease-out"
                            style="width: {{ $this->statistiques['total'] > 0 ? round(($this->statistiques['valides'] / $this->statistiques['total']) * 100, 1) : 0 }}%"
                        ></div>
                    </div>
                   
                    <div class="grid grid-cols-3 gap-4 text-xs text-blue-700">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                            <span><strong>{{ $this->statistiques['valides'] }}</strong> validés</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-orange-400 rounded-full mr-1"></div>
                            <span><strong>{{ $this->statistiques['proposes'] }}</strong> proposés</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-1"></div>
                            <span><strong>{{ $this->statistiques['non_valides'] }}</strong> restants</span>
                        </div>
                    </div>
                </div>
            @endif --}}


            @php
            $stats = $this->getStatistiquesGenerales();
        @endphp
        
        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
            {{-- Affectations --}}
            {{-- <x-filament::section>
                <x-slot name="heading">Affectations</x-slot>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600">
                        {{ $stats['affectations_validees'] }} / {{ $stats['affectations_total'] }}
                    </div>
                    <div class="text-sm text-gray-600">Affectations validées</div>
                </div>
            </x-filament::section>
         --}}
            {{-- Heures --}}
            <x-filament::section>
                <x-slot name="heading">Progression Générale </x-slot>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">
                        {{-- {{ $stats['heures_validees'] }}h / {{ $stats['heures_total'] }}h --}}
                        {{ $stats['taux_heures'] }}% complété
                    </div>
                    <div class="text-sm text-gray-600"></div>
                </div>
            </x-filament::section>
        
            {{-- Autre stat si besoin --}}
            {{-- <x-filament::section>
                <x-slot name="heading">État global</x-slot>
                <div class="text-center">
                    <x-filament::badge
                        size="lg"
                        color="{{ $stats['taux_heures'] == 100 ? 'success' : ($stats['taux_heures'] > 50 ? 'warning' : 'danger') }}">
                        {{ $stats['taux_heures'] }}% validé
                    </x-filament::badge>
                </div>
            </x-filament::section> --}}
        </div>
        

        </div>

        {{-- ✅ CONTENU PRINCIPAL --}}
        @if($parcours)
            <div class="flex items-center justify-between mt-1 mb-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $parcours['libelle_long'] }}</h3>
                   
                </div>
               
                {{-- @if($this->statistiques['total'] > 0)
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ $this->statistiques['valides'] }}/{{ $this->statistiques['total'] }}
                        </div>
                        <div class="text-sm text-gray-600">éléments validés</div>
                    </div>
                @endif --}}
            </div>
               
            @if(!empty($parcours['affectations']))
                <ul class="space-y-2">
                    @foreach ($parcours['affectations'] as $affectation)
                        @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.affectation', [
                            'parcoursId' => $parcours['uuid'],
                            'affectation' => $affectation,
                            
                        ])
                    @endforeach
                </ul>
            @else
                <div class="text-center py-16 text-gray-500">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                    <p class="text-xl font-medium mb-2">Aucun résultat trouvé</p>
                    <p class="text-sm mb-6 max-w-md mx-auto">
                        @if(!empty($search))
                            Aucun élément ne correspond à votre recherche <strong>"{{ $search }}"</strong>
                        @elseif($this->hasActiveFilters)
                            Aucun élément ne correspond aux filtres sélectionnés
                        @else
                            Ce parcours ne contient aucune affectation définie
                        @endif
                    </p>
                    <x-filament::button
                        color="gray"
                        size="sm"
                        wire:click="resetAllFilters"
                    >
                        <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4 mr-2" />
                        Réinitialiser les filtres
                    </x-filament::button>
                </div>
            @endif
        @else
            <div class="text-center py-16 text-gray-500">
                <x-filament::icon icon="heroicon-o-document-text" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <p class="text-xl font-medium mb-2">Aucun parcours disponible</p>
                <p class="text-sm">Ce marin n'a pas encore de parcours défini ou vous n'avez pas les droits pour le consulter</p>
            </div>
        @endif
           
        {{-- ✅ BOUTON DE VALIDATION FLOTTANT --}}
        <div class="mt-6">
            @if($this->canValider())
                <div
                    x-data="{ hasSelectedItems: false }"
                    x-init="$watch('selectedItems', () => {
                        hasSelectedItems = Object.keys(selectedItems.objectif || {}).length > 0 ||
                                          Object.keys(selectedItems.savoirfaire || {}).length > 0 ||
                                          Object.keys(selectedItems.competence || {}).length > 0 ||
                                          Object.keys(selectedItems.affectation || {}).length > 0;
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
                        class="relative shadow-xl border-2 border-green-400"
                        x-on:click="$dispatch('open-modal', { id: 'sauve' })"
                    >
                        <div class="flex items-center space-x-2">
                            <x-filament::icon
                                icon="heroicon-o-check-circle"
                                class="w-6 h-6"
                            />
                            <div class="text-left" style="margin-left:10px;">
                                <div class="text-lg font-semibold leading-tight">Valider</div>
                                <div class="text-lg font-semibold leading-tight">les éléments cochés</div>
                            </div>
                        </div>
                    </x-filament::button>
                </div>
            @endif
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
