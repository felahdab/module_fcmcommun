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


    
