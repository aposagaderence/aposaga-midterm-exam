<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScreeningGateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gate_code' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/', 'unique:screening_gates,gate_code'],
        ];
    }
}
