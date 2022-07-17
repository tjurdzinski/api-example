<?php

namespace App\Services;

use App\Exceptions\ReservationInvalidDates;
use App\Exceptions\ReservationSave;
use App\Exceptions\VacancyUnavailable;
use App\Models\Reservation;
use App\Models\Vacancy;
use App\Repositories\ReservationRepository;
use App\Repositories\VacancyRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function __construct(
        private readonly ReservationRepository $repository,
        private readonly VacancyRepository     $vacancyRepository,
    )
    {
    }

    public function create(array $vacanciesId, string $clientId): Reservation
    {
        DB::beginTransaction();
        $vacancies = $this->vacancyRepository->getForReservation($vacanciesId, $clientId);

        if (count($vacanciesId) !== count($vacancies)) {
            throw new VacancyUnavailable();
        }

        if (!$this->ensureDatesAreInSequence($vacancies)) {
            throw new ReservationInvalidDates();
        }

        $price = 0;
        $vacancies->map(function (Vacancy $vacancy) use (&$price) {
            $price = $price + $vacancy->price;
        });

        $params = [
            'from' => $vacancies->first()->date->format('Y-m-d'),
            'to' => $vacancies->last()->date->add('1 day')->format('Y-m-d'),
            'price' => $price,
            'user_id' => $clientId,
        ];

        $reservation = $this->repository->create($params);
        $reservation->vacancies()->sync($vacanciesId);

        $r = $this->vacancyRepository->reduceAvailableLimit($vacanciesId);

        if ($r !== count($vacanciesId)) {
            throw new ReservationSave();
        }

        DB::commit();
        return $reservation;
    }

    /**
     * @param Collection<Vacancy> $vacancies
     * @return bool
     */
    private function ensureDatesAreInSequence(Collection $vacancies): bool
    {
        for ($i = 1; $i < $vacancies->count(); $i++) {
            $current = new \DateTime($vacancies[$i]->date);
            $diff = $current->diff($vacancies[$i - 1]->date);

            if ($diff->days == 1) {
                continue;
            }

            return false;
        }

        return true;
    }
}
