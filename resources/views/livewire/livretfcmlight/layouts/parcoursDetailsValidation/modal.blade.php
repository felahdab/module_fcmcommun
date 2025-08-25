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
        <x-filament::button color="gray" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400" wire:click="$dispatch('close-modal',{id:'sauve'})">Annuler</x-filament::button>
        <x-filament::button color="success" 
        class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700" 
        x-on:click="$wire.valider(selectedItems, commentaire); $dispatch('close-modal', {id: 'sauve'});">Valider</x-filament::button>
    </x-slot>
</x-filament::modal>

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
     <x-filament::button x-on:click="a_devalider = {}; commentaire=''; $dispatch('close-modal', { id: 'annul' })" color="primary">Annuler</x-filament::button>
     <x-filament::button x-on:click="$wire.devalider(a_devalider); a_devalider = {}; commentaire=''; $dispatch('close-modal', { id: 'annul' });" color="danger">Confirmer</x-filament::button>
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
    <p class="p-8">Vous souhaitez proposer a votre mentor la validation de l'élément suivant</p>
    <p x-text="message_du_modal"></p>
    <textarea x-model="commentaire" rows="4" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 "></textarea>
</x-slot>
<x-slot name="footer">
    <x-filament::button wire:click="$dispatch('close-modal', { id: 'propos' })" color="primary">Annuler</x-filament::button>
    <x-filament::button color="success" 
        class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700" 
        x-on:click="$wire.proposer(a_proposer, commentaire).then((value) => { a_proposer= {}; commentaire = ''; $dispatch('close-modal', {id: 'propos'}) } );">Valider</x-filament::button>
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
    <p><strong>Type :</strong><span x-text="infos['type']"></span></p>
    <p><strong>UUID :</strong><span x-text="infos['uuid']"></span></p>
    <p><strong>Titre :</strong><span x-text="infos['libelleCourt']"></span></p>
    <p><strong>Description :</strong><span x-text="infos['libelleLong']"></span></p>
    <p><strong>Durée :</strong><span x-text="infos['duree']"></span></p>
    <p><strong>Date de validation :</strong><span x-text="infos['dateValidation']"></span></p>
    <p><strong>Validé par :</strong><span x-text="infos['valideur']"></span></p>
    <p><strong>Commentaire :</strong><span x-text="infos['commentaire']"></span></p>
</x-slot>
<x-slot name="footer">
    <x-filament::button x-on:click="infos = {}; $dispatch('close-modal', { id: 'infosModal' })" color="primary">Fermer</x-filament::button>
</x-slot>
</x-filament::modal>
