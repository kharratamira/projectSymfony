<?php

namespace App\Entity;

enum VieContrat: string
{
    case EXPIRE = 'expire';
    case RENOUVELLE = 'renouvelle';
    case ACTIVE = 'active';
case EN_ATTENTE = 'en_attente';
case ANNULEE = 'annulee';
    public static function getValues(): array
    {
        return [
            self::EXPIRE->value,
            self::RENOUVELLE->value,
            self::ACTIVE->value,
            self::ANNULEE->value,
            self::EN_ATTENTE->value,
        ];
    }
}
