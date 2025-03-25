<?php

namespace App\Entity;
enum StatutDemande: string
{
    case EN_ATTENTE = 'en_attente';
    case Accepter = 'accepter';
    
    case ANNULEE = 'annulee';
    public static function getValues(): array
    {
        return [
            self::EN_ATTENTE->value,
            self::Accepter->value,
            
            self::ANNULEE->value,
        ];
    }
}