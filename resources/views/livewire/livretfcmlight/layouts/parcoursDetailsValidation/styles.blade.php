<style>
    .collapsible {
        transition: all 0.3s ease-in-out;
        
    }


    .highlight {
    border: 2px solid #f0f8ff; /* Bordure bleue */

    transition: all 0.3s ease-in-out; /* Transition fluide */
    padding: 25px;
    border-radius: 10px;
    }


    .mt1{margin-top:15px;}
    .mb1{margin-bottom:25px;}
    .mb-1{margin-bottom:10px;}
    .mb-2{margin-bottom:20px;}
    .mr1{margin-right:10px;}
    .ml2{margin-left:30px;}
    .ml1{margin-left:15px;}
    .ml-1{margin-left:10px;}
    .ml-2{margin-left:20px;}

    .text-success{
        color:lawngreen;
    }

    /* Couleurs personnalisées */
    .fcm-fonction {
    border-left: 4px solid #389D60;
    background-color: #f0f9ff;
    background-color: rgba(56, 157, 96, 0.1);
    padding:8px;
    border-radius: 4px;
}

.fcm-competence {
    border-left: 4px solid #6AADE8;
    background-color: rgba(106, 173, 232, 0.1);
    padding:8px;
    border-radius: 4px;
}

.fcm-savoirfaire {
    border-left: 4px solid #e67b47;
    background-color: rgba(230, 123, 71, 0.1);
    padding:8px;
    border-radius: 4px;
}

.fcm-activite {
    border-left: 2px solid #d1d5db;
    background-color: #f9fafb;
    padding:8px;
    border-radius: 4px;
}

/* Classes de texte */
.fcm-fonction-text {
    color: #389D60;
    font-weight: bold;
}

.fcm-competence-text {
    color: #6AADE8;
    font-weight: 600;
}

.fcm-savoirfaire-text {
    color: #e67b47;
    font-weight: 500;
}

.fcm-activite-text {
    color: #374151;
}

/* États hover */
.fcm-fonction:hover {
    background-color: rgba(56, 157, 96, 0.15);
}

.fcm-competence:hover {
    background-color: rgba(106, 173, 232, 0.15);
}

.fcm-savoirfaire:hover {
    background-color: rgba(230, 123, 71, 0.15);
}

.fcm-activite:hover {
    background-color: #f3f4f6;
}


/* Animation pour le bouton fixe */
.tree-checkbox:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Bouton de base */
.btn-base {
    font-weight: bold;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    border: 2px solid;
    transition: all 0.2s ease-in-out;
    transform: scale(1);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Variante verte */
.btn-green {
    background-color: #16a34a;
    color: #ffffff;
    border-color: #22c55e;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.btn-green:hover {
    background-color: #15803d;
    border-color: #16a34a;
    transform: scale(1.05);
}

.btn-green:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.5);
}

.btn-green:active {
    transform: scale(0.98);
}




</style>

