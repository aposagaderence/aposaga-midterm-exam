import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

type DashboardCounts = {
    passengers: number;
    flights: number;
    baggageItems: number;
    screeningGates: number;
    screeningLogs: number;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const cards: {
    key: keyof DashboardCounts;
    title: string;
    href: string;
}[] = [
    {
        key: 'passengers',
        title: 'Passengers',
        href: route('passengers.index'),
    },
    {
        key: 'flights',
        title: 'Flights',
        href: route('flights.index'),
    },
    {
        key: 'baggageItems',
        title: 'Baggage Items',
        href: route('baggage-items.index'),
    },
    {
        key: 'screeningGates',
        title: 'Screening Gates',
        href: route('screening-gates.index'),
    },
    {
        key: 'screeningLogs',
        title: 'Screening Logs',
        href: route('screening-logs.index'),
    },
];

export default function Dashboard({ counts }: { counts: DashboardCounts }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <main className="flex flex-col gap-6 p-4 md:p-6">
                <header>
                    <h1 className="text-2xl font-semibold">Dashboard</h1>
                    <p className="text-muted-foreground text-sm">
                        Airport operations overview
                    </p>
                </header>

                <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {cards.map((card) => (
                        <Link
                            key={card.key}
                            href={card.href}
                            className="rounded-xl border p-5 transition-colors hover:bg-muted/50"
                        >
                            <h2 className="text-sm font-medium text-muted-foreground">
                                {card.title}
                            </h2>
                            <p className="mt-2 text-3xl font-semibold">
                                {counts[card.key]}
                            </p>
                            <span className="mt-3 inline-block text-sm underline underline-offset-4">
                                Manage {card.title.toLowerCase()}
                            </span>
                        </Link>
                    ))}
                </section>
            </main>
        </AppLayout>
    );
}