import ReferenceManager from '@/components/reference-manager';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

type FlightRecord = {
    id: number;
    flight_code: string;
    baggage_items_count: number;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Flights', href: '/flights' }];

export default function FlightsIndex({ records }: { records: FlightRecord[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <ReferenceManager
                title="Flights"
                description="Manage flights used to assign passenger baggage."
                records={records}
                fields={[{ key: 'flight_code', label: 'Flight code', placeholder: 'e.g. AP123' }]}
                countKey="baggage_items_count"
                countLabel="Bags"
                storeUrl={route('flights.store')}
                updateUrl={(id) => route('flights.update', id)}
            />
        </AppLayout>
    );
}
