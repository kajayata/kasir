<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransaksiRequest extends FormRequest
{
    public const PAYMENT_METHODS = ['Tunai', 'QRIS', 'Debit', 'Kredit', 'E-Wallet', 'Transfer'];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100000'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'tax' => ['nullable', 'integer', 'min:0'],
            'other_fee' => ['nullable', 'integer', 'min:0'],
            'paid_amount' => ['required', 'integer', 'min:0'],
            'payment_method' => ['required', Rule::in(self::PAYMENT_METHODS)],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang masih kosong.',
            'items.*.id.exists' => 'Barang tidak ditemukan.',
        ];
    }
}
