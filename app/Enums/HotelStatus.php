<?php
namespace App\Enums;
enum HotelStatus:string
{
    case ENABLE = 'Enable';
    case DISABLE = 'Disable';
    case PENDING = 'Configuration Pending';

    public static function values():array
    {
        return [
            self::ENABLE->value,
            self::DISABLE->value,
            self::PENDING->value,
        ];
    }
}
