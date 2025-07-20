<?php

namespace Modules\FcmCommun\DataObjects\Enums;

enum UserGeneratedEventTypes: string
{
       
    case ATTRIBUER_COHORTE_MARIN                    = 'attribuer_cohorte_marin';
    case MARIN_EN_FCM                               = 'marin_en_fcm';
    case MARIN_PAS_FCM                              = 'marin_pas_fcm';
    case ATTRIBUER_MENTOR_MARIN                     = 'attribuer_mentor_marin';
    case ATTRIBUER_PARCOURS_SERIALISE_MARIN         = 'attribuer_parcours_serialise_marin';
    case CREATION_PARCOURS_SERIALISE                = 'creation_parcours_serialise';
    case VALID_ELMT_MARIN_PARCOURS                  = 'valid_elmt_marin_parcours';
    case ANNUL_ELMT_MARIN_PARCOURS                  = 'annul_elmt_marin_parcours';
    case PROPOS_ELMT_MARIN_PARCOURS                 = 'propos_elmt_marin_parcours';
    case ANNUL_PROPOS_ELMT_MARIN_PARCOURS           = 'annul_propos_elmt_marin_parcours';
    case VALID_PROPOS_ELMT_MARIN_PARCOURS           = 'valid_propos_elmt_marin_parcours';
    case RESTORE_ELMT_MARIN_PARCOURS                = 'restore_elmt_marin_parcours';
    case REBOOT_ELMT_MARIN_PARCOURS                 = 'reboot_elmt_marin_parcours';
   
}
