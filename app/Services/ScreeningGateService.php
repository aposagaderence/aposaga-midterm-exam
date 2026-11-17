<?php

namespace App\Services;

use App\Models\ScreeningGate;
use Illuminate\Support\Facades\DB;

class ScreeningGateService
{
    public function create(array $data): ScreeningGate
    {
        return DB::transaction(function () use ($data) {
            $data['gate_code'] = strtoupper(trim($data['gate_code']));

            return ScreeningGate::create($data);
        });
    }

    public function update(ScreeningGate $gate, array $data): ScreeningGate
    {
        return DB::transaction(function () use ($gate, $data) {
            $data['gate_code'] = strtoupper(trim($data['gate_code']));
            $gate->update($data);

            return $gate->refresh();
        });
    }
}
