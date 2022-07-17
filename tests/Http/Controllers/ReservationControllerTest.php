<?php

namespace Tests\Http\Controllers;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function testStoreWithoutAvailableLimit()
    {
        $vacancies = [];
        $startDate = new Carbon($this->faker->dateTimeBetween('+1 week', '+1 month')->setTime(0, 0, 0));
        for ($i = 0; $i < 7; $i++) {
            $date = (clone $startDate)->modify(sprintf('+%d day', $i));
            $vacancies[] = Vacancy::factory()->create([
                'date' => $date,
                'reservation_limit' => 3,
                'available_limit' => 0,
                'user_id' => $this->getDefaultUser()->id,
                'price' => 200
            ])->id;
        }

        $user = User::factory()->create();
        $params = [
            'vacancies' => $vacancies,
        ];

        $response = $this->postJson('/api/reservations', $params, $this->getAuthHeader($user));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $this->assertSame('Selected vacancies are unavailable', $response->json('error'));
    }

    public function testStoreNotExistingVacancy()
    {
        $vacancies = [];
        $startDate = new Carbon($this->faker->dateTimeBetween('+1 week', '+1 month')->setTime(0, 0, 0));
        for ($i = 0; $i < 7; $i++) {
            $date = (clone $startDate)->modify(sprintf('+%d day', $i));
            $vacancies[] = Vacancy::factory()->create([
                'date' => $date,
                'reservation_limit' => 3,
                'available_limit' => 3,
                'user_id' => $this->getDefaultUser()->id,
                'price' => 200
            ])->id;
        }

        $vacancies[] = $this->faker->uuid;

        $user = User::factory()->create();
        $params = [
            'vacancies' => $vacancies,
        ];

        $response = $this->postJson('/api/reservations', $params, $this->getAuthHeader($user));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $this->assertSame('Selected vacancies are unavailable', $response->json('error'));
    }

    public function testStoreVacanciesNotInSequence()
    {
        $vacancies = [];
        $startDate = new Carbon($this->faker->dateTimeBetween('+1 week', '+1 month')->setTime(0, 0, 0));
        for ($i = 0; $i < 7; $i++) {
            $date = (clone $startDate)->modify(sprintf('+%d day', $this->faker->numberBetween(2,5)));
            $vacancies[] = Vacancy::factory()->create([
                'date' => $date,
                'reservation_limit' => 3,
                'available_limit' => 3,
                'user_id' => $this->getDefaultUser()->id,
                'price' => 200
            ])->id;
        }

        $user = User::factory()->create();
        $params = [
            'vacancies' => $vacancies,
        ];

        $response = $this->postJson('/api/reservations', $params, $this->getAuthHeader($user));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $this->assertSame('Invalid date sequence provided', $response->json('error'));
    }

    public function testStore()
    {
        $vacancies = [];
        $startDate = new Carbon($this->faker->dateTimeBetween('+1 week', '+1 month')->setTime(0, 0, 0));
        for ($i = 0; $i < 7; $i++) {
            $date = (clone $startDate)->modify(sprintf('+%d day', $i));
            $vacancies[] = Vacancy::factory()->create([
                'date' => $date,
                'reservation_limit' => 3,
                'available_limit' => 3,
                'user_id' => $this->getDefaultUser()->id,
                'price' => 200
            ])->id;
        }

        $user = User::factory()->create();
        $params = [
            'vacancies' => $vacancies,
        ];

        $response = $this->postJson('/api/reservations', $params, $this->getAuthHeader($user));
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $response->json('data');
        $this->assertFalse($data['confirmed']);
        $this->assertSame($startDate->toJSON(), $data['from']);
        $this->assertSame($date->addDay()->toJSON(), $data['to']);
        $this->assertSame(1400, $data['price']);
        $this->assertSame($user->id, $data['user_id']);

        $vacanciesAfterReservation = Vacancy::query()
            ->whereIn('id', $vacancies)
            ->where('available_limit', 2)
            ->get();

        $this->assertSame(count($vacancies), count($vacanciesAfterReservation));

        $vacanciesAfterReservation->map(function (Vacancy $vacancy) use ($data) {
            $this->assertSame($data['id'], $vacancy->reservations()->first()->id);
        });
    }

    public function testGetUnauthorized() {
        $response = $this->get('/api/reservations');
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testGetZeroMyReservations() {
        for ($i = 0; $i < 7; $i++) {
            $startDate = new Carbon($this->faker->dateTimeBetween('+1 week', '+1 month')->setTime(0, 0, 0));
            $date = (clone $startDate)->modify(sprintf('+%d day', $i));
            $vacancies[] = Vacancy::factory()->create([
                'date' => $date,
                'reservation_limit' => 3,
                'available_limit' => 2,
                'user_id' => $this->getDefaultUser()->id,
                'price' => 200
            ])->id;
        }

        $user = User::factory()->create();
        $endDate = $date->addDay();
        $reservation = Reservation::factory()->create([
            'user_id' => $user,
            'price' => 1400,
            'from' => $startDate,
            'to' =>  $endDate,
        ]);
        $reservation->vacancies()->sync($vacancies);

        $response = $this->get('/api/reservations', $this->getAuthHeader(User::factory()->create()));
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $response->json('data');
        $this->assertCount(0, $data);
    }

    public function testGet() {
        for ($i = 0; $i < 7; $i++) {
            $startDate = new Carbon($this->faker->dateTimeBetween('+1 week', '+1 month')->setTime(0, 0, 0));
            $date = (clone $startDate)->modify(sprintf('+%d day', $i));
            $vacancies[] = Vacancy::factory()->create([
                'date' => $date,
                'reservation_limit' => 3,
                'available_limit' => 2,
                'user_id' => $this->getDefaultUser()->id,
                'price' => 200
            ])->id;
        }

        $user = User::factory()->create();
        $endDate = $date->addDay();
        $reservation = Reservation::factory()->create([
            'user_id' => $user,
            'price' => 1400,
            'from' => $startDate,
            'to' =>  $endDate,
        ]);
        $reservation->vacancies()->sync($vacancies);

        $response = $this->get('/api/reservations', $this->getAuthHeader($user));
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame($user->id, $data[0]['user_id']);
        $this->assertSame(1400, $data[0]['price']);
        $this->assertFalse($data[0]['confirmed']);
        $this->assertSame($startDate->toJSON(), $data[0]['from']);
        $this->assertSame($endDate->toJSON(), $data[0]['to']);
    }
}
