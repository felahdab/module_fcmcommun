<script>

window.savoirfaireChildren = @json($savoirFaireChildren);
    window.competenceChildren = @json($competenceChildren);
    window.fonctionChildren = @json($fonctionChildren);


    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.getElementById('openAValiderModal')?.click();
        }, 0);
    });

   

    
    function parcoursManager() {
        return {
            sections: {},
            savoirFaireChildren: window.savoirfaireChildren || {},
competenceChildren: window.competenceChildren || {},
fonctionChildren: window.fonctionChildren || {},

            selectedItems:{
                sauve: {},
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

            toggleCheck(sectionId) {
               
               
                const [type, uuid] = sectionId.split('_');
                if (!this.selectedItems.sauve[type]) {
                    this.selectedItems.sauve[type] = {};
                }

                // Cas savoirfaire :

                  if (type === 'activites') {
        // Toggle l'activité
        if (this.selectedItems.sauve[type][uuid]) {
            delete this.selectedItems.sauve[type][uuid];
        } else {
            this.selectedItems.sauve[type][uuid] = true;
        }

        // Trouver le savoir-faire parent de cette activité
        // Parcours le mapping savoirFaireChildren pour retrouver le parent
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
                actUuid => this.selectedItems.sauve['activites'] && this.selectedItems.sauve['activites'][actUuid]
            );
            if (allChecked) {
                // Cocher le savoir-faire parent
                if (!this.selectedItems.sauve['savoirfaires']) this.selectedItems.sauve['savoirfaires'] = {};
                this.selectedItems.sauve['savoirfaires'][parentSavoirFaire] = true;
            } else {
                // Décocher le savoir-faire parent
                if (this.selectedItems.sauve['savoirfaires'] && this.selectedItems.sauve['savoirfaires'][parentSavoirFaire]) {
                    delete this.selectedItems.sauve['savoirfaires'][parentSavoirFaire];
                    if (Object.keys(this.selectedItems.sauve['savoirfaires']).length === 0) {
                        delete this.selectedItems.sauve['savoirfaires'];
                    }
                }
            }
        }
        console.log ('type : ',type, ' uuid', uuid);
                            console.log('selectedItems :',this.selectedItems.sauve);
        return;
    }


                if (type === 'savoirfaires') {
                    
                    const enfants = savoirfaireChildren && savoirfaireChildren[uuid] ? savoirfaireChildren[uuid] : [];
                    const isChecked = !!this.selectedItems.sauve[type][uuid];

                    //console.log(enfants);
                   
                    /** 
                     * LE double !! convertit la valeur en booléen (true or false)
                    * si this.selectedItems[type][uuid] vaut true alors isChecked sera true
                    * si this.selectedItems[type][uuid] faut false or undefinde alors isChecked sera false
                    */


                    if (isChecked) {
                        console.log(' dans la boucle');
                        // Décocher le savoirfaire => décocher tous ses enfants cochés
                        delete this.selectedItems.sauve[type][uuid];
                        enfants.forEach(childUuid => {
                            if (this.selectedItems.sauve['activites'] && this.selectedItems.sauve['activites'][childUuid]) {
                                delete this.selectedItems.sauve['activites'][childUuid];
                            }
                        });
                        // Nettoyage si plus rien
                        if (this.selectedItems.sauve['activites'] && Object.keys(this.selectedItems.sauve['activites']).length === 0) {
                            delete this.selectedItems.sauve['activites'];
                        }
                        if (Object.keys(this.selectedItems.sauve[type]).length === 0) {
                            delete this.selectedItems.sauve[type];
                        }
                    } else {
                        // Cocher le savoirfaire => cocher tous ses enfants non cochés
                        this.selectedItems.sauve[type][uuid] = true;
                        if (!this.selectedItems.sauve['activites']) this.selectedItems.sauve['activites'] = {};
                        enfants.forEach(childUuid => {
                            this.selectedItems.sauve['activites'][childUuid] = true;
                        });
                    }
                } 
                // else {
                //     // Cas normal (activité, etc)
                //     if (this.selectedItems[type][uuid]) {
                //         delete this.selectedItems[type][uuid];
                //         if (Object.keys(this.selectedItems[type]).length === 0) {
                //             delete this.selectedItems[type];
                //         }
                //     } else {
                //         this.selectedItems[type][uuid] = true;
                //     }
                // }

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
            
            initSections() {
                this.loadFromStorage();
            }
        }
    }
    </script>