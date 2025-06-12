<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'car_name',
        'day_rate',
        'month_rate',
        'image_car',
    ];
}
