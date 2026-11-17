<?php

namespace App\Services;

use App\Models\Flight;
use Illuminate\Support\Facades\DB;

class FlightService
{
    public function create(array $data): Flight
    {
        return DB::transaction(function () use ($data) {
            $data['flight_code'] = strtoupper(trim($data['flight_code']));

            return Flight::create($data);
        });
    }

    public function update(Flight $flight, array $data): Flight
    {
        return DB::transaction(function () use ($flight, $data) {
            $data['flight_code'] = strtoupper(trim($data['flight_code']));
            $flight->update($data);

            return $flight->refresh();
        });
    }
}
