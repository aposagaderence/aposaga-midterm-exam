import ReferenceManager from '@/components/reference-manager';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

type ScreeningGateRecord = {
    id: number;
    gate_code: string;
    screening_logs_count: number;
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Screening Gates', href: '/screening-gates' }];

export default function ScreeningGatesIndex({ records }: { records: ScreeningGateRecord[] }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <ReferenceManager
                title="Screening Gates"
                description="Manage gates used when screening baggage."
                records={records}
                fields={[{ key: 'gate_code', label: 'Gate code', placeholder: 'e.g. GATE-1' }]}
                countKey="screening_logs_count"
                countLabel="Screenings"
                storeUrl={route('screening-gates.store')}
                updateUrl={(id) => route('screening-gates.update', id)}
            />
        </AppLayout>
    );
}
