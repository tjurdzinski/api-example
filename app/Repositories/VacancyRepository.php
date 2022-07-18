<?php

namespace App\Repositories;

use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VacancyRepository
{
    public function getAll(): Collection
    {
        return Vacancy::query()
            ->orderBy('date')
            ->get();
    }

    public function getById(string $id, string $userId): Vacancy|null
    {
        return Vacancy::query()
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function create(array $data): Vacancy
    {
        $data['available_limit'] = $data['reservation_limit'];
        $vacancy = (new Vacancy())->fill($data);
        $vacancy->save();

        return $vacancy;
    }

    /**
     * @param array $ids
     * @param string $clientId
     * @return Collection<Vacancy>
     */
    public function getForReservation(array $ids, string $clientId): Collection
    {
        return Vacancy::query()
            ->where('user_id', '!=', $clientId)
            ->where('available_limit', '>', '0')
            ->whereIn('id', $ids)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * @param array $ids
     * @return bool|int
     */
    public function reduceAvailableLimit(array $ids): bool|int
    {
        return Vacancy::query()
            ->whereIn('id', $ids)
            ->update([
                'available_limit' => DB::raw('available_limit - 1'),
            ]);
    }
}
