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
        ];
    }
}
