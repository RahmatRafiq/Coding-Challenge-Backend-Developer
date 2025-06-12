<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'car_id',
        'order_date',
        'pickup_date',
        'dropoff_date',
        'pickup_location',
        'dropoff_location',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'pickup_date' => 'datetime',
        'dropoff_date' => 'datetime',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
