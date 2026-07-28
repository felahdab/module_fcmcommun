<?php

namespace Modules\FcmCommun\Http\Livewire\Concerns\LivretFcmLight;

use Livewire\Attributes\Computed;

trait HasDisplayHelpers
{
    // pour nettoyer les tooltip
    #[Computed]
    function cleanForTooltip(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // 1. Retirer les backslashes
        $text = stripslashes($text);

        // 2. Retirer les accents
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        // 3. Retirer tous les caractères spéciaux sauf lettres, chiffres, espaces et ponctuation basique
        $text = preg_replace('/[^a-zA-Z0-9\s\.\,\!\?\-\(\)]/', ' ', $text);

        // 4. Nettoyer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    public function getCompetenceCode(array $csf): string
    {
        return $csf['competence']['code'] ?? 'COMP';
    }

    public function getCompetenceColor(array $csf): string
    {
        return $csf['competence']['color'] ?? 'gray';
    }

    public function escapeForTooltip($text): string
    {
        if (empty($text)) {
            return '';
        }

        return htmlspecialchars(
            str_replace(["\r\n", "\n", "\r", "'", '"'], [' ', ' ', ' ', "&#39;", "&#34;"], $text),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    public function escapeForJs($text): string
    {
        if (empty($text)) {
            return '';
        }

        return addslashes(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }
}
