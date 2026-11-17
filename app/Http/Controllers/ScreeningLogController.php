<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScreeningLogRequest;
use App\Http\Requests\UpdateScreeningLogRequest;
use App\Models\BaggageItem;
use App\Models\ScreeningGate;
use App\Models\ScreeningLog;
use App\Services\ScreeningLogService;
use Inertia\Inertia;

class ScreeningLogController extends Controller
{
    public function __construct(
        private ScreeningLogService $service
    ) {}

    public function index()
    {
        $logs = ScreeningLog::with([
            'baggageItem.passenger',
            'baggageItem.flight',
            'screeningGate',
        ])->latest()->paginate(10);

        return Inertia::render('screening-logs/index', [
            'screeningLogs' => $logs,
            'baggageItems' => BaggageItem::with('passenger')
                ->orderBy('bag_tag_number')
                ->get(['id', 'bag_tag_number', 'bag_weight_kg', 'passenger_id']),
            'screeningGates' => ScreeningGate::select('id', 'gate_code')
                ->orderBy('gate_code')->get(),
        ]);
    }

    public function create()
    {
        return to_route('screening-logs.index');
    }

    public function store(StoreScreeningLogRequest $request)
    {
        $this->service->create(
            $request->validated()
        );

        return to_route('screening-logs.index')
            ->with('success', 'Screening log created successfully.');
    }

    public function update(UpdateScreeningLogRequest $request, ScreeningLog $screeningLog)
    {
        $this->service->update($screeningLog, $request->validated());

        return to_route('screening-logs.index')
            ->with('success', 'Screening log updated successfully.');
    }
}