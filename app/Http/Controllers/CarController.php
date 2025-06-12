<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Models\Car;
use App\Repositories\Contracts\CarRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarController extends Controller
{
    protected $carRepository;

    public function __construct(CarRepositoryInterface $carRepository)
    {
        $this->carRepository = $carRepository;
    }

    public function index(Request $request)
    {
        return Inertia::render('Cars/Index');
    }

    public function json(Request $request)
    {
        $search  = $request->search['value'] ?? '';
        $query   = Car::query();
        $columns = [
            'id',
            'car_name',
            'day_rate',
            'month_rate',
            'image_car',
            'created_at',
            'updated_at',
        ];

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('car_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('order')) {
            $query->orderBy($columns[$request->order[0]['column']], $request->order[0]['dir']);
        }

        $data = DataTable::paginate($query, $request);

        $data['data'] = collect($data['data'])->map(function ($car) {
            return [
                'id'         => $car->id,
                'car_name'   => $car->car_name,
                'day_rate'   => $car->day_rate,
                'month_rate' => $car->month_rate,
                'image_car'  => $car->image_car,
                'created_at' => $car->created_at->toDateTimeString(),
                'actions'    => '',
            ];
        })->values();

        return response()->json($data);
    }

    public function create()
    {
        return Inertia::render('Cars/Form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'car_name'   => 'required|string',
            'day_rate'   => 'required|numeric',
            'month_rate' => 'required|numeric',
            'image_car'  => 'nullable|string',
        ]);
        $car = $this->carRepository->create($data);
        return redirect()->route('cars.index')->with('success', 'Car berhasil dibuat.');
    }

    public function edit($id)
    {
        $car = $this->carRepository->find($id);
        return Inertia::render('Cars/Form', [
            'car' => $car,
        ]);
    }

    public function update(Request $request, $id)
    {
        $car  = $this->carRepository->find($id);
        $data = $request->validate([
            'car_name'   => 'sometimes|required|string',
            'day_rate'   => 'sometimes|required|numeric',
            'month_rate' => 'sometimes|required|numeric',
            'image_car'  => 'nullable|string',
        ]);
        $this->carRepository->update($car, $data);
        return redirect()->route('cars.index')->with('success', 'Car berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $car = $this->carRepository->find($id);
        $this->carRepository->delete($car);
        return redirect()->route('cars.index')->with('success', 'Car berhasil dihapus.');
    }
}
