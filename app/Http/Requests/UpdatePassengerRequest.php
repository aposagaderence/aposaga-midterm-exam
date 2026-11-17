<?php

namespace App\Http\Requests;

use App\Models\Passenger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePassengerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $passport = $this->input('passenger_passport');

        if (is_string($passport)) {
            $this->merge(['passenger_passport' => strtoupper(trim($passport))]);
        }
    }

    public function rules(): array
    {
        $passenger = $this->route('passenger');

        return [
            'passenger_passport' => [
    'required',
    'string',
    'max:255',
    'regex:/^[0-9]+$/',
    Rule::unique('passengers', 'passenger_passport')->ignore(
        $passenger instanceof Passenger ? $passenger->id : $passenger
    ),
],
            'passenger_name' => ['required', 'string', 'max:255'],
        ];
    }
}
