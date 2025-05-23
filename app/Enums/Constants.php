<?php

namespace Modules\FcmCommun\Enums;

class Constants
{
    // FLASH NOTIFICATIONS FILAMENT
    // COHORTE
    public const COHORTE = [
        'title' => 'Attribution Cohorte ',
        'message' => 'Le marin %s a été ajouté à la cohorte.',
    ];

    public const MENTOR = [
        'title' => 'Attribution Mentor',
        'message' => 'Le marin %s a été assigné à un mentor.',
    ];

    public const MARIN_EN_FCM = [
        'title' => 'Marin en FCM',
        'message' => 'Le marin %s est maintenant en FCM.',
    ];

    public const MARIN_PAS_FCM = [
        'title' => 'Marin hors FCM',
        'message' => 'Le marin %s n\'est plus  en FCM.',
    ];

    const PARCOURS = [
        'title' => 'Creation Parcours Serialise',
        'message' => 'Le parcours %s a été serialisé.',
    ];

    const MARIN_PARCOURS = [
        'title' => 'Attribution Marin - Parcours',
        'message' => 'Un parcours a été assigné au  marin %s .',
    ];

    const VALID_MARIN_PARCOURS = [
        'title' => 'Mise a Jour  Marin - Parcours',
        'message' => 'Enregistrement un/des element(s) Marin Parcours au  marin %s .',
    ];
    

    /**
     * Récupère et formate un message dynamique en fonction du type.
     */
    public static function getMess(array $type, string $marin): array
    {
        return [
            'title' => $type['title'], // Titre statique
            'message' => sprintf($type['message'], $marin), // Message formaté
        ];
    }


   
}
