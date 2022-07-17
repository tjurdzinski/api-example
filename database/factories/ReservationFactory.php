<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startDate = new Carbon($this->faker->dateTimeThisMonth);
        return [
            'user_id' => User::factory()->create()->id,
            'from' => $startDate,
            'to' => clone($startDate)->addDays(6),
            'price' => 200,
            'confirmed' => false,
        ];
    }
}
