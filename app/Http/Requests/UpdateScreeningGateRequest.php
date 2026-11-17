<?php

namespace App\Http\Requests;

use App\Models\ScreeningGate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScreeningGateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gate = $this->route('screening_gate');

        return [
            'gate_code' => [
                'required', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('screening_gates', 'gate_code')->ignore(
                    $gate instanceof ScreeningGate ? $gate->id : $gate
                ),
            ],
        ];
    }
}
