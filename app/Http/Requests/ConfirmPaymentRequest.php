<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'method' => ['required', 'string', 'in:zelle,pago_movil,paypal,stripe'],
            'reference_number' => ['required', 'string'],
            'receipt_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // 5MB
        ];
    }
}
