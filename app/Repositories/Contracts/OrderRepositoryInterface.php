<?php

namespace App\Repositories\Contracts;

use App\Models\Order;

interface OrderRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function update(Order $order, array $data);

    public function delete(Order $order);
}