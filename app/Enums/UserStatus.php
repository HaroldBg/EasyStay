<?php
namespace App\Enums;
enum UserStatus: string
{
    case ENABLE = 'Enable';
    case DISABLE = 'Disable';
    case EMAIL_CONFIRMATION_PENDING = 'Email Confirmation Pending';
    case ACCOUNT_CONFIRMATION_PENDING = 'Account Confirmation Pending';
}
