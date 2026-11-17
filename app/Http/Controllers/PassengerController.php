<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePassengerRequest;
use App\Http\Requests\UpdatePassengerRequest;
use App\Models\Passenger;
use App\Services\PassengerService;
use Inertia\Inertia;

class PassengerController extends Controller
{
    public function __construct(private PassengerService $service) {}

    public function index()
    {
        return Inertia::render('passengers/index', [
            'records' => Passenger::withCount('baggageItems')->orderBy('passenger_name')->get(),
        ]);
    }

    public function create()
    {
        return to_route('passengers.index');
    }

    public function store(StorePassengerRequest $request)
    {
        $this->service->create($request->validated());

        return to_route('passengers.index')->with('success', 'Passenger created successfully.');
    }

    public function update(UpdatePassengerRequest $request, Passenger $passenger)
    {
        $this->service->update($passenger, $request->validated());

        return to_route('passengers.index')->with('success', 'Passenger updated successfully.');
    }

    

    
}
