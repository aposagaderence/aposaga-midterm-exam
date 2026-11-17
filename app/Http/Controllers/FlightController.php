<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Models\Flight;
use App\Services\FlightService;
use Inertia\Inertia;

class FlightController extends Controller
{
    public function __construct(private FlightService $service) {}

    public function index()
    {
        return Inertia::render('flights/index', [
            'records' => Flight::withCount('baggageItems')->orderBy('flight_code')->get(),
        ]);
    }

    public function create()
    {
        return to_route('flights.index');
    }

    public function store(StoreFlightRequest $request)
    {
        $this->service->create($request->validated());

        return to_route('flights.index')->with('success', 'Flight created successfully.');
    }

    public function update(UpdateFlightRequest $request, Flight $flight)
    {
        $this->service->update($flight, $request->validated());

        return to_route('flights.index')->with('success', 'Flight updated successfully.');
    }
}
