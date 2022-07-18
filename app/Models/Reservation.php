<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Reservation
 *
 * @property string $id
 * @property string $user_id
 * @property \Illuminate\Support\Carbon $from
 * @property \Illuminate\Support\Carbon $to
 * @property int $price
 * @property bool $confirmed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Vacancy[] $vacancies
 * @property-read int|null $vacancies_count
 * @method static \Database\Factories\ReservationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereUserId($value)
 * @mixin \Eloquent
 */
class Reservation extends Model
{
    use HasFactory, Uuid;

    public $incrementing = false;

    protected $keyType = 'uuid';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'from',
        'to',
        'price',
        'confirmed',
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'confirmed' => false,
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'from' => 'date',
        'to' => 'date',
        'price' => 'integer',
        'confirmed' => 'boolean',
    ];

    public function toArray()
    {
        $result = parent::toArray();
        $result['vacancies'] = $this->vacancies;
        return $result;
    }

    public function vacancies(): BelongsToMany
    {
        return $this->belongsToMany(Vacancy::class, 'reservation_vacancy');
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100,
        );
    }
}
