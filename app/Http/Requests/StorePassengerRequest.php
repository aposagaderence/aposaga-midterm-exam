<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePassengerRequest extends FormRequest
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
        return [
           'passenger_passport' => [
    'required',
    'numeric',
    'max:255',
    'regex:/^[0-9]+$/',
    'unique:passengers,passenger_passport',
],
            'passenger_name' => ['required', 'string', 'max:255'],
        ];
    }
}
