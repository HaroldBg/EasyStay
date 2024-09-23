<?php
namespace App\Enums;
enum UserRoles: String
{
    case ADMIN = 'Admin';
    case CLIENT = 'Client';
    case SUDO = 'Sudo';
    case FRONTDESKAGENT = 'Front Desk Agent';

    public static function values():array
    {
        return [
            self::ADMIN->value,
            self::CLIENT->value,
            self::SUDO->value,
            self::FRONTDESKAGENT->value,
        ];
    }
}
