<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Car extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'car_name',
        'day_rate',
        'month_rate',
        'image_car',
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeAvailableFor($query, $pickup, $dropoff)
    {
        return $query->whereDoesntHave('orders', function ($q) use ($pickup, $dropoff) {
            $q->whereNull('deleted_at')
                ->where('pickup_date', '<=', $dropoff)
                ->where('dropoff_date', '>=', $pickup);
        });
    }
}
