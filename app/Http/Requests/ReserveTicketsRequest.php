<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReserveTicketsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'raffle_id' => ['required', 'exists:raffles,id'],
            'ticket_numbers' => ['required', 'array', 'min:1'],
            'ticket_numbers.*' => ['integer'],
            'coupon_code' => ['nullable', 'string', 'exists:coupons,code'],
        ];
    }
}
