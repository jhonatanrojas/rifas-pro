<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteDrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'raffle_id' => ['required', 'exists:raffles,id'],
            'prize_description' => ['required', 'string', 'max:500'],
            'confirm_draw' => ['required', 'accepted'],
            'winning_number' => ['nullable', 'integer', 'min:0'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'execution_mode' => ['nullable', 'in:automatic,manual_external'],
        ];
    }
}
