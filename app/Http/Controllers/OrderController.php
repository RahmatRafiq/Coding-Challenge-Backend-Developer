<?php
namespace App\Http\Controllers;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function index()
    {
        $orders = $this->orderRepository->all();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'car_id'           => 'required|exists:cars,id',
            'order_date'       => 'required|date',
            'pickup_date'      => 'required|date',
            'dropoff_date'     => 'required|date|after_or_equal:pickup_date',
            'pickup_location'  => 'required|string|max:50',
            'dropoff_location' => 'required|string|max:50',
        ]);
        $order = $this->orderRepository->create($data);
        return response()->json($order, 201);
    }

    public function show($id)
    {
        $order = $this->orderRepository->find($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = $this->orderRepository->find($id);
        $data  = $request->validate([
            'car_id'           => 'sometimes|required|exists:cars,id',
            'order_date'       => 'sometimes|required|date',
            'pickup_date'      => 'sometimes|required|date',
            'dropoff_date'     => 'sometimes|required|date|after_or_equal:pickup_date',
            'pickup_location'  => 'sometimes|required|string|max:50',
            'dropoff_location' => 'sometimes|required|string|max:50',
        ]);
        $order = $this->orderRepository->update($order, $data);
        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = $this->orderRepository->find($id);
        $this->orderRepository->delete($order);
        return response()->json(['message' => 'Order deleted']);
    }
}
