<?php
namespace App\Repositories;

use App\Models\Car;
use App\Repositories\Contracts\CarRepositoryInterface;

class CarRepository implements CarRepositoryInterface
{
    public function all()
    {
        return Car::all();
    }

    public function find($id)
    {
        return Car::findOrFail($id);
    }

    public function create(array $data)
    {
        return Car::create($data);
    }

    public function update(Car $car, array $data)
    {
        $car->update($data);
        return $car;
    }

    public function delete(Car $car)
    {
        return $car->delete();
    }
}
