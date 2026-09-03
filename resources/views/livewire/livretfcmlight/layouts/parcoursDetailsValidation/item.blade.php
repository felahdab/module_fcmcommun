@php
    // --- Normalisation des infos ---
    if (empty($infos) || !is_array($infos)) {
        $infos = [];
    }

    $pieceJointeUrl = !empty($pieceJointe) && in_array($type, ['savoirfaire', 'objectif'], true)
        ? route('fcmcentral.piece-jointe.telecharger', [
            'marinUuid' => $this->marin->uuid,
            'type' => $type,
            'uuid' => $uuid,
        ])
        : '';

    // Uniformiser les clés selon le type
    if ($type === 'affectation') {
        $infos['competences'] = $infos['competences'] ?? [];
    }
    if ($type === 'competence') {
        $infos['competenceSavoirfaires'] = $infos['competenceSavoirfaires'] ?? [];
    }
    if ($type === 'savoirfaire') {
        $infos['objectifs_list'] = $infos['objectifs_list'] ?? [];
    }

    // Variables communes
    $indentation = match($type){
        'affectation' => 'ml-0',
        'competence' => 'ml-4',
        'savoirfaire' => 'ml-8',
        'objectif' => 'ml-12',
        default => '',
    };
    $couleurActuelle = $couleurLigne ?? 'gray';
    $colorClasses = 'fcm-item-' . $couleurActuelle;
    $textColorClasses = 'fcm-text-' . $couleurActuelle;

    // Bug pour les badges
    $customStyle="margin: 0px 4px 0px 4px;";
    if ($couleurLigne == "purple"){
        $customStyle .= "border-color: #800080;
        background-color: rgba(128, 0, 128, 0.15);
        color:#800080;";
    }
    if ($couleurLigne == "brown"){
        $customStyle .= "border-color: #8B4513;
        background-color: rgba(139, 69, 19, 0.15);
        color:#8B4513;;";
    }

    $isChecked = $etatValid ?? false;
    $isProposed = !empty($dateProposition);
    $sectionId = $type.'_'.$uuid;
    $tooltipText = !empty($libelleLong) ? $libelleLong : $label;

    // ✅ CODE BADGE POUR TOUS LES TYPES
    $codeElement = $code ?? match($type) {
        'affectation' => "AFF{$uuid}",
        'competence' => "C{$uuid}",
        'objectif' => "OBJ{$uuid}",
        default => $infos['code'] ?? ''
    };

    // PopUp Infos

    $popupInfos = $libelleLong;

    // Spécifique aux savoir-faire
    if ($type === 'savoirfaire') {
        $competenceCode = $infos['competence_code'] ?? 'COMP';
        $competenceColor = $infos['competence_color'] ?? 'gray';
        $savoirfaireTypeCode = $infos['savoirfaire_type_code'] ?? 'C';
        $savoirfaireTypeColor = $infos['savoirfaire_type_color'] ?? 'info';
        $isCompagnonnage = $savoirfaireTypeCode === 'C';

        $objectifs = $infos['objectifs_list'] ?? [];
        $totalObjectifs = count($objectifs);
        $objectifsValides = collect($objectifs)->where('etat_valid', true)->count();
        $pourcentage = $totalObjectifs > 0 ? round(($objectifsValides / $totalObjectifs) * 100) : 0;

        $libelleLongTronque = (str_replace(['&amp;#39;','$apos;'],"'",$libelleLong) ?? '');
        $hasObjectifs = $isCompagnonnage && $totalObjectifs > 0;

        $popupInfos = ($infos['savoirfaire_type_code'] <> 'C')
        ? $intitule
        : $libelleLong
        ;

    } else {
        $hasObjectifs = false;
    }

    // Vérifier si le marin est gelé/sedimentation

    $marinBloque = !$this->marinPeutProposerOuValider();

     // VÉRIFIER SI LE SAVOIR-FAIRE DOIT ÊTRE GRISÉ
     if ($type === 'savoirfaire') {
        $estGrise = in_array($uuid, $savoirfairesAGriser ?? []);
    } elseif ($type === 'objectif') {
        $estGrise = in_array($uuid, $objectifsAGriser ?? []);
    }

         //  Pour les savoir-faires grisés, vérifier s'ils ont des objectifs à afficher
    $hasObjectifsVisibles = false;
    if ($type === 'savoirfaire' && $hasObjectifs) {
        //  FIX: Vérifier qu'il y a bien des objectifs dans $infos
        $hasObjectifsVisibles = !empty($infos['objectifs']) || !empty($infos['objectifs_list']);
    }

    // Déterminer si on doit afficher le chevron
    $showChevron = (
        ($type === 'affectation' && !empty($infos['competences'])) ||
        ($type === 'competence' && !empty($infos['competenceSavoirfaires'])) ||
        ($type === 'savoirfaire' && $hasObjectifs)
    );

   // Les ACCENTS et apostrophes

    $commentairePropositionClean =$commentaireProposition ?? '';

    $commentaireClean = $commentaire ?? '';
    $tooltipTextClean = $tooltipText ?? '';
    $labelClean = $label ?? '';
    $popupInfosClean = $popupInfos ?? '';

    $tooltipPropositionAttente = 'En attente de validation depuis le ' . ($dateProposition ?? '') .
    '. Commentaire:' .  $this->cleanForTooltip($commentairePropositionClean);

    $tooltipPropositionModifie = 'Modifier votre proposition du ' . ($dateProposition ?? '') .
     '. Commentaire: ' .  $this->cleanForTooltip($commentairePropositionClean);

     $labelForJs = addslashes($labelClean);

@endphp



<div class="flex items-center justify-between mt1 mb1 {{ $indentation }}
@if(($type === 'savoirfaire')||($type === 'objectif'))
{{ $isChecked ?  $colorClasses : ($estGrise ? 'fcm-item-grayed' : 'fcm-item-gray')  }}
@else
{{  $colorClasses }}
@endif
"
     wire:key="item-{{ $uuid }}-{{ $isChecked ? 'checked' : 'unchecked' }}-{{ $isProposed ? 'proposed' : 'normal' }}"
     x-data="{
         isSelected() {
             return selectedItems['{{$type}}'] && selectedItems['{{$type}}']['{{$uuid}}'];
         }
     }">

    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item.action-icons')

    @include('fcmcommun::livewire.livretfcmlight.layouts.parcoursDetailsValidation.item.info-icons')
</div>
