<?php
namespace App\Enums;
enum UserRoles: String
{
    case ADMIN = 'Admin';
    case CLIENT = 'Client';
    case SUDO = 'Sudo';
    case FRONTDESKAGENT = 'Front Desk Agent';
}
