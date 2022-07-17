<?php

namespace App\Exceptions;

class ReservationInvalidDates extends ReservationException
{
    protected $message = 'Invalid date sequence provided';
}
