<script>
   
   window.savoirfaireChildren = @json($savoirFaireChildren ?? []);
   
   function parcoursManager() {
    return {
        parcours: @js($marin->complements_fcm->donnees_du_parcours->parcoursmarin),
        sections: {},
        
        a_devalider: {},
        a_proposer: {},
        infos: {},
        
        commentaire: '',
        message_du_modal: '',
        
        savoirFaireChildren: window.savoirfaireChildren || {},
        selectedItems: {},
        
        // ✅ MÉTHODE POUR VIDER LES SÉLECTIONS
        clearSelections() {
            this.selectedItems = {};
            console.log('✅ Sélections vidées');
        },
        
        // ✅ INITIALISATION AVEC ÉVÉNEMENTS
        init() {
            this.loadFromStorage();
            
            // Écouter l'événement de vidage des sélections
            this.$wire.on('clear-selections', () => {
                this.clearSelections();
            });
            
            // Écouter les mises à jour Livewire
            document.addEventListener('livewire:updated', () => {
                console.log('✅ Livewire updated - data refreshed');
            });
        },
        
        hasSelectedItems() {
            if (this.selectedItems['activite'] && Object.keys(this.selectedItems['activite']).length > 0) {
                return true;
            }
            if (this.selectedItems['savoirfaire'] && Object.keys(this.selectedItems['savoirfaire']).length > 0) {
                return true;
            }
            return false;
        },
        
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
        
        toggleCheck(sectionId) {
    const [type, uuid] = sectionId.split('_');
    
    console.log(`=== TOGGLE ${type} ${uuid} ===`);
    
    if (!this.selectedItems[type]) {
        this.selectedItems[type] = {};
    }
    
    // ✅ ÉTAT AVANT LE TOGGLE
    const wasChecked = !!this.selectedItems[type][uuid];
    console.log('État avant:', wasChecked ? 'COCHÉ' : 'DÉCOCHÉ');
    
    // ✅ EFFECTUER LE TOGGLE
    if (wasChecked) {
        delete this.selectedItems[type][uuid];
        if (Object.keys(this.selectedItems[type]).length === 0) {
            delete this.selectedItems[type];
        }
    } else {
        this.selectedItems[type][uuid] = true;
    }
    
    // ✅ ÉTAT APRÈS LE TOGGLE
    const isNowChecked = !wasChecked;
    console.log('État après:', isNowChecked ? 'COCHÉ' : 'DÉCOCHÉ');
    
    // ✅ GESTION SPÉCIALE SELON LE TYPE
    if (type === 'activite') {
        this.handleActiviteToggle(uuid, isNowChecked);
    } else if (type === 'savoirfaire') {
        this.handleSavoirFaireToggle(uuid, isNowChecked);
    }
    
    console.log('Résultat final:', this.selectedItems);
},

handleSavoirFaireToggle(uuid, isNowChecked) {
    const enfants = this.savoirFaireChildren[uuid] || [];
    console.log(`Savoir-faire ${uuid} ${isNowChecked ? 'COCHÉ' : 'DÉCOCHÉ'}, enfants:`, enfants);
    
    if (isNowChecked) {
        // ✅ COCHER toutes les activités enfants
        if (!this.selectedItems['activite']) {
            this.selectedItems['activite'] = {};
        }
        enfants.forEach(childUuid => {
            this.selectedItems['activite'][childUuid] = true;
            console.log(`→ Activité ${childUuid} COCHÉE`);
        });
    } else {
        // ✅ DÉCOCHER toutes les activités enfants
        enfants.forEach(childUuid => {
            if (this.selectedItems['activite'] && this.selectedItems['activite'][childUuid]) {
                delete this.selectedItems['activite'][childUuid];
                console.log(`→ Activité ${childUuid} DÉCOCHÉE`);
            }
        });
        
        // Nettoyer si plus d'activités
        if (this.selectedItems['activite'] && Object.keys(this.selectedItems['activite']).length === 0) {
            delete this.selectedItems['activite'];
        }
    }
},

handleActiviteToggle(uuid, isNowChecked) {
    console.log(`Activité ${uuid} ${isNowChecked ? 'COCHÉE' : 'DÉCOCHÉE'}`);
    
    // Trouver le savoir-faire parent
    let parentSavoirFaire = null;
    for (const [sfUuid, enfants] of Object.entries(this.savoirFaireChildren)) {
        if (enfants.includes(uuid)) {
            parentSavoirFaire = sfUuid;
            break;
        }
    }
    
    if (parentSavoirFaire) {
        console.log(`Parent savoir-faire: ${parentSavoirFaire}`);
        
        // Vérifier si TOUTES les activités du savoir-faire sont cochées
        const allChecked = this.savoirFaireChildren[parentSavoirFaire].every(
            actUuid => this.selectedItems['activite'] && this.selectedItems['activite'][actUuid]
        );
        
        console.log('Toutes les activités cochées?', allChecked);
        
        if (allChecked) {
            // ✅ COCHER le savoir-faire parent
            if (!this.selectedItems['savoirfaire']) {
                this.selectedItems['savoirfaire'] = {};
            }
            this.selectedItems['savoirfaire'][parentSavoirFaire] = true;
            console.log(`→ Savoir-faire parent ${parentSavoirFaire} COCHÉ`);
        } else {
            // ✅ DÉCOCHER le savoir-faire parent
            if (this.selectedItems['savoirfaire'] && this.selectedItems['savoirfaire'][parentSavoirFaire]) {
                delete this.selectedItems['savoirfaire'][parentSavoirFaire];
                console.log(`→ Savoir-faire parent ${parentSavoirFaire} DÉCOCHÉ`);
                
                if (Object.keys(this.selectedItems['savoirfaire']).length === 0) {
                    delete this.selectedItems['savoirfaire'];
                }
            }
        }
    }
},

        
        initLivret() {
            this.init();
        }
    }
}

    </script>