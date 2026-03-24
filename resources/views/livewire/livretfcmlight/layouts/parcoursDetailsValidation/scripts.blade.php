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
            date_validation: new Date().toISOString().split('T')[0],

            modification_proposition: false,
            a_proposition_mentor: {},  
            commentaire_proposition_existant: '',  
           
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

            debugStructure() {
                console.log('=== DEBUG STRUCTURE ===');
                console.log('savoirFaireChildren:', this.savoirFaireChildren);
               
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
                } else if (type === 'competence') {
                    this.handleCompetenceToggle(uuid, isNowChecked);
                } else if (type === 'affectation') {
                    this.handleAffectationToggle(uuid, isNowChecked);
                }
               
                console.log('Résultat final:', this.selectedItems);
            },

            // ✅ GESTION DU TOGGLE AFFECTATION (descendant)
            handleAffectationToggle(affectationUuid, isNowChecked) {
                console.log(`Affectation ${affectationUuid} ${isNowChecked ? 'COCHÉE' : 'DÉCOCHÉE'}`);
               
                const competences = this.getCompetencesAffectation(affectationUuid);
                console.log(`Compétences de l'affectation ${affectationUuid}:`, competences);
               
                competences.forEach(competenceUuid => {
                    if (isNowChecked) {
                        if (!this.selectedItems['competence']) this.selectedItems['competence'] = {};
                        this.selectedItems['competence'][competenceUuid] = true;
                        console.log(`→ Compétence ${competenceUuid} COCHÉE`);
                        this.handleCompetenceToggle(competenceUuid, true);
                    } else {
                        if (this.selectedItems['competence']?.[competenceUuid]) {
                            delete this.selectedItems['competence'][competenceUuid];
                            console.log(`→ Compétence ${competenceUuid} DÉCOCHÉE`);
                            this.handleCompetenceToggle(competenceUuid, false);
                        }
                    }
                });
               
                if (!isNowChecked && this.selectedItems['competence'] && Object.keys(this.selectedItems['competence']).length === 0) {
                    delete this.selectedItems['competence'];
                }
            },

            // ✅ GESTION DU TOGGLE COMPÉTENCE (descendant)
            handleCompetenceToggle(competenceUuid, isNowChecked) {
                console.log(`Compétence ${competenceUuid} ${isNowChecked ? 'COCHÉE' : 'DÉCOCHÉE'}`);
               
                const savoirfaires = this.getSavoirfairesCompetence(competenceUuid);
                console.log(`Savoir-faires de la compétence ${competenceUuid}:`, savoirfaires);
               
                savoirfaires.forEach(sfUuid => {
                    if (isNowChecked) {
                        if (!this.selectedItems['savoirfaire']) this.selectedItems['savoirfaire'] = {};
                        this.selectedItems['savoirfaire'][sfUuid] = true;
                        console.log(`→ Savoir-faire ${sfUuid} COCHÉ`);
                        this.handleSavoirFaireToggle(sfUuid, true);
                    } else {
                        if (this.selectedItems['savoirfaire']?.[sfUuid]) {
                            delete this.selectedItems['savoirfaire'][sfUuid];
                            console.log(`→ Savoir-faire ${sfUuid} DÉCOCHÉ`);
                            this.handleSavoirFaireToggle(sfUuid, false);
                        }
                    }
                });
               
                if (!isNowChecked && this.selectedItems['savoirfaire'] && Object.keys(this.selectedItems['savoirfaire']).length === 0) {
                    delete this.selectedItems['savoirfaire'];
                }
            },

            // ✅ GESTION DU TOGGLE SAVOIR-FAIRE (descendant)
            handleSavoirFaireToggle(savoirfaireUuid, isNowChecked) {
                console.log(`Savoir-faire ${savoirfaireUuid} ${isNowChecked ? 'COCHÉ' : 'DÉCOCHÉ'}`);
               
                const enfants = this.getEnfantsSavoirFaire(savoirfaireUuid);
                console.log(`Enfants du savoir-faire ${savoirfaireUuid}:`, enfants);
               
                if (isNowChecked) {
                    if (!this.selectedItems['objectif']) this.selectedItems['objectif'] = {};
                    enfants.forEach(childUuid => {
                        this.selectedItems['objectif'][childUuid] = true;
                        console.log(`→ Objectif ${childUuid} COCHÉ`);
                    });
                } else {
                    enfants.forEach(childUuid => {
                        if (this.selectedItems['objectif']?.[childUuid]) {
                            delete this.selectedItems['objectif'][childUuid];
                            console.log(`→ Objectif ${childUuid} DÉCOCHÉ`);
                        }
                    });
                   
                    if (this.selectedItems['objectif'] && Object.keys(this.selectedItems['objectif']).length === 0) {
                        delete this.selectedItems['objectif'];
                    }
                }
            },

            // ✅ GESTION DU TOGGLE OBJECTIF (remontant)
            handleObjectifToggle(objectifUuid, isNowChecked) {
                console.log(`Objectif ${objectifUuid} ${isNowChecked ? 'COCHÉ' : 'DÉCOCHÉ'}`);
               
                const parentSavoirFaire = this.findParentSavoirFaire(objectifUuid);
               
                if (parentSavoirFaire) {
                    console.log(`Parent savoir-faire: ${parentSavoirFaire}`);
                   
                    const tousLesObjectifs = this.getEnfantsSavoirFaire(parentSavoirFaire);
                    console.log('Tous les objectifs du parent:', tousLesObjectifs);
                   
                    const allChecked = tousLesObjectifs.every(objUuid => 
                        this.selectedItems['objectif']?.[objUuid]
                    );
                   
                    console.log('Tous les objectifs cochés?', allChecked);
                   
                    if (allChecked) {
                        if (!this.selectedItems['savoirfaire']) this.selectedItems['savoirfaire'] = {};
                        this.selectedItems['savoirfaire'][parentSavoirFaire] = true;
                        console.log(`→ Savoir-faire parent ${parentSavoirFaire} COCHÉ`);
                       
                        // ✅ REMONTER À LA COMPÉTENCE
                        const parentCompetence = this.findParentCompetence(parentSavoirFaire);
                        if (parentCompetence) {
                            this.checkAndToggleCompetence(parentCompetence);
                        }
                    } else {
                        if (this.selectedItems['savoirfaire']?.[parentSavoirFaire]) {
                            delete this.selectedItems['savoirfaire'][parentSavoirFaire];
                            console.log(`→ Savoir-faire parent ${parentSavoirFaire} DÉCOCHÉ`);
                           
                            if (Object.keys(this.selectedItems['savoirfaire']).length === 0) {
                                delete this.selectedItems['savoirfaire'];
                            }
                           
                            // ✅ DÉCOCHER LA COMPÉTENCE PARENT
                            const parentCompetence = this.findParentCompetence(parentSavoirFaire);
                            if (parentCompetence && this.selectedItems['competence']?.[parentCompetence]) {
                                delete this.selectedItems['competence'][parentCompetence];
                                this.checkAndToggleAffectation(parentCompetence);
                            }
                        }
                    }
                }
            },

            // ✅ VÉRIFIER ET TOGGLE COMPÉTENCE SI TOUS SES SF SONT COCHÉS
            checkAndToggleCompetence(competenceUuid) {
                const savoirfaires = this.getSavoirfairesCompetence(competenceUuid);
                const allChecked = savoirfaires.every(sfUuid => this.selectedItems['savoirfaire']?.[sfUuid]);
               
                if (allChecked) {
                    if (!this.selectedItems['competence']) this.selectedItems['competence'] = {};
                    this.selectedItems['competence'][competenceUuid] = true;
                    console.log(`→ Compétence ${competenceUuid} COCHÉE (tous SF cochés)`);
                   
                    const parentAffectation = this.findParentAffectation(competenceUuid);
                    if (parentAffectation) {
                        this.checkAndToggleAffectation(parentAffectation);
                    }
                } else {
                    if (this.selectedItems['competence']?.[competenceUuid]) {
                        delete this.selectedItems['competence'][competenceUuid];
                        console.log(`→ Compétence ${competenceUuid} DÉCOCHÉE (SF manquants)`);
                    }
                }
            },

            // ✅ VÉRIFIER ET TOGGLE AFFECTATION SI TOUTES SES COMPÉTENCES SONT COCHÉES
            checkAndToggleAffectation(affectationUuid) {
                const competences = this.getCompetencesAffectation(affectationUuid);
                const allChecked = competences.every(compUuid => this.selectedItems['competence']?.[compUuid]);
               
                if (allChecked) {
                    if (!this.selectedItems['affectation']) this.selectedItems['affectation'] = {};
                    this.selectedItems['affectation'][affectationUuid] = true;
                    console.log(`→ Affectation ${affectationUuid} COCHÉE (toutes compétences cochées)`);
                } else {
                    if (this.selectedItems['affectation']?.[affectationUuid]) {
                        delete this.selectedItems['affectation'][affectationUuid];
                        console.log(`→ Affectation ${affectationUuid} DÉCOCHÉE (compétences manquantes)`);
                    }
                }
            },

            // ✅ MÉTHODES HELPER POUR TROUVER LES PARENTS
            findParentAffectation(competenceUuid) {
                if (!this.parcours?.affectations) return null;
               
                for (const affectation of this.parcours.affectations) {
                    if (affectation.competences?.some(c => c.uuid === competenceUuid)) {
                        return affectation.uuid;
                    }
                }
                return null;
            },

            findParentCompetence(savoirfaireUuid) {
                if (!this.parcours?.affectations) return null;
               
                for (const affectation of this.parcours.affectations) {
                    for (const competence of affectation.competences || []) {
                        for (const csf of competence.competenceSavoirfaires || []) {
                            const csfUuid = csf.uuid || `csf_${csf.id}`;
                            if (csfUuid === savoirfaireUuid) {
                                return competence.uuid;
                            }
                        }
                    }
                }
                return null;
            },

            findParentSavoirFaire(objectifUuid) {
                console.log(`Recherche du parent pour l'objectif: ${objectifUuid}`);
               
                // ✅ 1. Chercher dans savoirFaireChildren
                for (const [pivotKey, objectifs] of Object.entries(this.savoirFaireChildren)) {
                    if (objectifs.includes(objectifUuid)) {
                        console.log(`Trouvé dans pivot ${pivotKey}`);
                        return pivotKey;
                    }
                }
               
                // ✅ 2. Fallback: chercher dans le parcours
                if (this.parcours?.affectations) {
                    for (const affectation of this.parcours.affectations) {
                        for (const competence of affectation.competences || []) {
                            for (const csf of competence.competenceSavoirfaires || []) {
                                for (const obj of csf.objectifs || []) {
                                    const objUuid = obj.uuid || `obj_${csf.id}_${obj.id}`;
                                    if (objUuid === objectifUuid) {
                                        return csf.uuid || `csf_${csf.id}`;
                                    }
                                }
                            }
                        }
                    }
                }
               
                return null;
            },

            // ✅ MÉTHODES HELPER POUR RÉCUPÉRER LES ENFANTS
            getCompetencesAffectation(affectationUuid) {
                if (!this.parcours?.affectations) return [];
               
                const affectation = this.parcours.affectations.find(a => a.uuid === affectationUuid);
                return affectation?.competences?.map(c => c.uuid) || [];
            },

            getSavoirfairesCompetence(competenceUuid) {
                if (!this.parcours?.affectations) return [];
               
                for (const affectation of this.parcours.affectations) {
                    const competence = affectation.competences?.find(c => c.uuid === competenceUuid);
                    if (competence) {
                        return competence.competenceSavoirfaires?.map(csf => csf.uuid || `csf_${csf.id}`) || [];
                    }
                }
                return [];
            },

            getEnfantsSavoirFaire(savoirfaireUuid) {
                console.log(`Recherche enfants pour: ${savoirfaireUuid}`);
               
                // ✅ 1. Essayer savoirFaireChildren
                if (this.savoirFaireChildren[savoirfaireUuid]) {
                    return this.savoirFaireChildren[savoirfaireUuid];
                }
               
                // ✅ 2. Fallback: chercher dans le parcours
                if (this.parcours?.affectations) {
                    for (const affectation of this.parcours.affectations) {
                        for (const competence of affectation.competences || []) {
                            for (const csf of competence.competenceSavoirfaires || []) {
                                const csfUuid = csf.uuid || `csf_${csf.id}`;
                                if (csfUuid === savoirfaireUuid) {
                                    return csf.objectifs?.map(obj => obj.uuid || `obj_${csf.id}_${obj.id}`) || [];
                                }
                            }
                        }
                    }
                }
               
                return [];
            },
           
            initLivret() {
                this.init();
            }
        }
    }
</script>
