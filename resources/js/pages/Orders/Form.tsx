import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent, useEffect } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import CustomSelect from '@/components/select';

import type { Order } from '@/types/Orders';
import type { Car } from '@/types/Cars';
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Separator } from '@/components/ui/separator';

export default function OrderForm({ order, cars, noCarMessage }: { order?: Order; cars: Car[]; noCarMessage?: string }) {
    const isEdit = !!order;
    const { data, setData, post, put, processing, errors } = useForm({
        car_id: order ? order.car_id : '',
        order_date: order ? order.order_date : '',
        pickup_date: order ? order.pickup_date : '',
        dropoff_date: order ? order.dropoff_date : '',
        pickup_location: order ? order.pickup_location : '',
        dropoff_location: order ? order.dropoff_location : '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Orders', href: '/orders' },
        { title: isEdit ? 'Edit Order' : 'Create Order', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('orders.update', order!.id));
        } else {
            post(route('orders.store'));
        }
    };

    const carOptions = (cars ?? []).map(car => ({
        value: car.id,
        label: car.car_name,
    }));

    useEffect(() => {
        if (
            carOptions.length === 0 ||
            !carOptions.some(option => option.value === data.car_id)
        ) {
            setData('car_id', '');
        }
    }, [data.pickup_date, data.dropoff_date, cars, carOptions, data.car_id, setData]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Order' : 'Create Order'} />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Order Management</h1>
                <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                    <Separator className="my-6 md:hidden" />
                    <div className="flex-1 md:max-w-2xl space-y-6">
                        <HeadingSmall
                            title={isEdit ? 'Edit Order' : 'Create Order'}
                            description="Fill in the details below"
                        />
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="car_id">Car</Label>
                                {!data.pickup_date || !data.dropoff_date ? (
                                    <div className="text-gray-500 text-sm mb-2">
                                        Silakan pilih tanggal pickup dan dropoff terlebih dahulu.
                                    </div>
                                ) : carOptions.length === 0 ? (
                                    <div className="text-red-600 text-sm mb-2">
                                        {noCarMessage || 'Semua mobil telah diorder pada tanggal yang dipilih.'}
                                    </div>
                                ) : (
                                    <CustomSelect
                                        id="car_id"
                                        options={carOptions}
                                        value={carOptions.find(option => option.value === data.car_id)}
                                        onChange={(selected) => setData('car_id', (selected as { value: number }).value)}
                                        required
                                    />
                                )}
                                <InputError message={errors.car_id} />
                            </div>

                            <div>
                                <Label htmlFor="order_date">Order Date</Label>
                                <Input
                                    id="order_date"
                                    type="date"
                                    value={data.order_date}
                                    onChange={(e) => setData('order_date', e.target.value)}
                                    required
                                />
                                <InputError message={errors.order_date} />
                            </div>

                            <div>
                                <Label htmlFor="pickup_date">Pickup Date</Label>
                                <Input
                                    id="pickup_date"
                                    type="date"
                                    value={data.pickup_date}
                                    onChange={(e) => setData('pickup_date', e.target.value)}
                                    required
                                />
                                <InputError message={errors.pickup_date} />
                            </div>

                            <div>
                                <Label htmlFor="dropoff_date">Dropoff Date</Label>
                                <Input
                                    id="dropoff_date"
                                    type="date"
                                    value={data.dropoff_date}
                                    onChange={(e) => setData('dropoff_date', e.target.value)}
                                    required
                                />
                                <InputError message={errors.dropoff_date} />
                            </div>

                            <div>
                                <Label htmlFor="pickup_location">Pickup Location</Label>
                                <Input
                                    id="pickup_location"
                                    type="text"
                                    value={data.pickup_location}
                                    onChange={(e) => setData('pickup_location', e.target.value)}
                                    required
                                />
                                <InputError message={errors.pickup_location} />
                            </div>

                            <div>
                                <Label htmlFor="dropoff_location">Dropoff Location</Label>
                                <Input
                                    id="dropoff_location"
                                    type="text"
                                    value={data.dropoff_location}
                                    onChange={(e) => setData('dropoff_location', e.target.value)}
                                    required
                                />
                                <InputError message={errors.dropoff_location} />
                            </div>

                            <div className="flex items-center space-x-4">
                                <Button disabled={processing}>
                                    {isEdit ? 'Update Order' : 'Create Order'}
                                </Button>
                                <Link
                                    href={route('orders.index')}
                                    className="px-4 py-2 bg-muted text-foreground rounded hover:bg-muted/70"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
