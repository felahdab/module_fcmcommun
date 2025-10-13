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
           
            clearSelections() {
                this.selectedItems = {};
                console.log('✅ Sélections vidées');
            },
           
            init() {
                this.loadFromStorage();
               
                this.$wire.on('clear-selections', () => {
                    this.clearSelections();
                });
               
                document.addEventListener('livewire:updated', () => {
                    console.log('✅ Livewire updated - data refreshed');
                });

                console.log('✅ Parcours Manager initialisé');
                console.log('✅ savoirFaireChildren:', this.savoirFaireChildren);
                this.debugStructure();
            },

            // ✅ DEBUG POUR COMPRENDRE LA STRUCTURE
            debugStructure() {
                console.log('=== DEBUG STRUCTURE ===');
                console.log('savoirFaireChildren:', this.savoirFaireChildren);
               
                // Analyser les clés pour comprendre le format
                for (const [key, values] of Object.entries(this.savoirFaireChildren)) {
                    console.log(`Clé: "${key}" → Valeurs:`, values);
                }
            },
           
            hasSelectedItems() {
                return (this.selectedItems['objectif'] && Object.keys(this.selectedItems['objectif']).length > 0) ||
                       (this.selectedItems['savoirfaire'] && Object.keys(this.selectedItems['savoirfaire']).length > 0);
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
           
            // ✅ TOGGLE CHECK PRINCIPAL
            toggleCheck(sectionId) {
                console.log('=== TOGGLE CHECK ===', sectionId);
               
                const parts = sectionId.split('_');
                const type = parts[0];
                const uuid = parts.slice(1).join('_');
               
                console.log('Type:', type, 'UUID:', uuid);

                if (!this.selectedItems[type]) {
                    this.selectedItems[type] = {};
                }
               
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
               
                const isNowChecked = !wasChecked;
                console.log('État après:', isNowChecked ? 'COCHÉ' : 'DÉCOCHÉ');
               
                // ✅ GESTION SPÉCIALE SELON LE TYPE
                if (type === 'objectif') {
                    this.handleObjectifToggle(uuid, isNowChecked);
                } else if (type === 'savoirfaire') {
                    this.handleSavoirFaireToggle(uuid, isNowChecked);
                }
               
                console.log('Résultat final:', this.selectedItems);
            },

            // ✅ GESTION DU TOGGLE SAVOIR-FAIRE (descendant)
            handleSavoirFaireToggle(savoirfaireUuid, isNowChecked) {
                console.log(`Savoir-faire ${savoirfaireUuid} ${isNowChecked ? 'COCHÉ' : 'DÉCOCHÉ'}`);
               
                const enfants = this.getEnfantsSavoirFaire(savoirfaireUuid);
                console.log(`Enfants du savoir-faire ${savoirfaireUuid}:`, enfants);
               
                if (isNowChecked) {
                    // ✅ COCHER tous les objectifs enfants
                    if (!this.selectedItems['objectif']) {
                        this.selectedItems['objectif'] = {};
                    }
                    enfants.forEach(childUuid => {
                        this.selectedItems['objectif'][childUuid] = true;
                        console.log(`→ Objectif ${childUuid} COCHÉ`);
                    });
                } else {
                    // ✅ DÉCOCHER tous les objectifs enfants
                    enfants.forEach(childUuid => {
                        if (this.selectedItems['objectif'] && this.selectedItems['objectif'][childUuid]) {
                            delete this.selectedItems['objectif'][childUuid];
                            console.log(`→ Objectif ${childUuid} DÉCOCHÉ`);
                        }
                    });
                   
                    // Nettoyer si plus d'objectifs
                    if (this.selectedItems['objectif'] && Object.keys(this.selectedItems['objectif']).length === 0) {
                        delete this.selectedItems['objectif'];
                    }
                }
            },

            // ✅ GESTION DU TOGGLE OBJECTIF (remontant) - CORRIGÉ
            handleObjectifToggle(objectifUuid, isNowChecked) {
                console.log(`Objectif ${objectifUuid} ${isNowChecked ? 'COCHÉ' : 'DÉCOCHÉ'}`);
               
                // ✅ Trouver le savoir-faire parent
                const parentSavoirFaire = this.findParentSavoirFaire(objectifUuid);
               
                if (parentSavoirFaire) {
                    console.log(`Parent trouvé: ${parentSavoirFaire}`);
                   
                    // ✅ Récupérer TOUS les objectifs de ce savoir-faire
                    const tousLesObjectifs = this.getEnfantsSavoirFaire(parentSavoirFaire);
                    console.log('Tous les objectifs du parent:', tousLesObjectifs);
                   
                    // ✅ Vérifier si TOUS les objectifs sont maintenant cochés
                    const allChecked = tousLesObjectifs.every(objUuid => {
                        const isChecked = this.selectedItems['objectif'] && this.selectedItems['objectif'][objUuid];
                        console.log(`Objectif ${objUuid}: ${isChecked ? 'COCHÉ' : 'DÉCOCHÉ'}`);
                        return isChecked;
                    });
                   
                    console.log('Tous les objectifs cochés?', allChecked);
                   
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
                } else {
                    console.log(`❌ Aucun parent trouvé pour l'objectif ${objectifUuid}`);
                }
            },

            // ✅ NOUVELLE MÉTHODE POUR TROUVER LE PARENT D'UN OBJECTIF
            findParentSavoirFaire(objectifUuid) {
                console.log(`Recherche du parent pour l'objectif: ${objectifUuid}`);
               
                // ✅ 1. Chercher dans savoirFaireChildren (mapping pivot → objectifs)
                for (const [pivotKey, objectifs] of Object.entries(this.savoirFaireChildren)) {
                    if (objectifs.includes(objectifUuid)) {
                        console.log(`Trouvé dans pivot ${pivotKey}, recherche du savoir-faire correspondant...`);
                       
                        // ✅ 2. Trouver le savoir-faire qui correspond à ce pivot
                        const savoirfaireUuid = this.findSavoirFaireUuidFromPivot(pivotKey);
                        if (savoirfaireUuid) {
                            console.log(`Parent trouvé: ${savoirfaireUuid}`);
                            return savoirfaireUuid;
                        }
                    }
                }
               
                // ✅ 3. Fallback: chercher directement dans les données du parcours
                if (this.parcours && this.parcours.annees) {
                    for (const annee of this.parcours.annees) {
                        if (annee.savoirfaires) {
                            for (const sf of annee.savoirfaires) {
                                if (sf.objectifs) {
                                    for (const obj of sf.objectifs) {
                                        const objUuid = obj.uuid || `obj_${sf.pivot?.id}_${obj.id}`;
                                        if (objUuid === objectifUuid) {
                                            const sfUuid = sf.uuid + '_' + (sf.pivot?.uuid || sf.pivot?.id || '');
                                            console.log(`Parent trouvé via parcours: ${sfUuid}`);
                                            return sfUuid;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
               
                console.log(`❌ Aucun parent trouvé pour ${objectifUuid}`);
                return null;
            },

            // ✅ MÉTHODES HELPER AMÉLIORÉES
            findSavoirFaireUuidFromPivot(pivotKey) {
                console.log(`Recherche savoir-faire pour pivot: ${pivotKey}`);
               
                if (this.parcours && this.parcours.annees) {
                    for (const annee of this.parcours.annees) {
                        if (annee.savoirfaires) {
                            for (const sf of annee.savoirfaires) {
                                // ✅ Vérifier différents formats de clé pivot
                                const pivotId = sf.pivot?.id?.toString();
                                const pivotUuid = sf.pivot?.uuid?.toString();
                                const pivotKey1 = `pivot_${pivotId}`;
                               
                                if (pivotId === pivotKey ||
                                    pivotUuid === pivotKey ||
                                    pivotKey1 === pivotKey ||
                                    `pivot_${sf.pivot?.id}` === pivotKey) {
                                   
                                    const sfUuid = sf.uuid + '_' + (sf.pivot?.uuid || sf.pivot?.id || '');
                                    console.log(`Savoir-faire trouvé: ${sfUuid} pour pivot ${pivotKey}`);
                                    return sfUuid;
                                }
                            }
                        }
                    }
                }
               
                console.log(`❌ Aucun savoir-faire trouvé pour pivot ${pivotKey}`);
                return null;
            },

            getEnfantsSavoirFaire(savoirfaireUuid) {
                console.log(`Recherche enfants pour: ${savoirfaireUuid}`);
               
                // ✅ 1. Essayer de trouver directement dans savoirFaireChildren
                if (this.savoirFaireChildren[savoirfaireUuid]) {
                    console.log('Trouvé directement:', this.savoirFaireChildren[savoirfaireUuid]);
                    return this.savoirFaireChildren[savoirfaireUuid];
                }
               
                // ✅ 2. Essayer d'extraire le pivot ID depuis l'UUID
                const parts = savoirfaireUuid.split('_');
                if (parts.length >= 2) {
                    const lastPart = parts[parts.length - 1];
                    const pivotKey = `pivot_${lastPart}`;
                   
                    if (this.savoirFaireChildren[pivotKey]) {
                        console.log(`Trouvé avec clé pivot ${pivotKey}:`, this.savoirFaireChildren[pivotKey]);
                        return this.savoirFaireChildren[pivotKey];
                    }
                   
                    if (this.savoirFaireChildren[lastPart]) {
                        console.log(`Trouvé avec ID ${lastPart}:`, this.savoirFaireChildren[lastPart]);
                        return this.savoirFaireChildren[lastPart];
                    }
                }
               
                // ✅ 3. Fallback: chercher dans les données du parcours
                const enfantsFromParcours = this.findObjectifsFromParcours(savoirfaireUuid);
                console.log('Trouvé dans parcours:', enfantsFromParcours);
                return enfantsFromParcours;
            },

            findObjectifsFromParcours(savoirfaireUuid) {
                const objectifs = [];
                if (this.parcours && this.parcours.annees) {
                    for (const annee of this.parcours.annees) {
                        if (annee.savoirfaires) {
                            for (const sf of annee.savoirfaires) {
                                const sfUuid = sf.uuid + '_' + (sf.pivot?.uuid || sf.pivot?.id || '');
                                if (sfUuid === savoirfaireUuid && sf.objectifs) {
                                    sf.objectifs.forEach(obj => {
                                        const objUuid = obj.uuid || `obj_${sf.pivot?.id}_${obj.id}`;
                                        objectifs.push(objUuid);
                                    });
                                }
                            }
                        }
                    }
                }
                return objectifs;
            },
           
            initLivret() {
                this.init();
            }
        }
    }
</script>

