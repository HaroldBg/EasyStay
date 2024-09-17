<?php
namespace App\Enums;
enum HotelStatus:string
{
    case ENABLE = 'Enable';
    case DISABLE = 'Disable';
    case PENDING = 'Confirmation Pending';
}
