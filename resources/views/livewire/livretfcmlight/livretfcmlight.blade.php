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
                 <div x-show="hasSelectedItems">
                    <x-filament::button 
                        x-on:click="$dispatch('open-modal', { id: 'sauve' })" >
                        Valider
                    </x-filament::button>
                </div> 
                {{-- <div 
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
        <button
            type="button"
            class="btn-base btn-green"
            x-on:click="$dispatch('open-modal', { id: 'validateSelected' })"
            x-bind:title="'Valider ' + (Object.keys(selectedItems.activite || {}).length + Object.keys(selectedItems.savoirfaire || {}).length) + ' élément(s) sélectionné(s)'"
        >
            <div class="flex items-center space-x-2">
                <svg xmlns=http://www.w3.org/2000/svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm leading-tight">
                    Valider<br>éléments cochés
                </span>
            </div>
            
            <div 
                x-show="(Object.keys(selectedItems.activite || {}).length + Object.keys(selectedItems.savoirfaire || {}).length) > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold"
                x-text="Object.keys(selectedItems.activite || {}).length + Object.keys(selectedItems.savoirfaire || {}).length"
            >
            </div>
        </button>
    </div> --}}

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


    
