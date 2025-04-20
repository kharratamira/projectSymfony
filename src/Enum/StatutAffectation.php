<?php

namespace App\Entity;
enum StatutAffectation: string
{
    case EN_ATTENTE = 'en_attente';
  
    case EN_COURS = 'en_cours';
    case TERMINEE = 'terminee';
   
    public static function getValues(): array
    {
        return [
            self::EN_ATTENTE->value,
          
            
            self::EN_COURS->value,
            self::TERMINEE->value,
        ];
    }
}