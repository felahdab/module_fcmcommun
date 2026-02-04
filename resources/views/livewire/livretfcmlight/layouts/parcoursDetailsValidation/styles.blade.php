<style>
    /* CSS de base inchangé */
    .collapsible { transition: all 0.3s ease-in-out; }
    .highlight { border: 2px solid #f0f8ff; transition: all 0.3s ease-in-out; padding: 25px; border-radius: 10px; }
    .mt1{margin-top:15px;} .mb1{margin-bottom:25px;} .mb-1{margin-bottom:10px;} .mb-2{margin-bottom:20px;}
    .mr1{margin-right:10px;} .ml2{margin-left:30px;} .ml1{margin-left:15px;} .ml-1{margin-left:10px;} .ml-2{margin-left:20px;} .ml4{margin-left:60px;}
    .mt3{margin-top:30px;}
    .text-success{ color:lawngreen; }
    .tree-checkbox:hover { transform: scale(1.1); transition: transform 0.2s ease; }

    /* ✅ CLASSES COULEUR CONDITIONNELLES */
    .fcm-item-success { border-left: 4px solid #10b981; background-color: rgba(16, 185, 129, 0.1); padding: 8px; border-radius: 4px; }
    .fcm-item-warning { border-left: 4px solid #f59e0b; background-color: rgba(245, 158, 11, 0.1); padding: 8px; border-radius: 4px; }
    .fcm-item-danger { border-left: 4px solid #ef4444; background-color: rgba(239, 68, 68, 0.1); padding: 8px; border-radius: 4px; }
    .fcm-item-info { border-left: 4px solid #0ea5e9; background-color: rgba(14, 165, 233, 0.1); padding: 8px; border-radius: 4px; }
    .fcm-item-gray { border-left: 4px solid #6b7280; background-color: rgba(107, 114, 128, 0.1); padding: 8px; border-radius: 4px; }
    .fcm-item-grayed { border-left: 4px solid #c3c7ce; background-color: rgba(166, 169, 175, 0.1); padding: 8px; border-radius: 4px; }

    /* Classes de texte */
    .fcm-text-success { color: #10b981; font-weight: bold; }
    .fcm-text-warning { color: #f59e0b; font-weight: bold; }
    .fcm-text-danger { color: #ef4444; font-weight: bold; }
    .fcm-text-info { color: #0ea5e9; font-weight: bold; }
    .fcm-text-gray { color: #6b7280; font-weight: bold; }
    .fcm-text-grayed { color: #313235; font-weight: bold; }

    /* États hover */
    .fcm-item-success:hover { background-color: rgba(16, 185, 129, 0.15); }
    .fcm-item-warning:hover { background-color: rgba(245, 158, 11, 0.15); }
    .fcm-item-danger:hover { background-color: rgba(239, 68, 68, 0.15); }
    .fcm-item-info:hover { background-color: rgba(14, 165, 233, 0.15); }
    .fcm-item-gray:hover { background-color: rgba(107, 114, 128, 0.15); }
    .fcm-item-grayed:hover { background-color: rgba(107, 114, 128, 0.15); }

    /* ✅ BARRES DE PROGRESSION COLORÉES */
    .progress-fill-success { background-color: #10b981; }
    .progress-fill-warning { background-color: #f59e0b; }
    .progress-fill-danger { background-color: #ef4444; }
    .progress-fill-info { background-color: #0ea5e9; }
    .progress-fill-gray { background-color: #6b7280; }
    .progress-fill-grayed { background-color: #6b7280; }

    /* Boutons existants */
    .btn-base { font-weight: bold; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 2px solid; transition: all 0.2s ease-in-out; transform: scale(1); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
    .btn-green { background-color: #16a34a; color: #ffffff; border-color: #22c55e; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    .btn-green:hover { background-color: #15803d; border-color: #16a34a; transform: scale(1.05); }
    .btn-green:focus { outline: 2px solid transparent; outline-offset: 2px; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.5); }
    .btn-green:active { transform: scale(0.98); }

    /* Marron */
.fcm-item-brown { border-left: 4px solid #8B4513; background-color: rgba(139, 69, 19, 0.08); padding: 8px; border-radius: 4px; }
.fcm-text-brown { color: #8B4513; font-weight: bold; }
.fcm-item-brown:hover { background-color: rgba(139, 69, 19, 0.15); }
.progress-fill-brown { background-color: #8B4513; }

/* Violet */
.fcm-item-purple { border-left: 4px solid #800080; background-color: rgba(128, 0, 128, 0.08); padding: 8px; border-radius: 4px; }
.fcm-text-purple { color: #800080; font-weight: bold; }
.fcm-item-purple:hover { background-color: rgba(128, 0, 128, 0.15); }
.progress-fill-purple { background-color: #800080; }

.guillemets::before { content :"« "}
.guillemets::after { content :" »"}

.fcm-item-grayed {
    background-color: #f3f4f6 !important;
    opacity: 0.7;
    border-left: 3px solid #d1d5db;
}



/* ✅ Désactiver seulement les boutons de validation/proposition */
.fcm-item-grayed .tree-checkbox,
.fcm-item-grayed button[x-on\:click*="toggleCheck"],
.fcm-item-grayed button[x-on\:click*="a_proposer"] {
    pointer-events: none;
    opacity: 0.3;
}

/* ✅ Laisser les boutons info et chevron actifs */
.fcm-item-grayed button[x-on\:click*="infos"],
.fcm-item-grayed button[x-on\:click*="toggleSection"] {
    pointer-events: auto;
    opacity: 1;
}

.jaunepale{background-color: #FFE600;color: #FFE600;}
.orangefort{background-color: #FF6F00;color: #FF6F00;}

.fi-color-jaunepale {
    background-color: #FFE600 !important;
    color: #7a6a00 !important; /* texte foncé pour contraste */
    border-color: #FFE600 !important;
}

/* Orange fort */
.fi-color-orangefort {
    background-color: #FF6F00 !important;
    color: #fff !important; /* texte blanc pour contraste */
    border-color: #FF6F00 !important;
}




</style>
