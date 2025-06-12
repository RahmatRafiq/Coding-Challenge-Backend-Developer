<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Models\Car;
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
                        $q->where('car_name', 'like', "%{$search}%");
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

    public function create(Request $request)
    {
        $pickup  = $request->input('pickup_date');
        $dropoff = $request->input('dropoff_date');

        $cars         = [];
        $noCarMessage = null;
        if ($pickup && $dropoff) {
            $cars = Car::availableFor($pickup, $dropoff)->get(['id', 'car_name']);
            if ($cars->isEmpty()) {
                $noCarMessage = 'Semua mobil telah diorder pada tanggal yang dipilih.';
            }
        } else {
            $cars = Car::all(['id', 'car_name']);
        }

        return Inertia::render('Orders/Form', [
            'cars'         => $cars,
            'noCarMessage' => $noCarMessage,
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

        $overlap = Order::where('car_id', $data['car_id'])
            ->whereNull('deleted_at')
            ->where(function ($q) use ($data) {
                $q->where('pickup_date', '<=', $data['dropoff_date'])
                    ->where('dropoff_date', '>=', $data['pickup_date']);
            })
            ->exists();

        if ($overlap) {
            return back()
                ->withErrors(['car_id' => 'Mobil ini sedang diorder pada tanggal tersebut.'])
                ->withInput();
        }

        $order = $this->orderRepository->create($data);
        return redirect()->route('orders.index')->with('success', 'Order berhasil dibuat.');
    }

    public function edit($id)
    {
        $order = $this->orderRepository->find($id);
        $cars = Car::all(['id', 'car_name']);

        return Inertia::render('Orders/Form', [
            'order' => $order,
            'cars'  => $cars,
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

        $overlap = Order::where('car_id', $data['car_id'])
            ->where('id', '!=', $id)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($data) {
                $q->where('pickup_date', '<=', $data['dropoff_date'])
                    ->where('dropoff_date', '>=', $data['pickup_date']);
            })
            ->exists();

        if ($overlap) {
            return back()
                ->withErrors(['car_id' => 'Mobil ini sedang diorder pada tanggal tersebut.'])
                ->withInput();
        }

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
