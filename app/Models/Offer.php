<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Offer extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'store_id',
        'image',
        'about',
        'price',
        'address',
        'latitude',
        'longitude',
        'start_date',
        'end_date',
    ];

    protected $dates = ['updated_at', 'deleted_at'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'double',
            'longitude' => 'double',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeNearbyOffers(Builder $query, $latitude = 0, $longitude = 0, $distanceInMeters = 10000)
    {
        $haversine = "(6371000 * acos(cos(radians($latitude))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians($longitude))
                        + sin(radians($latitude))
                        * sin(radians(latitude))))";

        return $query->selectRaw("*, $haversine AS distance")
                    ->having('distance', '<=', $distanceInMeters)
                    ->orderBy('distance');
    }

    public function getDistanceAttribute($distance)
    {
        // Format distance as meters, kilometers, or miles
        if ($distance < 1000) {
            return round($distance) . __('misc.meter'); // Meters
        } elseif ($distance < 1609) {
            return round($distance / 1000, 1) . __('misc.kilometer'); // Kilometers
        } else {
            // Convert distance to miles (1 mile = 1609 meters)
            return round($distance / 1609, 1) . __('misc.mile'); // Miles
        }
    }

    public function setPriceAttribute($value)
    {
        // Format the price (optional)
        $this->attributes['price'] = number_format((float) $value, 2);
    }
}
