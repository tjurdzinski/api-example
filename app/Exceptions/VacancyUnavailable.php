<?php

namespace App\Exceptions;

class VacancyUnavailable extends ReservationException
{
    protected $message = 'Selected vacancies are unavailable';
}
