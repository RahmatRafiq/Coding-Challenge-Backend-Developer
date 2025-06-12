export interface Order {
    id: number;
    car_id: number;
    order_date: string;
    pickup_date: string;
    dropoff_date: string;
    pickup_location: string;
    dropoff_location: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    car: Car;
}
