<?php

use App\Http\Controllers\BaggageItemController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\ScreeningGateController;
use App\Http\Controllers\ScreeningLogController;
use App\Models\BaggageItem;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\ScreeningGate;
use App\Models\ScreeningLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Option A: Redirect the home page directly to the Login page
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Option B: If you prefer keeping the welcome page instead, uncomment the line below:
// Route::get('/', function () { return view('welcome'); });

// Protected Routes (Accessible only to logged-in users)
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('dashboard', [
            'counts' => [
                'passengers' => Passenger::count(),
                'flights' => Flight::count(),
                'baggageItems' => BaggageItem::count(),
                'screeningGates' => ScreeningGate::count(),
                'screeningLogs' => ScreeningLog::count(),
            ],
        ]);
    })->name('dashboard');

    Route::resource('baggage-items', BaggageItemController::class)
        ->only(['index', 'create', 'store', 'update']);

    Route::resource('passengers', PassengerController::class)
        ->only(['index', 'create', 'store', 'update']);

    Route::resource('flights', FlightController::class)
        ->only(['index', 'create', 'store', 'update']);

    Route::resource('screening-gates', ScreeningGateController::class)
        ->only(['index', 'create', 'store', 'update']);

    Route::resource('screening-logs', ScreeningLogController::class)
        ->only(['index', 'create', 'store', 'update']);

    Route::delete('/baggage-items/{baggage_item}', function (BaggageItem $baggageItem) {
        DB::transaction(function () use ($baggageItem) {
            $baggageItem->screeningLogs()->delete();
            $baggageItem->delete();
        });

        return to_route('baggage-items.index')
            ->with('success', 'Baggage item deleted successfully.');
    })->name('baggage-items.destroy');

    Route::delete('/passengers/{passenger}', function (Passenger $passenger) {
        DB::transaction(function () use ($passenger) {
            $baggageIds = $passenger->baggageItems()->pluck('id');
            ScreeningLog::whereIn('baggage_item_id', $baggageIds)->delete();
            $passenger->baggageItems()->delete();
            $passenger->delete();
        });

        return to_route('passengers.index')
            ->with('success', 'Passenger deleted successfully.');
    })->name('passengers.destroy');

    Route::delete('/flights/{flight}', function (Flight $flight) {
        DB::transaction(function () use ($flight) {
            $baggageIds = $flight->baggageItems()->pluck('id');
            ScreeningLog::whereIn('baggage_item_id', $baggageIds)->delete();
            $flight->baggageItems()->delete();
            $flight->delete();
        });

        return to_route('flights.index')
            ->with('success', 'Flight deleted successfully.');
    })->name('flights.destroy');

    Route::delete('/screening-gates/{screening_gate}', function (ScreeningGate $screeningGate) {
        DB::transaction(function () use ($screeningGate) {
            $screeningGate->screeningLogs()->delete();
            $screeningGate->delete();
        });

        return to_route('screening-gates.index')
            ->with('success', 'Screening gate deleted successfully.');
    })->name('screening-gates.destroy');

    Route::delete('/screening-logs/{screening_log}', function (ScreeningLog $screeningLog) {
        $screeningLog->delete();

        return to_route('screening-logs.index')
            ->with('success', 'Screening log deleted successfully.');
    })->name('screening-logs.destroy');
});

// Authentication Routes (Login, Register, Logout, Password Reset)
require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';