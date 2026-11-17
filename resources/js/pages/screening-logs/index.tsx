import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';

type Passenger = {
    passenger_name: string;
};

type BaggageItem = {
    id: number;
    bag_tag_number: string;
    bag_weight_kg: string;
    passenger: Passenger;
};

type ScreeningGate = {
    id: number;
    gate_code: string;
};

type ScreeningLog = {
    id: number;
    screener_officer: string;
    screening_result: 'Passed' | 'Failed';
    flagged_reason: string | null;
    clearance_status: string;
    baggage_item_id: number;
    screening_gate_id: number;
    baggage_item: BaggageItem;
    screening_gate: ScreeningGate;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Props = {
    screeningLogs: {
        data: ScreeningLog[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        total: number;
    };
    baggageItems: BaggageItem[];
    screeningGates: ScreeningGate[];
};

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Screening Logs', href: '/screening-logs' }];

export default function ScreeningLogsIndex({ screeningLogs, baggageItems, screeningGates }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const deleteForm = useForm<Record<string, string>>({});
    const form = useForm({
        baggage_item_id: '',
        screening_gate_id: '',
        screener_officer: '',
        screening_result: 'Passed',
        flagged_reason: '',
        clearance_status: 'Pending',
    });

    function resetForm() {
        setEditingId(null);
        form.reset();
        form.clearErrors();
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (editingId) {
            form.put(route('screening-logs.update', editingId), {
                onSuccess: resetForm,
            });
        } else {
            form.post(route('screening-logs.store'), {
                onSuccess: resetForm,
            });
        }
    }

    function edit(log: ScreeningLog) {
        setEditingId(log.id);
        form.setData({
            baggage_item_id: String(log.baggage_item_id),
            screening_gate_id: String(log.screening_gate_id),
            screener_officer: log.screener_officer,
            screening_result: log.screening_result,
            flagged_reason: log.flagged_reason ?? '',
            clearance_status: log.clearance_status,
        });
        form.clearErrors();
    }

    function remove(log: ScreeningLog) {
        if (window.confirm('Delete this screening log?')) {
            deleteForm.delete(route('screening-logs.destroy', log.id));
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Screening Logs" />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold">Screening Logs</h1>
                    <p className="text-muted-foreground text-sm">
                        Record and review baggage screening results.
                    </p>
                </div>

                <form onSubmit={submit} className="grid gap-4 rounded-xl border p-4 md:grid-cols-2">
                    <h2 className="text-lg font-medium md:col-span-2">
                        {editingId ? 'Update screening' : 'Record screening'}
                    </h2>

                    <div className="grid gap-2">
                        <Label htmlFor="baggage_item_id">Baggage</Label>
                        <select
                            id="baggage_item_id"
                            className="bg-background h-10 rounded-md border px-3 text-sm"
                            value={form.data.baggage_item_id}
                            onChange={(event) => form.setData('baggage_item_id', event.target.value)}
                        >
                            <option value="">Select baggage</option>
                            {baggageItems.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.bag_tag_number} — {item.passenger?.passenger_name ?? 'Passenger'} (
                                    {item.bag_weight_kg} kg)
                                </option>
                            ))}
                        </select>
                        {form.errors.baggage_item_id && (
                            <p className="text-destructive text-sm">{form.errors.baggage_item_id}</p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="screening_gate_id">Screening gate</Label>
                        <select
                            id="screening_gate_id"
                            className="bg-background h-10 rounded-md border px-3 text-sm"
                            value={form.data.screening_gate_id}
                            onChange={(event) => form.setData('screening_gate_id', event.target.value)}
                        >
                            <option value="">Select gate</option>
                            {screeningGates.map((gate) => (
                                <option key={gate.id} value={gate.id}>
                                    {gate.gate_code}
                                </option>
                            ))}
                        </select>
                        {form.errors.screening_gate_id && (
                            <p className="text-destructive text-sm">{form.errors.screening_gate_id}</p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="screener_officer">Screening officer</Label>
                        <Input
                            id="screener_officer"
                            value={form.data.screener_officer}
                            onChange={(event) => form.setData('screener_officer', event.target.value)}
                        />
                        {form.errors.screener_officer && (
                            <p className="text-destructive text-sm">{form.errors.screener_officer}</p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="screening_result">Result</Label>
                        <select
                            id="screening_result"
                            className="bg-background h-10 rounded-md border px-3 text-sm"
                            value={form.data.screening_result}
                            onChange={(event) => form.setData('screening_result', event.target.value)}
                        >
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                        </select>
                        {form.errors.screening_result && (
                            <p className="text-destructive text-sm">{form.errors.screening_result}</p>
                        )}
                    </div>

                    {form.data.screening_result === 'Failed' && (
                        <div className="grid gap-2 md:col-span-2">
                            <Label htmlFor="flagged_reason">Reason for flagging</Label>
                            <textarea
                                id="flagged_reason"
                                className="bg-background min-h-24 rounded-md border px-3 py-2 text-sm"
                                value={form.data.flagged_reason}
                                onChange={(event) => form.setData('flagged_reason', event.target.value)}
                            />
                            {form.errors.flagged_reason && (
                                <p className="text-destructive text-sm">{form.errors.flagged_reason}</p>
                            )}
                        </div>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="clearance_status">Clearance status</Label>
                        <select
                            id="clearance_status"
                            className="bg-background h-10 rounded-md border px-3 text-sm"
                            value={form.data.clearance_status}
                            onChange={(event) => form.setData('clearance_status', event.target.value)}
                            disabled={form.data.screening_result === 'Failed'}
                        >
                            <option value="Pending">Pending</option>
                            <option value="Cleared">Cleared</option>
                            <option value="Held">Held</option>
                        </select>
                        {form.errors.clearance_status && (
                            <p className="text-destructive text-sm">{form.errors.clearance_status}</p>
                        )}
                    </div>

                    <div className="flex items-end gap-2">
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? 'Saving…' : editingId ? 'Save changes' : 'Record screening'}
                        </Button>
                        {editingId && (
                            <Button type="button" variant="outline" onClick={resetForm}>
                                Cancel
                            </Button>
                        )}
                    </div>
                </form>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/50">
                            <tr>
                                <th className="p-3 font-medium">Baggage</th>
                                <th className="p-3 font-medium">Gate</th>
                                <th className="p-3 font-medium">Officer</th>
                                <th className="p-3 font-medium">Result</th>
                                <th className="p-3 font-medium">Clearance</th>
                                <th className="p-3 font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {screeningLogs.data.map((log) => (
                                <tr key={log.id} className="border-t">
                                    <td className="p-3">{log.baggage_item?.bag_tag_number ?? '—'}</td>
                                    <td className="p-3">{log.screening_gate?.gate_code ?? '—'}</td>
                                    <td className="p-3">{log.screener_officer}</td>
                                    <td className="p-3">{log.screening_result}</td>
                                    <td className="p-3">{log.clearance_status}</td>
                                    <td className="p-3">
                                        <div className="flex gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => edit(log)}
                                            >
                                                Edit
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                disabled={deleteForm.processing}
                                                onClick={() => remove(log)}
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {screeningLogs.data.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="text-muted-foreground p-8 text-center">
                                        No screenings have been recorded yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {screeningLogs.last_page > 1 && (
                    <nav
                        className="flex flex-wrap items-center justify-between gap-3"
                        aria-label="Screening log pagination"
                    >
                        <p className="text-muted-foreground text-sm">
                            Page {screeningLogs.current_page} of {screeningLogs.last_page} (
                            {screeningLogs.total} records)
                        </p>
                        <div className="flex flex-wrap gap-1">
                            {screeningLogs.links.map((link, index) => (
                                <Button
                                    key={`${index}-${link.label}`}
                                    asChild={Boolean(link.url)}
                                    type="button"
                                    size="sm"
                                    variant={link.active ? 'default' : 'outline'}
                                    disabled={!link.url}
                                >
                                    {link.url ? (
                                        <Link href={link.url} preserveScroll>
                                            {link.label.replace('&laquo;', '«').replace('&raquo;', '»')}
                                        </Link>
                                    ) : (
                                        <span>{link.label.replace('&laquo;', '«').replace('&raquo;', '»')}</span>
                                    )}
                                </Button>
                            ))}
                        </div>
                    </nav>
                )}
            </div>
        </AppLayout>
    );
}