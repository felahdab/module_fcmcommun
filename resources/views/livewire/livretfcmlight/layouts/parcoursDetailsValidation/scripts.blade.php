<script>
   
   window.savoirfaireChildren = @json($savoirFaireChildren ?? []);
   
    function parcoursManager() {
        return {
            parcours : @js($marin->complements_fcm->donnees_du_parcours->parcoursmarin),
            sections: {},

            a_devalider : {},
            a_proposer : {},
            infos : {},

            commentaire : '',
            message_du_modal : '',

            savoirFaireChildren: window.savoirfaireChildren || {},
           
            selectedItems:{
            },

            hasSelectedItems() 
            {
                if (this.selectedItems['activite'])
                {
                    if (Object.keys(this.selectedItems['activite']).length > 0)
                    {
                        return true;
                    }
                }

                if (this.selectedItems['savoirfaire'])
                {
                    if (Object.keys(this.selectedItems['savoirfaire']).length > 0)
                    {
                        return true;
                    }
                }

                return false;
            },
            
            selectedItemsSauveJson: '',
            
            toggleSection(sectionId) {
                this.sections[sectionId] = ! this.sections[sectionId];
                this.saveToStorage();
            },
            
            isOpen(sectionId) {
                return this.sections[sectionId] || false;
            },
            
            saveToStorage() {
                localStorage.setItem('parcours_sections_state', JSON.stringify(this.sections));
            },

            toggleCheck(sectionId) {
    const [type, uuid] = sectionId.split('_');
 
    if (!this.selectedItems[type]) {
        this.selectedItems[type] = {};
    }
 
    // ✅ GESTION SPÉCIFIQUE POUR LES ACTIVITÉS
    if (type === 'activite') {
        // Toggle l'activité
        if (this.selectedItems[type][uuid]) {
            delete this.selectedItems[type][uuid];
        } else {
            this.selectedItems[type][uuid] = true;
        }
 
        // Trouver le savoir-faire parent de cette activité
        let parentSavoirFaire = null;
        for (const [sfUuid, enfants] of Object.entries(this.savoirFaireChildren)) {
            if (enfants.includes(uuid)) {
                parentSavoirFaire = sfUuid;
                break;
            }
        }
 
        if (parentSavoirFaire) {
            // Vérifier si toutes les activités filles sont cochées
            const allChecked = this.savoirFaireChildren[parentSavoirFaire].every(
                actUuid => this.selectedItems['activite'] && this.selectedItems['activite'][actUuid]
            );
            
            if (allChecked) {
                // Cocher le savoir-faire parent
                if (!this.selectedItems['savoirfaire']) {
                    this.selectedItems['savoirfaire'] = {};
                }
                this.selectedItems['savoirfaire'][parentSavoirFaire] = true;
            } else {
                // Décocher le savoir-faire parent
                if (this.selectedItems['savoirfaire'] && this.selectedItems['savoirfaire'][parentSavoirFaire]) {
                    delete this.selectedItems['savoirfaire'][parentSavoirFaire];
                    if (Object.keys(this.selectedItems['savoirfaire']).length === 0) {
                        delete this.selectedItems['savoirfaire'];
                    }
                }
            }
        }
 
        // Nettoyage si plus d'activités sélectionnées
        if (this.selectedItems['activite'] && Object.keys(this.selectedItems['activite']).length === 0) {
            delete this.selectedItems['activite'];
        }
 
        console.log('type :', type, ' uuid:', uuid);
        console.log('selectedItems :', this.selectedItems);
        return;
    }
 
    // ✅ GESTION SPÉCIFIQUE POUR LES SAVOIR-FAIRES
    if (type === 'savoirfaire') {
        const enfants = this.savoirFaireChildren && this.savoirFaireChildren[uuid] ? this.savoirFaireChildren[uuid] : [];
        const isChecked = !!this.selectedItems[type][uuid];
 
        if (isChecked) {
            console.log('Décochage du savoir-faire');
            // Décocher le savoirfaire => décocher tous ses enfants cochés
            delete this.selectedItems[type][uuid];
            
            enfants.forEach(childUuid => {
                if (this.selectedItems['activite'] && this.selectedItems['activite'][childUuid]) {
                    delete this.selectedItems['activite'][childUuid];
                }
            });
            
            // Nettoyage si plus rien
            if (this.selectedItems['activite'] && Object.keys(this.selectedItems['activite']).length === 0) {
                delete this.selectedItems['activite'];
            }
            if (Object.keys(this.selectedItems[type]).length === 0) {
                delete this.selectedItems[type];
            }
        } else {
            console.log('Cochage du savoir-faire');
            // Cocher le savoirfaire => cocher tous ses enfants non cochés
            this.selectedItems[type][uuid] = true;
            
            if (!this.selectedItems['activite']) {
                this.selectedItems['activite'] = {};
            }
            
            enfants.forEach(childUuid => {
                this.selectedItems['activite'][childUuid] = true;
            });
        }
 
        console.log('type :', type, ' uuid:', uuid);
        console.log('selectedItems :', this.selectedItems);
        return;
    }
 
    // ✅ CAS NORMAL POUR LES AUTRES TYPES (fonction, competence, etc.)
    if (this.selectedItems[type][uuid]) {
        delete this.selectedItems[type][uuid];
        if (Object.keys(this.selectedItems[type]).length === 0) {
            delete this.selectedItems[type];
        }
    } else {
        this.selectedItems[type][uuid] = true;
    }
 
    console.log('type :', type, ' uuid:', uuid);
    console.log('selectedItems :', this.selectedItems);
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
            
            initLivret() {
                this.loadFromStorage();
            }
        }
    }
    </script>