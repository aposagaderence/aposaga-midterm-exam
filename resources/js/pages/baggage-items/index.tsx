import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';

type Passenger = {
    id: number;
    passenger_name: string;
    passenger_passport: string;
};

type Flight = {
    id: number;
    flight_code: string;
};

type BaggageItem = {
    id: number;
    bag_tag_number: string;
    bag_weight_kg: string;
    heavy_cargo: boolean;
    passenger: Passenger;
    flight: Flight;
};

type Props = {
    baggageItems: BaggageItem[];
    passengers: Passenger[];
    flights: Flight[];
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Baggage Items', href: '/baggage-items' }];

export default function BaggageItemsIndex({ baggageItems, passengers, flights }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const deleteForm = useForm<Record<string, string>>({});
    const form = useForm({
        passenger_id: '',
        flight_id: '',
        bag_tag_number: '',
        bag_weight_kg: '',
    });

    function resetForm() {
        setEditingId(null);
        form.reset();
        form.clearErrors();
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (editingId) {
            form.put(route('baggage-items.update', editingId), {
                onSuccess: resetForm,
            });
        } else {
            form.post(route('baggage-items.store'), {
                onSuccess: resetForm,
            });
        }
    }

    function edit(item: BaggageItem) {
        setEditingId(item.id);
        form.setData({
            passenger_id: String(item.passenger.id),
            flight_id: String(item.flight.id),
            bag_tag_number: item.bag_tag_number,
            bag_weight_kg: item.bag_weight_kg,
        });
        form.clearErrors();
    }

    function remove(item: BaggageItem) {
        if (window.confirm(`Delete baggage item ${item.bag_tag_number}?`)) {
            deleteForm.delete(route('baggage-items.destroy', item.id));
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Baggage Items" />
            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">Baggage Items</h1>
                    <p className="text-muted-foreground text-sm">Register and manage passenger baggage.</p>
                </div>

                {(passengers.length === 0 || flights.length === 0) && (
                    <div role="alert" className="rounded-md border border-amber-600/30 bg-amber-600/10 p-4 text-sm">
                        <p className="font-medium">Add the required records before registering baggage:</p>
                        <ul className="mt-2 list-inside list-disc">
                            {passengers.length === 0 && (
                                <li>
                                    <Link className="underline underline-offset-4" href={route('passengers.index')}>
                                        Create a passenger
                                    </Link>
                                </li>
                            )}
                            {flights.length === 0 && (
                                <li>
                                    <Link className="underline underline-offset-4" href={route('flights.index')}>
                                        Create a flight
                                    </Link>
                                </li>
                            )}
                        </ul>
                    </div>
                )}

                <form onSubmit={submit} className="grid gap-4 rounded-xl border p-4 md:grid-cols-2">
                    <h2 className="text-lg font-medium md:col-span-2">{editingId ? 'Update baggage item' : 'Register baggage'}</h2>
                    <div className="grid gap-2">
                        <Label htmlFor="passenger_id">Passenger</Label>
                        <select
                            id="passenger_id"
                            className="bg-background h-10 rounded-md border px-3 text-sm"
                            value={form.data.passenger_id}
                            onChange={(event) => form.setData('passenger_id', event.target.value)}
                        >
                            <option value="">Select passenger</option>
                            {passengers.map((passenger) => (
                                <option key={passenger.id} value={passenger.id}>
                                    {passenger.passenger_name} ({passenger.passenger_passport})
                                </option>
                            ))}
                        </select>
                        {form.errors.passenger_id && <p className="text-destructive text-sm">{form.errors.passenger_id}</p>}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="flight_id">Flight</Label>
                        <select
                            id="flight_id"
                            className="bg-background h-10 rounded-md border px-3 text-sm"
                            value={form.data.flight_id}
                            onChange={(event) => form.setData('flight_id', event.target.value)}
                        >
                            <option value="">Select flight</option>
                            {flights.map((flight) => (
                                <option key={flight.id} value={flight.id}>
                                    {flight.flight_code}
                                </option>
                            ))}
                        </select>
                        {form.errors.flight_id && <p className="text-destructive text-sm">{form.errors.flight_id}</p>}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="bag_tag_number">Bag tag number</Label>
                        <Input
                            id="bag_tag_number"
                            value={form.data.bag_tag_number}
                            onChange={(event) => form.setData('bag_tag_number', event.target.value)}
                        />
                        {form.errors.bag_tag_number && <p className="text-destructive text-sm">{form.errors.bag_tag_number}</p>}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="bag_weight_kg">Weight (kg)</Label>
                        <Input
                            id="bag_weight_kg"
                            type="number"
                            min="0.01"
                            step="0.01"
                            value={form.data.bag_weight_kg}
                            onChange={(event) => form.setData('bag_weight_kg', event.target.value)}
                        />
                        {form.errors.bag_weight_kg && <p className="text-destructive text-sm">{form.errors.bag_weight_kg}</p>}
                        {Number(form.data.bag_weight_kg) > 32 && (
                            <p role="status" className="text-amber-700 text-sm dark:text-amber-400">
                                Heavy cargo handling will be flagged for baggage over 32 kg.
                            </p>
                        )}
                    </div>
                    <div className="flex gap-2 md:col-span-2">
                        <Button type="submit" disabled={form.processing || passengers.length === 0 || flights.length === 0}>
                            {form.processing ? 'Saving…' : editingId ? 'Save changes' : 'Register baggage'}
                        </Button>
                        {editingId && (
                            <Button type="button" variant="outline" onClick={resetForm}>
                                Cancel
                            </Button>
                        )}
                    </div>
                </form>

                {deleteForm.errors.delete && (
                    <p role="alert" className="rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">
                        {deleteForm.errors.delete}
                    </p>
                )}

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/50">
                            <tr>
                                <th className="p-3 font-medium">Tag</th>
                                <th className="p-3 font-medium">Passenger</th>
                                <th className="p-3 font-medium">Flight</th>
                                <th className="p-3 font-medium">Weight</th>
                                <th className="p-3 font-medium">Cargo</th>
                                <th className="p-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {baggageItems.map((item) => (
                                <tr key={item.id} className="border-t">
                                    <td className="p-3 font-medium">{item.bag_tag_number}</td>
                                    <td className="p-3">{item.passenger?.passenger_name ?? '—'}</td>
                                    <td className="p-3">{item.flight?.flight_code ?? '—'}</td>
                                    <td className="p-3">{item.bag_weight_kg} kg</td>
                                    <td className="p-3">{item.heavy_cargo ? 'Heavy cargo' : 'Standard'}</td>
                                    <td className="p-3">
                                        <div className="flex gap-2">
                                            <Button type="button" variant="outline" size="sm" onClick={() => edit(item)}>
                                                Edit
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                disabled={deleteForm.processing}
                                                onClick={() => remove(item)}
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {baggageItems.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="text-muted-foreground p-8 text-center">
                                        No baggage has been registered yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
