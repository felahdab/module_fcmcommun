<x-filament::card>
    <div x-data x-show="false">
    Chargement...
    </div>

    <div x-data="parcoursManager()" x-init="initLivret()" x-cloak>
        <div class="flex items-center justify-between mt1 mb1 ">
            <h3 class="text-2xl font-bold  mt1 mb1">{{ $parcours['libelle_long'] }}</h3>
        </div>
            
        <ul>
            @foreach ($parcours['fonctions'] as $fonction)
                @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.fonction', [
                    'parcoursId' => $parcours['uuid'],
                    'fonction' => $fonction,
                ])
            @endforeach
        </ul>
            
        <!-- Bouton de validation -->
        <div class="mt-6">
            @can('fcmcentral::valider_element_parcours')
                 {{-- <div x-show="hasSelectedItems">
                    <x-filament::button 
                    class="btn-base btn-green"
                        x-on:click="$dispatch('open-modal', { id: 'sauve' })" >
                        Valider
                    </x-filament::button>
                </div>  --}}
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
        class="relative"
        x-on:click="$dispatch('open-modal', { id: 'sauve' })"
       
    >
        <div class="flex items-center space-x-4 space-y-2">
            <x-filament::icon
                icon="heroicon-o-check-circle"
                class="w-5 h-5 mr-1"
            />
            <span class="text-sm leading-tight">
                Valider<br> les éléments cochés
            </span>
        </div>
        
       
    </x-filament::button>
</div>


            @endcan
        </div>
    {{-- </form> --}}

    
        @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.modal')
    </div>

    @push('styles')
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.styles')
    @endpush

  
    
</x-filament::card>

@push('scripts')
@include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.scripts')
@endpush


    
