<script>
   
    function parcoursManager() {
        return {
            parcours : @js($marin->complements_fcm->donnees_du_parcours->parcoursmarin),
            sections: {},

            a_devalider : {},
            a_proposer : {},

            commentaire : '',
            message_du_modal : '',

            savoirFaireChildren: window.savoirfaireChildren || {},
           
            selectedItems:{
                sauve: {},
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

                if ( ! this.selectedItems.sauve[type]) {
                    this.selectedItems.sauve[type] = {};
                }

                if (this.selectedItems.sauve[type][uuid]) {
                    delete this.selectedItems.sauve[type][uuid];
                } else {
                    this.selectedItems.sauve[type][uuid] = true;
                }

                console.log ('type : ',type, ' uuid', uuid);
                console.log('selectedItems :',this.selectedItems.sauve);
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