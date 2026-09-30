<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Barcode kosong harus jadi null, kalau tidak bentrok unique dengan produk lain tanpa barcode.
        if ($this->filled('barcode') === false) {
            $this->merge(['barcode' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $produkId = $this->route('produk')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($produkId)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($produkId)],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama barang wajib diisi.',
            'name.max' => 'Nama barang maksimal 255 karakter.',
            'code.required' => 'Kode barang wajib diisi.',
            'code.max' => 'Kode barang maksimal 50 karakter.',
            'code.unique' => 'Kode barang sudah dipakai produk lain.',
            'barcode.max' => 'Barcode maksimal 50 karakter.',
            'barcode.unique' => 'Barcode sudah dipakai produk lain.',
            'price.required' => 'Harga wajib diisi.',
            'price.integer' => 'Harga harus berupa angka bulat.',
            'price.min' => 'Harga tidak boleh negatif.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
        ];
    }
}
