import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type FormEvent, useState } from 'react';

type ReferenceRecord = {
    id: number;
    [key: string]: number | string;
};

type ReferenceField = {
    key: string;
    label: string;
    placeholder: string;
};

type Props = {
    title: string;
    description: string;
    records: ReferenceRecord[];
    fields: ReferenceField[];
    countKey: string;
    countLabel: string;
    storeUrl: string;
    updateUrl: (id: number) => string;
    deleteUrl?: (id: number) => string;
};

export default function ReferenceManager({
    title,
    description,
    records,
    fields,
    countKey,
    countLabel,
    storeUrl,
    updateUrl,
    deleteUrl,
}: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const { flash } = usePage<SharedData>().props;

    const form = useForm<Record<string, string>>(
        fields.reduce<Record<string, string>>((values, field) => {
            values[field.key] = '';
            return values;
        }, {}),
    );

    const deleteForm = useForm<Record<string, string>>({});
    const singular = title.endsWith('s') ? title.slice(0, -1) : title;
    const resourceName = title.toLowerCase().replace(/\s+/g, '-');

    function resetForm() {
        setEditingId(null);
        form.reset();
        form.clearErrors();
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const options = { onSuccess: resetForm };

        if (editingId) {
            form.put(updateUrl(editingId), options);
        } else {
            form.post(storeUrl, options);
        }
    }

    function edit(record: ReferenceRecord) {
        setEditingId(record.id);
        form.setData(
            fields.reduce<Record<string, string>>((values, field) => {
                values[field.key] = String(record[field.key] ?? '');
                return values;
            }, {}),
        );
        form.clearErrors();
    }

    function remove(record: ReferenceRecord) {
        if (!window.confirm(`Delete this ${singular.toLowerCase()}?`)) {
            return;
        }

        const url =
            deleteUrl?.(record.id) ??
            route(`${resourceName}.destroy`, record.id);

        deleteForm.delete(url);
    }

    return (
        <>
            <Head title={title} />

            <div className="flex flex-col gap-6 p-4 md:p-6">
                <header>
                    <h1 className="text-2xl font-semibold">{title}</h1>
                    <p className="text-muted-foreground text-sm">{description}</p>
                </header>

                {flash?.success && (
                    <p
                        role="status"
                        className="rounded-md border border-green-600/30 bg-green-600/10 p-3 text-sm"
                    >
                        {flash.success}
                    </p>
                )}

                {deleteForm.errors.delete && (
                    <p
                        role="alert"
                        className="rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive"
                    >
                        {deleteForm.errors.delete}
                    </p>
                )}

                <form
                    onSubmit={submit}
                    className="grid gap-4 rounded-xl border p-4 md:grid-cols-2"
                >
                    <h2 className="text-lg font-medium md:col-span-2">
                        {editingId
                            ? `Update ${singular.toLowerCase()}`
                            : `Add ${singular.toLowerCase()}`}
                    </h2>

                    {fields.map((field) => (
                        <div key={field.key} className="grid gap-2">
                            <Label htmlFor={field.key}>{field.label}</Label>
                            <Input
                                id={field.key}
                                value={form.data[field.key]}
                                placeholder={field.placeholder}
                                onChange={(event) =>
                                    form.setData(field.key, event.target.value)
                                }
                            />
                            {form.errors[field.key] && (
                                <p className="text-destructive text-sm">
                                    {form.errors[field.key]}
                                </p>
                            )}
                        </div>
                    ))}

                    <div className="flex gap-2 md:col-span-2">
                        <Button type="submit" disabled={form.processing}>
                            {form.processing
                                ? 'Saving…'
                                : editingId
                                  ? 'Save changes'
                                  : 'Add record'}
                        </Button>

                        {editingId && (
                            <Button
                                type="button"
                                variant="outline"
                                onClick={resetForm}
                            >
                                Cancel
                            </Button>
                        )}
                    </div>
                </form>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/50">
                            <tr>
                                {fields.map((field) => (
                                    <th
                                        key={field.key}
                                        className="p-3 font-medium"
                                    >
                                        {field.label}
                                    </th>
                                ))}
                                <th className="p-3 font-medium">{countLabel}</th>
                                <th className="p-3 font-medium">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            {records.map((record) => (
                                <tr key={record.id} className="border-t">
                                    {fields.map((field) => (
                                        <td key={field.key} className="p-3">
                                            {record[field.key]}
                                        </td>
                                    ))}
                                    <td className="p-3">{record[countKey]}</td>
                                    <td className="p-3">
                                        <div className="flex gap-2">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => edit(record)}
                                            >
                                                Edit
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="destructive"
                                                size="sm"
                                                disabled={deleteForm.processing}
                                                onClick={() => remove(record)}
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}

                            {records.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={fields.length + 2}
                                        className="text-muted-foreground p-8 text-center"
                                    >
                                        No {title.toLowerCase()} have been added yet.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}