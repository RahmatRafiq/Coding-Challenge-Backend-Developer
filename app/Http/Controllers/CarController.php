<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\CarRepositoryInterface;

class CarController extends Controller
{
    protected $carRepository;

    public function __construct(CarRepositoryInterface $carRepository)
    {
        $this->carRepository = $carRepository;
    }

    public function index()
    {
        $cars = $this->carRepository->all();
        return response()->json($cars);
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
        return response()->json($car, 201);
    }

    public function show($id)
    {
        $car = $this->carRepository->find($id);
        return response()->json($car);
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
        $car = $this->carRepository->update($car, $data);
        return response()->json($car);
    }

    public function destroy($id)
    {
        $car = $this->carRepository->find($id);
        $this->carRepository->delete($car);
        return response()->json(['message' => 'Car deleted']);
    }
}
