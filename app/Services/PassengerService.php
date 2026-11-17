<?php

namespace App\Services;

use App\Models\Passenger;
use Illuminate\Support\Facades\DB;

class PassengerService
{
    public function create(array $data): Passenger
    {
        return DB::transaction(function () use ($data) {
            $data = $this->normalize($data);

            return Passenger::create($data);
        });
    }

    public function update(Passenger $passenger, array $data): Passenger
    {
        return DB::transaction(function () use ($passenger, $data) {
            $passenger->update($this->normalize($data));

            return $passenger->refresh();
        });
    }

    private function normalize(array $data): array
    {
        $data['passenger_passport'] = strtoupper(trim($data['passenger_passport']));
        $data['passenger_name'] = trim($data['passenger_name']);

        return $data;
    }
}
