import { useRef } from 'react';
import ReactDOM from 'react-dom/client';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import DataTableWrapper, { DataTableWrapperRef } from '@/components/datatables';
import { BreadcrumbItem } from '@/types';
import { Car } from '@/types/Cars';

const columns = [
    { data: 'id', title: 'ID' },
    { data: 'car_name', title: 'Car Name' },
    { data: 'day_rate', title: 'Day Rate' },
    { data: 'month_rate', title: 'Month Rate' },
    { data: 'image_car', title: 'Image' },
    { data: 'created_at', title: 'Created At' },
    {
        data: null,
        title: 'Actions',
        orderable: false,
        searchable: false,
        render: (_data: null, _type: string, row: unknown,) => {
            const car = row as Car;
            return `
                <span class="inertia-link-cell" data-id="${car.id}"></span>
                <button class="btn-delete ml-2 px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700" data-id="${car.id}">Delete</button>
            `;
        },
    },
];

export default function CarsIndex({ success }: { success?: string }) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Car Management', href: '/cars' }];
    const dtRef = useRef<DataTableWrapperRef>(null);

    const handleDelete = (id: number) => {
        router.delete(route('cars.destroy', id), {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    const drawCallback = () => {
        document.querySelectorAll('.inertia-link-cell').forEach((cell) => {
            const id = cell.getAttribute('data-id');
            if (id) {
                const root = ReactDOM.createRoot(cell);
                root.render(
                    <Link
                        href={`/cars/${id}/edit`}
                        className="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                    >
                        Edit
                    </Link>
                );
            }
        });

        document.querySelectorAll('.btn-delete').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (id) handleDelete(Number(id));
            });
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Cars" />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Car Management</h1>
                <div className="col-md-12">
                    <HeadingSmall title="Cars" description="Manage application cars" />
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-xl font-semibold">Car List</h2>
                        <Link href={route('cars.create')}>
                            <Button>Create Car</Button>
                        </Link>
                    </div>
                    {success && (
                        <div className="p-2 mb-2 bg-green-100 text-green-800 rounded">{success}</div>
                    )}
                    <DataTableWrapper
                        ref={dtRef}
                        ajax={{
                            url: route('cars.json'),
                            type: 'POST',
                        }}
                        columns={columns}
                        options={{ drawCallback }}
                    />
                </div>
            </div>
        </AppLayout>
    );
}