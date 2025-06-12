<?php

namespace App\Repositories\Contracts;

use App\Models\Car;

interface CarRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function update(Car $car, array $data);

    public function delete(Car $car);
}