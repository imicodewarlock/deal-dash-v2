<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Store extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category_id',
        'image',
        'address',
        'about',
        'phone',
        'latitude',
        'longitude',
        'place_id',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'store_id');
    }

    public function favoriteByUsers()
    {
        return $this->belongsToMany(User::class, 'store_favorites')->withTimestamps();
    }

    public function favoritesCount()
    {
        return $this->favoriteByUsers()->count();
    }

    public function scopeNearbyStores(Builder $query, $latitude = 0, $longitude = 0, $distanceInMeters = 10000)
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
}
