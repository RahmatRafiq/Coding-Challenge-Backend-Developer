<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Car;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $carIds = Car::pluck('id')->toArray();

        for ($i = 0; $i < 50; $i++) {
            $pickupDate = $faker->dateTimeBetween('-1 month', '+1 month');
            $dropoffDate = (clone $pickupDate)->modify('+' . $faker->numberBetween(1, 14) . ' days');

            Order::create([
                'car_id'           => $faker->randomElement($carIds),
                'order_date'       => $faker->dateTimeBetween('-2 months', $pickupDate)->format('Y-m-d'),
                'pickup_date'      => $pickupDate->format('Y-m-d'),
                'dropoff_date'     => $dropoffDate->format('Y-m-d'),
                'pickup_location'  => $faker->city,
                'dropoff_location' => $faker->city,
            ]);
        }
    }
}