<?php
namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function all()
    {
        return Order::with('car')->get();
    }

    public function find($id)
    {
        return Order::with('car')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data)
    {
        $order->update($data);
        return $order;
    }

    public function delete(Order $order)
    {
        return $order->delete();
    }
}
