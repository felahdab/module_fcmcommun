<x-filament::modal 
id="sauve" 
alignment="center" 
width="3xl"
icon="heroicon-o-information-circle"
>
    <x-slot name="heading">
        <h2 class="text-lg font-bold">Ajouter un commentaire</h2>
    </x-slot>
    <x-slot name="description">
        <textarea x-model="commentaire" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 "></textarea>
    </x-slot>
    <x-slot name="footer">
        <x-filament::button 
        color="gray" 
        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" 
        wire:click="$dispatch('close-modal',{id:'sauve'})">
        Annuler
        </x-filament::button>
        <x-filament::button 
            color="success" 
            class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700" 
            x-data="{ loading: false }"
            x-bind:disabled="loading"
            x-on:click="
                loading = true;
                $wire.valider(selectedItems, commentaire)
                    .then(() => {
                        loading = false;
                        selectedItems = {};
                        commentaire = '';
                        $dispatch('close-modal', {id: 'sauve'});
                    })
                    .catch(() => {
                        loading = false;
                    });
            "
        >
            <span x-show="!loading">Valider</span>
            <span x-show="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" 
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" 
                    stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                  Sauvegarde...
            </span>
        </x-filament::button>

        
    </x-slot>
</x-filament::modal>

 <!-- Modal Annul -->
 <!-- Modal Annul -->
<x-filament::modal
id="annul"
alignment="center"
width="3xl"
icon="heroicon-o-x-circle"
> 
<x-slot name="heading">
    <h2 class="text-lg font-bold">Confirmer la dévalidation</h2>
</x-slot>
<x-slot name="description">
    <p class="p-8">Voulez-vous vraiment dévalider l'élément du parcours suivant ?</p>
    <p x-text="message_du_modal"></p>
</x-slot>
<x-slot name="footer">
    <x-filament::button 
        x-on:click="a_devalider = {}; commentaire=''; $dispatch('close-modal', { id: 'annul' })" 
        color="primary"
    >
        Annuler
    </x-filament::button>
    
    <x-filament::button 
        color="danger"
        x-data="{ loading: false }"
        x-bind:disabled="loading"
        x-on:click="
            loading = true;
            $wire.devalider(a_devalider)
                .then(() => {
                    loading = false;
                    a_devalider = {};
                    commentaire = '';
                    $dispatch('close-modal', { id: 'annul' });
                })
                .catch(() => {
                    loading = false;
                });
        "
    >
        <span x-show="!loading">Confirmer</span>
        <span x-show="loading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns=http://www.w3.org/2000/svg fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Suppression...
        </span>
    </x-filament::button>
</x-slot>
</x-filament::modal>


<!-- Modal Propos -->
<x-filament::modal
    id="propos"
    alignment="center"
    width="3xl"
    icon="heroicon-o-x-circle"
> 
    <x-slot name="heading">
        <h2 class="text-lg font-bold">Confirmer la Proposition</h2>
    </x-slot>
    <x-slot name="description">
        <p class="p-8">Vous souhaitez proposer à votre mentor la validation de l'élément suivant</p>
        <p x-text="message_du_modal"></p>
        <textarea x-model="commentaire" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"></textarea>
    </x-slot>
    <x-slot name="footer">
        <x-filament::button 
            wire:click="$dispatch('close-modal', { id: 'propos' })" 
            color="primary"
        >
            Annuler
        </x-filament::button>
        
        <x-filament::button 
            color="success" 
            class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700" 
            x-data="{ loading: false }"
            x-bind:disabled="loading"
            x-on:click="
                loading = true;
                $wire.proposer(a_proposer, commentaire)
                    .then(() => {
                        loading = false;
                        a_proposer = {};
                        commentaire = '';
                        $dispatch('close-modal', {id: 'propos'});
                    })
                    .catch(() => {
                        loading = false;
                    });
            "
        >
            <span x-show="!loading">Valider</span>
            <span x-show="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns=http://www.w3.org/2000/svg fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Proposition...
            </span>
        </x-filament::button>
    </x-slot>
</x-filament::modal>



<!-- Modal Infos -->
<x-filament::modal
id="infosModal"
alignment="center"
width="lg"
icon="heroicon-o-information-circle"
>
<x-slot name="heading">
    <h2 class="text-lg font-bold">Informations</h2>
</x-slot>
<x-slot name="description">
   
    
   
   
   
    <p><strong>Date de validation : </strong><span x-text="infos['dateValidation']"></span></p>
    <p><strong>Validé par : </strong><span x-text="infos['valideur']"></span></p>
    <p><strong>Commentaire : </strong><span x-text="infos['commentaire']"></span></p>
</x-slot>
<x-slot name="footer">
    <x-filament::button x-on:click="infos = {}; $dispatch('close-modal', { id: 'infosModal' })" color="primary">Fermer</x-filament::button>
</x-slot>
</x-filament::modal>
