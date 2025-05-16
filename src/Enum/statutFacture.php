<?php

namespace App\Entity;
enum statutFacture: string
{
    case EN_ATTENTE = 'en_attente';
    case PAYEE = 'Payée';
    
    case RETARD = 'en_retard';
    public static function getValues(): array
    {
        return [
            self::EN_ATTENTE->value,
            self::PAYEE->value,
            
            self::RETARD->value,
        ];
    }
}                                                                                                                               