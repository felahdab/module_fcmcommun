<x-filament::card>

<div x-data x-show="false">
Chargement...
</div>

<div x-data="parcoursManager()" x-init="initLivret()" x-cloak>
    <div class="flex items-center justify-between mt1 mb1 ">
        <h3 class="text-2xl font-bold  mt1 mb1">{{ $parcours['libelle_long'] }}</h3>
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
        @foreach ($parcours['fonctions'] as $fonction)
            @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.fonction', [
                'parcoursId' => $parcours['uuid'],
                'fonction' => $fonction,
            ])
        @endforeach
    </ul>
        
    <!-- Bouton Submit -->
    <div class="mt-6">
        @can('fcmcentral::valider_element_parcours')
            <x-filament::button 
                x-on:click="$dispatch('open-modal', { id: 'sauve' })" >
                Valider
            </x-filament::button>
        @endcan
    </div>
{{-- </form> --}}

 
    <!-- Modal Sauve -->
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.modal')
</div>

    @push('styles')
    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.styles')
    @endpush

  
   


    
</x-filament::card>

@push('scripts')
@include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.scripts')
@endpush


    
