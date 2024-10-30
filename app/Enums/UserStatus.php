<?php
namespace App\Enums;
enum UserStatus: string
{
    case ENABLE = 'Enable';
    case DISABLE = 'Disable';
    case EMAIL_CONFIRMATION_PENDING = 'Email Confirmation Pending';
    case ACCOUNT_CONFIRMATION_PENDING = 'Account Confirmation Pending';
    case DELETED = 'Deleted';
    public static function values():array
    {
        return [
            self::ACCOUNT_CONFIRMATION_PENDING->value,
            self::DISABLE->value,
            self::ENABLE->value,
            self::EMAIL_CONFIRMATION_PENDING->value,
            self::DELETED->value,
        ];
    }
}
