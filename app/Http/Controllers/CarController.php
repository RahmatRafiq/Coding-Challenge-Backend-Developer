<?php
namespace App\Http\Controllers;

use App\Helpers\DataTable;
use App\Helpers\MediaLibrary;
use App\Models\Car;
use App\Repositories\Contracts\CarRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;

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
        return Inertia::render('Cars/Form', [
            'carImage' => null,
        ]);
    }

   public function store(Request $request)
    {
        $data = $request->validate([
            'car_name'   => 'required|string',
            'day_rate'   => 'required|numeric',
            'month_rate' => 'required|numeric',
            'image_car'  => 'nullable|string',
            'car-images' => 'array|max:1',
            'car-images.*' => 'string',
        ]);
        $car = $this->carRepository->create($data);

        // Simpan media
        MediaLibrary::put($car, 'car-images', $request, 'car-images');

        return redirect()->route('cars.index')->with('success', 'Car berhasil dibuat.');
    }

       public function edit($id)
    {
        $car = $this->carRepository->find($id);
        $media = $car->getMedia('car-images')->first();
        $carImage = $media
            ? [
                'file_name' => $media->file_name,
                'size'      => $media->size,
                'url'       => $media->getFullUrl(),
            ]
            : null;

        return Inertia::render('Cars/Form', [
            'car' => $car,
            'carImage' => $carImage,
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
            'car-images' => 'array|max:1',
            'car-images.*' => 'string',
        ]);
        $this->carRepository->update($car, $data);

        // Update media
        MediaLibrary::put($car, 'car-images', $request, 'car-images');

        return redirect()->route('cars.index')->with('success', 'Car berhasil diperbarui.');
    }
        public function upload(Request $request)
    {
        $request->validate([
            'car-images.*' => 'required|file|image|max:2048',
        ]);

        $file     = $request->file('car-images')[0];
        $tempPath = $file->store('', 'temp');

        return response()->json([
            'name' => basename($tempPath),
            'url'  => Storage::disk('temp')->url($tempPath),
        ]);
    }
    public function deleteFile(Request $request)
    {
        $data = $request->validate(['filename' => 'required|string']);

        if (Storage::disk('car-images')->exists($data['filename'])) {
            Storage::disk('car-images')->delete($data['filename']);
        }

        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('file_name', $data['filename'])->first();
        if ($media) {
            $media->delete();
        }

        return response()->json(['message' => 'File berhasil dihapus'], 200);
    }
    public function destroy($id)
    {
        $car = $this->carRepository->find($id);
        $this->carRepository->delete($car);
        return redirect()->route('cars.index')->with('success', 'Car berhasil dihapus.');
    }
}
