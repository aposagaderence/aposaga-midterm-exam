<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScreeningGateRequest;
use App\Http\Requests\UpdateScreeningGateRequest;
use App\Models\ScreeningGate;
use App\Services\ScreeningGateService;
use Inertia\Inertia;

class ScreeningGateController extends Controller
{
    public function __construct(private ScreeningGateService $service) {}

    public function index()
    {
        return Inertia::render('screening-gates/index', [
            'records' => ScreeningGate::withCount('screeningLogs')->orderBy('gate_code')->get(),
        ]);
    }

    public function create()
    {
        return to_route('screening-gates.index');
    }

    public function store(StoreScreeningGateRequest $request)
    {
        $this->service->create($request->validated());

        return to_route('screening-gates.index')->with('success', 'Screening gate created successfully.');
    }

    public function update(UpdateScreeningGateRequest $request, ScreeningGate $screeningGate)
    {
        $this->service->update($screeningGate, $request->validated());

        return to_route('screening-gates.index')->with('success', 'Screening gate updated successfully.');
    }
}
