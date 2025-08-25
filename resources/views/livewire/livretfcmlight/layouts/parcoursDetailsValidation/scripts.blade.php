<script>
   
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

                if ( ! this.selectedItems[type]) {
                    this.selectedItems[type] = {};
                }

                if (this.selectedItems[type][uuid]) {
                    delete this.selectedItems[type][uuid];
                } else {
                    this.selectedItems[type][uuid] = true;
                }

                console.log ('type : ',type, ' uuid', uuid);
                console.log('selectedItems :',this.selectedItems);
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