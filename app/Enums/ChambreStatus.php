<?php
namespace App\Enums;

use function Laravel\Prompts\select;

enum ChambreStatus : String
{
    case AVAILABLE = 'Disponible';
    case BOOKED = 'Réservé';
    case OCCUPED = 'Occupé';
    case MAINTENANCE = 'Maintenance';
    case DELETED = 'Deleted';

    public static function values():array
    {
        return [
            self::AVAILABLE->value,
            self::BOOKED->value,
            self::MAINTENANCE->value,
            self::OCCUPED->value,
            self::DELETED->value,
        ];
    }
}
