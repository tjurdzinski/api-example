<?php

namespace App\Repositories;

use App\Models\Reservation;
use Illuminate\Support\Collection;

class ReservationRepository
{
    public function create(array $data): Reservation
    {
        $r = (new Reservation())->fill($data);
        $r->save();

        return $r;
    }

    public function getForUser(string $userId): Collection
    {
        return Reservation::query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
