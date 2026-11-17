<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
           'flight_code' => [
    'required',
    'string',
    'max:30',
    'regex:/^[0-9]+$/',
    'unique:flights,flight_code',
],
        ];
    }
}
