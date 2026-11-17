<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\BaggageItem;

class UpdateBaggageItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $tag = $this->input('bag_tag_number');

        if (is_string($tag)) {
            $this->merge(['bag_tag_number' => strtoupper(trim($tag))]);
        }
    }

    public function rules(): array
    {
        $baggageItem = $this->route('baggage_item');

        return [
            'passenger_id' => [
                'required', 'integer', 'exists:passengers,id',
            ],
            'flight_id' => [
                'required', 'integer', 'exists:flights,id',
            ],
            'bag_tag_number' => [
                'required', 'string', 'max:50',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('baggage_items', 'bag_tag_number')->ignore(
                    $baggageItem instanceof BaggageItem ? $baggageItem->id : $baggageItem
                ),
            ],
            'bag_weight_kg' => [
                'required', 'numeric', 'gt:0', 'max:999999.99',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bag_tag_number.unique' => 'This bag tag is already registered.',
            'bag_weight_kg.gt' => 'Bag weight must be greater than zero.',
        ];
    }
}