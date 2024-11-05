<?php
namespace App\Enums;

enum ReservationStatus:String
{

    case CHECKIN = 'Check-in';
    case CHECKOUT = 'Check-out';
    case WAITING = 'En attente';
    case CANCELED = 'Annulée';
    case CONFIRMED = 'Confirmé';
    case DELETED = 'Deleted';

    public static function values():array
    {
        return [
            self::DELETED->value,
            self::CHECKIN->value,
            self::CHECKOUT->value,
            self::WAITING->value,
            self::CANCELED->value,
            self::CONFIRMED->value,
        ];
    }
}
