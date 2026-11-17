<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBaggageItemRequest;
use App\Http\Requests\UpdateBaggageItemRequest;
use App\Models\BaggageItem;
use App\Models\Flight;
use App\Models\Passenger;
use App\Services\BaggageItemService;
use Inertia\Inertia;

class BaggageItemController extends Controller
{
    public function __construct(
        private BaggageItemService $service
    ) {}

    public function index()
    {
        $baggage = BaggageItem::with([
            'passenger',
            'flight',
            'screeningLogs',
        ])->latest()->get();

        return Inertia::render('baggage-items/index', [
            'baggageItems' => $baggage,
            'passengers' => Passenger::select(
                'id', 'passenger_passport', 'passenger_name'
            )->orderBy('passenger_name')->get(),
            'flights' => Flight::select('id', 'flight_code')
                ->orderBy('flight_code')->get(),
        ]);
    }

    public function create()
    {
        return to_route('baggage-items.index');
    }

    public function store(StoreBaggageItemRequest $request)
    {
        $this->service->create(
            $request->validated()
        );

        return to_route('baggage-items.index')
            ->with('success', 'Baggage item registered successfully.');
    }

    public function update(UpdateBaggageItemRequest $request, BaggageItem $baggageItem)
    {
        $this->service->update($baggageItem, $request->validated());

        return to_route('baggage-items.index')
            ->with('success', 'Baggage item updated successfully.');
    }
}