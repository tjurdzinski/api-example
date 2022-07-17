<?php

namespace Tests\Http\Controllers;

use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class VacancyControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function testGetAllUnauthorized()
    {
        $response = $this->get('api/vacancies');
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testGetAllEmptyResponse()
    {
        $response = $this->get('api/vacancies', $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['data' => []], $response->json());
    }

    public function testGetAll()
    {
        Vacancy::factory(15)->create([
            'user_id' => $this->getDefaultUser()->id,
        ]);

        $response = $this->get('api/vacancies', $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(15, $response->json('data'));
    }

    public function testGetOne()
    {
        /** @var Vacancy $vacancy */
        $vacancy = Vacancy::factory()->create([
            'user_id' => $this->getDefaultUser()->id,
        ]);

        $response = $this->get(sprintf('api/vacancies/%s', $vacancy->id), $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = $response->json('data');
        $this->assertSame($vacancy->price, $data['price']);
        $this->assertSame($vacancy->user->id, $data['user_id']);
    }

    public function testGetOneNotMyVacancy()
    {
        /** @var Vacancy $vacancy */
        $vacancy = Vacancy::factory()->create();

        $response = $this->get(sprintf('api/vacancies/%s', $vacancy->id), $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetOneNotFound()
    {
        $response = $this->get(sprintf('api/vacancies/%s', $this->faker->uuid), $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetOneInvalidTypeId()
    {
        $response = $this->get(sprintf('api/vacancies/%s', 123), $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testCreate()
    {
        $date = Carbon::today();
        $params = [
            'date' => Carbon::today()->toDateString(),
            'reservation_limit' => 5,
            'price' => 215.10,
        ];

        $this->assertSame(0, Vacancy::query()->count());

        $response = $this->postJson('api/vacancies', $params, $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $response->assertJson(['data' => [
            'date' => $date->toJSON(),
            'reservation_limit' => 5,
            'price' => 215.10,
            'user_id' => $this->getDefaultUser()->id,
            'available_limit' => 5,
        ]]);

        $this->assertSame(1, Vacancy::query()->count());
    }

    public function testCreateValidation()
    {
        $params = [];
        $response = $this->postJson('api/vacancies', $params, $this->getAuthHeader($this->getDefaultUser()));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $r = $response->json('errors');
        $this->assertCount(3, $r);
        $this->assertNotEmpty($r['date']);
        $this->assertNotEmpty($r['reservation_limit']);
        $this->assertNotEmpty($r['price']);
    }

    public function testCreateUnauthorized()
    {
        $response = $this->postJson('api/vacancies');
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}
