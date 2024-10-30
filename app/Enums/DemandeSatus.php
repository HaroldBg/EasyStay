<?php
namespace App\Enums;

enum DemandeSatus : string
{
    case VALIDATE = 'Validate';
    case REJECTED = 'Rejected';
    case PENDING = 'Pending';
    case DELETED = 'Deleted';
    public static function values():array
    {
        return [
            self::VALIDATE->value,
            self::REJECTED->value,
            self::PENDING->value,
            self::DELETED->value,
        ];
    }
}
