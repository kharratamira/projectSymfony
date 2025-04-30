<?php

namespace App\Entity;
enum StatutAutorisation: string
{
    case EN_ATTENTE = 'en_attente';
    case ACCEPTER = 'accepter';
    
    case ANNULEE = 'annulee';
    public static function getValues(): array
    {
        return [
            self::EN_ATTENTE->value,
            self::ACCEPTER->value,
            
            self::ANNULEE->value,
        ];
    }
}