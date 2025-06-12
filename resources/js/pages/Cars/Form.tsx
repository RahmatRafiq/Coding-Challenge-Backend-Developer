import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent, useEffect, useRef } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Separator } from '@/components/ui/separator';
import Dropzoner from '@/components/dropzoner';

import type { Car, CarImageMedia } from '@/types/Cars';

type DropzoneFile = File & {
    previewElement?: HTMLElement & {
        querySelector: (selectors: string) => Element | null;
    };
    name: string;
};

type DropzoneInstance = {
    destroy: () => void;
    on: (event: string, callback: (...args: unknown[]) => void) => void;
    files: DropzoneFile[];
    removeFile: (file: DropzoneFile) => void;
};

export default function CarForm({ car, carImage }: { car?: Car; carImage?: CarImageMedia }) {
    const isEdit = !!car;
    const initialImages: string[] = carImage ? [carImage.file_name] : [];
    const initialFiles =
        carImage
            ? [
                {
                    file_name: carImage.file_name,
                    size: carImage.size,
                    original_url: carImage.url,
                },
            ]
            : [];

    const { data, setData, post, put, processing, errors } = useForm({
        car_name: car ? car.car_name : '',
        day_rate: car ? car.day_rate : '',
        month_rate: car ? car.month_rate : '',
        image_car: car ? car.image_car : '',
        'car-images': initialImages,
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

    const csrf_token =
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
    const dropzoneRef = useRef<HTMLDivElement>(null);
    const dzInstance = useRef<DropzoneInstance | null>(null);

    useEffect(() => {
        if (dropzoneRef.current) {
            if (dzInstance.current) {
                dzInstance.current.destroy();
            }
            dzInstance.current = Dropzoner(dropzoneRef.current, 'car-images', {
                urlStore: route('storage.store'),
                urlDestroy: route('storage.destroy'),
                urlDestroyPermanent: route('cars.deleteFile'),
                csrf: csrf_token,
                acceptedFiles: 'image/*',
                maxFiles: 1,
                maxSizeMB: 5,
                minSizeMB: 0.05,
                minFiles: 1,
                files: initialFiles,
                kind: 'image',
            }) as unknown as DropzoneInstance;

            dzInstance.current.on(
                'success',
                (...args: unknown[]) => {
                    const file = args[0] as DropzoneFile;
                    const response = args[1] as { name: string; url: string };
                    setData('car-images', [response.name]);
                    const thumb = file.previewElement?.querySelector('[data-dz-thumbnail]') as HTMLImageElement | null;
                    if (thumb) thumb.src = response.url;
                    dzInstance.current?.files.forEach((f) => {
                        if (f !== file) {
                            dzInstance.current?.removeFile(f);
                        }
                    });
                }
            );

            dzInstance.current.on('removedfile', (...args: unknown[]) => {
                const file = args[0] as DropzoneFile;
                setData(
                    'car-images',
                    (data['car-images'] || []).filter((name: string) => name !== file.name)
                );

                fetch(route('storage.destroy'), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf_token, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ filename: file.name }),
                });
            });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [csrf_token]);

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
                                <Label htmlFor="car-image">Car Image</Label>
                                <div
                                    ref={dropzoneRef}
                                    className="dropzone border-dashed border-2 rounded p-4 dark:text-black"
                                ></div>
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