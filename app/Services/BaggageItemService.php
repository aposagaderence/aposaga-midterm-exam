<?php

namespace App\Services;

use App\Models\BaggageItem;
use Illuminate\Support\Facades\DB;

class BaggageItemService
{
    public function create(array $data): BaggageItem
    {
        return DB::transaction(function () use ($data) {
            $data['bag_tag_number'] = strtoupper(
                trim($data['bag_tag_number'])
            );

            return BaggageItem::create($data);
        });
    }

    public function update(BaggageItem $baggage, array $data): BaggageItem
    {
        return DB::transaction(function () use ($baggage, $data) {
            $data['bag_tag_number'] = strtoupper(
                trim($data['bag_tag_number'])
            );

            $baggage->update($data);

            return $baggage->refresh();
        });
    }
}
