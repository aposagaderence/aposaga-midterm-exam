import ReferenceManager from '@/components/reference-manager';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

type PassengerRecord = {
    id: number;
    passenger_passport: string;
    passenger_name: string;
    baggage_items_count: number;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Passengers', href: '/passengers' }];

export default function PassengersIndex({ records }: { records: PassengerRecord[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <ReferenceManager
                title="Passengers"
                description="Manage passengers who can be assigned baggage."
                records={records}
                fields={[
                    { key: 'passenger_name', label: 'Passenger name', placeholder: 'Full name' },
                    { key: 'passenger_passport', label: 'Passport number', placeholder: 'Passport number' },
                ]}
                countKey="baggage_items_count"
                countLabel="Bags"
                storeUrl={route('passengers.store')}
                updateUrl={(id) => route('passengers.update', id)}
            />
        </AppLayout>

        
    );
}
