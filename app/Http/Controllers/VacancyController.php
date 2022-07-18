<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\Vacancy;
use App\Repositories\VacancyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VacancyController extends Controller
{
    public function __construct(private readonly VacancyRepository $repository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $vacancies = $this->repository->getAll();

        return response()->json([
            'data' => $vacancies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreVacancyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'reservation_limit' => 'required|numeric',
            'price' => 'required',
        ]);

        $data = $request->only('date', 'reservation_limit', 'available_limit', 'price');
        $data['user_id'] = Auth::id();

        return response()->json([
            'data' => $this->repository->create($data),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        validator($request->route()->parameters(), [
            'vacancy' => 'required|uuid',
        ])->validate();

        $vacancy = $this->repository->getById($request->vacancy, Auth::id());
        return response()->json([
            'data' => $vacancy,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param \App\Models\Vacancy $vacancy
     * @return JsonResponse
     */
    public function update(Request $request, Vacancy $vacancy): JsonResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Vacancy $vacancy
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vacancy $vacancy): JsonResponse
    {
        //
    }
}
