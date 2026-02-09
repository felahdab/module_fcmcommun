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
        <textarea x-model="commentaire" rows="4" 
        class="w-full border-gray-300 rounded-md shadow-sm 
                   focus:ring-primary-500 focus:border-primary-500 
                   dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
        >
    </textarea>
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
        <h2 class="text-lg font-bold" x-text="modification_proposition ? 'Modifier votre proposition' : 'Confirmer la Proposition'"></h2>
    </x-slot>
    <x-slot name="description">
        <p class="p-8">Vous souhaitez proposer à votre mentor la validation de l'élément suivant : </p>
        <p x-text="message_du_modal" class="guillemets italic"></p>
        <br/>
                <label class="block text-sm font-medium mb-2">Commentaire :</label>
        <textarea x-model="commentaire" rows="4" 
        class="w-full border-gray-300 rounded-md shadow-sm 
        focus:ring-primary-500 focus:border-primary-500 
        dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
        ></textarea>
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
        <span x-show="!loading" x-text="modification_proposition ? 'Modifier' : 'Proposer'"></span>
        <span x-show="loading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>

                Proposition...
            </span>
        </x-filament::button>
    </x-slot>
</x-filament::modal>


<!-- Modal Validation Mentor (Accepter/Refuser une proposition) -->
<x-filament::modal
    id="validMentor"
    alignment="center"
    width="3xl"
    icon="heroicon-o-shield-check"
>
    <x-slot name="heading">
        <h2 class="text-lg font-bold text-primary-600 dark:text-primary-400">
            Valider ou Refuser la proposition
        </h2>
    </x-slot>
    
    <x-slot name="description">
        <div class="space-y-4">
            {{-- Élément proposé --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Élément proposé :</p>
                <p x-text="message_du_modal" class="text-base font-semibold text-gray-900 dark:text-white"></p>
            </div>

            {{-- Commentaire du mentoré --}}
            <div x-show="commentaire_proposition_existant" class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <p class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-2">
                    <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="w-4 h-4 inline" />
                    Commentaire du mentoré :
                </p>
                <p x-text="commentaire_proposition_existant" class="text-sm text-blue-900 dark:text-blue-100 italic"></p>
            </div>

            {{-- Champ commentaire mentor --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
                    Votre commentaire (optionnel) :
                </label>
                <textarea 
                    x-model="commentaire" 
                    rows="3" 
                    class="w-full border-gray-300 rounded-md shadow-sm 
                           focus:ring-primary-500 focus:border-primary-500
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                    placeholder="Ajoutez un commentaire si nécessaire...">
                </textarea>
            </div>
        </div>
    </x-slot>
    
    <x-slot name="footer">
        <div class="flex justify-between w-full">
            {{-- Bouton Annuler à gauche --}}
            <x-filament::button
                x-on:click="a_proposition_mentor = {}; commentaire=''; commentaire_proposition_existant=''; $dispatch('close-modal', { id: 'validMentor' })"
                color="gray"
            >
                Annuler
            </x-filament::button>

            {{-- Boutons Refuser/Accepter à droite --}}
            <div class="flex space-x-2">
                {{-- Bouton REFUSER --}}
                <x-filament::button
                    color="danger"
                    icon="heroicon-m-x-circle"
                    x-data="{ loading: false }"
                    x-bind:disabled="loading"
                    x-on:click="
                        loading = true;
                        $wire.annulerProposition(a_proposition_mentor, commentaire)
                            .then(() => {
                                loading = false;
                                a_proposition_mentor = {};
                                commentaire = '';
                                commentaire_proposition_existant = '';
                                $dispatch('close-modal', { id: 'validMentor' });
                            })
                            .catch(() => {
                                loading = false;
                            });
                    "
                >
                    <span x-show="!loading">Refuser</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Refus...
                    </span>
                </x-filament::button>

                {{-- Bouton ACCEPTER --}}
                <x-filament::button
                    color="success"
                    icon="heroicon-m-check-circle"
                    x-data="{ loading: false }"
                    x-bind:disabled="loading"
                    x-on:click="
                        loading = true;
                        let elementAValider = {
                            [a_proposition_mentor.type]: {
                                [a_proposition_mentor.uuid]: true
                            }
                        };
                        $wire.valider(elementAValider, commentaire)
                            .then(() => {
                                loading = false;
                                a_proposition_mentor = {};
                                commentaire = '';
                                commentaire_proposition_existant = '';
                                $dispatch('close-modal', { id: 'validMentor' });
                            })
                            .catch(() => {
                                loading = false;
                            });
                    "
                >
                    <span x-show="!loading">Accepter</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Validation...
                    </span>
                </x-filament::button>
            </div>
        </div>
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
        {{-- <p><strong>Description : </strong><span x-text="infos.intitule"></span></p> --}}

        <template x-if="infos.dateProposition">
            <p><strong>Date de Proposition : </strong><span x-text="infos.dateProposition"></span></p>
        </template>

        <template x-if="infos.commentaireProposition">
            <p><strong>Commentaire Mentoré : </strong><span x-text="infos.commentaireProposition"></span></p>
        </template>

        <template x-if="infos.valideur">
            <p><strong>Validé par : </strong><span x-text="infos.valideur"></span></p>
        </template>

        <template x-if="infos.dateValidation">
            <p><strong>Date de validation : </strong><span x-text="infos.dateValidation"></span></p>
        </template>

        <template x-if="infos.commentaire">
            <p><strong>Commentaire : </strong><span x-text="infos.commentaire"></span></p>
        </template>
    </x-slot>

    <x-slot name="footer">
        <x-filament::button x-on:click="infos = {}; $dispatch('close-modal', { id: 'infosModal' })" color="primary">Fermer</x-filament::button>
    </x-slot>
</x-filament::modal>


