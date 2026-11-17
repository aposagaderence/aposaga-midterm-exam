<?php

namespace App\Http\Requests;

use App\Models\Flight;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $flight = $this->route('flight');

        return [
         'flight_code' => [
    'required',
    'string',
    'max:30',
    'regex:/^[0-9]+$/',
    Rule::unique('flights', 'flight_code')->ignore(
        $flight instanceof Flight ? $flight->id : $flight
    ),
],
        ];
    }
}
