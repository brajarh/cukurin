<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_outlet' => 'required|string|max:30',
            'alamat_outlet' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_outlet.required' => 'Nama cabang wajib diisi.',
            'nama_outlet.max' => 'Nama cabang maksimal 30 karakter.',
            'alamat_outlet.required' => 'Alamat cabang wajib diisi.',
            'alamat_outlet.max' => 'Alamat cabang maksimal 100 karakter.',
        ];
    }
}