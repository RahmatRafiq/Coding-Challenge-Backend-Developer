<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function index(Request $request)
    {
        return Inertia::render('Orders/Index');
    }

    public function json(Request $request)
    {
        $search  = $request->search['value'] ?? '';
        $query   = Order::with('car');
        $columns = [
            'id',
            'order_date',
            'pickup_date',
            'dropoff_date',
            'pickup_location',
            'dropoff_location',
            'created_at',
            'updated_at',
        ];

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('car', function ($q) use ($search) {
                        $q->where('car_name', 'like', "%{$search}%"); // perbaiki field
                    });
            });
        }

        if ($request->filled('order')) {
            $query->orderBy($columns[$request->order[0]['column']], $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($order) {
            return [
                'id'               => $order->id,
                'order_date'       => $order->order_date->toDateString(),
                'pickup_date'      => $order->pickup_date->toDateString(),
                'dropoff_date'     => $order->dropoff_date->toDateString(),
                'pickup_location'  => $order->pickup_location,
                'dropoff_location' => $order->dropoff_location,
                'created_at'       => $order->created_at->toDateTimeString(),
                'car'              => $order->car ? [
                    'id'       => $order->car->id,
                    'car_name' => $order->car->car_name,
                ] : null,
                'actions'          => '',
            ];
        })->values();

        return response()->json($data);
    }

    public function create()
    {
        // Eager load cars for the form
        $cars = \App\Models\Car::all(['id', 'car_name']);
        return Inertia::render('Orders/Form', [
            'cars' => $cars,
        ]);
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
        return redirect()->route('orders.index')->with('success', 'Order berhasil dibuat.');
    }

    public function edit($id)
    {
        $order = $this->orderRepository->find($id);
        $cars = \App\Models\Car::all(['id', 'car_name']);
        return Inertia::render('Orders/Form', [
            'order' => $order,
            'cars' => $cars,
        ]);
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
        $this->orderRepository->update($order, $data);
        return redirect()->route('orders.index')->with('success', 'Order berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $order = $this->orderRepository->find($id);
        $this->orderRepository->delete($order);
        return redirect()->route('orders.index')->with('success', 'Order berhasil dihapus.');
    }
}
