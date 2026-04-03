<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkReviewPaymentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'payment_ids' => ['required', 'array', 'min:1', 'max:50'],
            'payment_ids.*' => ['integer', 'exists:payments,id'],
            'status' => ['required', 'string', 'in:approved,rejected'],
        ];
    }
}
