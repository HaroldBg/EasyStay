<?php
namespace App\Enums;

enum TypeChambreStatus :String
{
    case AVAILABLE = 'Disponible';
    case DELETED = 'Deleted';

    public static function values():array
    {
        return [
            self::AVAILABLE->value,
            self::DELETED->value,
        ];
    }
}
