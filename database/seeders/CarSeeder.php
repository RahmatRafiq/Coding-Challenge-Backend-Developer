<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Car;
use Faker\Factory as Faker;


class CarSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            Car::create([
                'car_name'   => $faker->company . ' ' . $faker->word,
                'day_rate'   => $faker->numberBetween(200000, 1000000),
                'month_rate' => $faker->numberBetween(3000000, 20000000),
                'image_car'  => $faker->imageUrl(640, 480, 'cars', true, 'Car'),
            ]);
        }
    }
}