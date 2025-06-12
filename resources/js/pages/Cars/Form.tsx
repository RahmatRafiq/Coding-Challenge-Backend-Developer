import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Separator } from '@/components/ui/separator';

import type { Car } from '@/types/Cars';

export default function CarForm({ car }: { car?: Car }) {
    const isEdit = !!car;
    const { data, setData, post, put, processing, errors } = useForm({
        car_name: car ? car.car_name : '',
        day_rate: car ? car.day_rate : '',
        month_rate: car ? car.month_rate : '',
        image_car: car ? car.image_car : '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Cars', href: '/cars' },
        { title: isEdit ? 'Edit Car' : 'Create Car', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('cars.update', car!.id));
        } else {
            post(route('cars.store'));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Car' : 'Create Car'} />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Car Management</h1>
                <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                    <Separator className="my-6 md:hidden" />
                    <div className="flex-1 md:max-w-2xl space-y-6">
                        <HeadingSmall
                            title={isEdit ? 'Edit Car' : 'Create Car'}
                            description="Fill in the details below"
                        />
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="car_name">Car Name</Label>
                                <Input
                                    id="car_name"
                                    type="text"
                                    value={data.car_name}
                                    onChange={(e) => setData('car_name', e.target.value)}
                                    required
                                />
                                <InputError message={errors.car_name} />
                            </div>
                            <div>
                                <Label htmlFor="day_rate">Day Rate</Label>
                                <Input
                                    id="day_rate"
                                    type="number"
                                    value={data.day_rate}
                                    onChange={(e) => setData('day_rate', e.target.value)}
                                    required
                                />
                                <InputError message={errors.day_rate} />
                            </div>
                            <div>
                                <Label htmlFor="month_rate">Month Rate</Label>
                                <Input
                                    id="month_rate"
                                    type="number"
                                    value={data.month_rate}
                                    onChange={(e) => setData('month_rate', e.target.value)}
                                    required
                                />
                                <InputError message={errors.month_rate} />
                            </div>
                            <div>
                                <Label htmlFor="image_car">Image URL</Label>
                                <Input
                                    id="image_car"
                                    type="text"
                                    value={data.image_car}
                                    onChange={(e) => setData('image_car', e.target.value)}
                                />
                                <InputError message={errors.image_car} />
                            </div>
                            <div className="flex items-center space-x-4">
                                <Button disabled={processing}>
                                    {isEdit ? 'Update Car' : 'Create Car'}
                                </Button>
                                <Link
                                    href={route('cars.index')}
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