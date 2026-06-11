<?php
// src/Enum/BatchStatus.php
// Typage strict des statuts de lot (PHP 8.1+ native enum)

namespace PharmaFEFO\Enum;

enum BatchStatus: string
{
    case OK = 'OK';
    case WARNING = 'WARNING';
    case CRITICAL = 'CRITICAL';
    case EXPIRED = 'EXPIRED';
    case RETURN_PROCESS = 'RETURN_PROCESS';

    /**
     * Couleur associée (pour le code couleur de l'interface)
     */
    public function color(): string
    {
        return match ($this) {
            self::OK             => 'green',
            self::WARNING        => 'orange',
            self::CRITICAL       => 'red',
            self::EXPIRED        => 'gray',
            self::RETURN_PROCESS => 'blue',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::OK             => 'OK',
            self::WARNING        => 'Alerte (< 90 jours)',
            self::CRITICAL       => 'Critique (< 30 jours)',
            self::EXPIRED        => 'Périmé / À détruire',
            self::RETURN_PROCESS => 'Retour fournisseur',
        };
    }
}
