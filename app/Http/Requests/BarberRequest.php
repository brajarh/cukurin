<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BarberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_barber' => 'required|string|min:3|max:100',
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^[0-9+]{8,20}$/'],
            'id_outlet' => 'required|exists:outlet,id_outlet',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:service,id_service',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_barber.required' => 'Nama barber wajib diisi.',
            'nama_barber.min' => 'Nama barber minimal 3 karakter.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP hanya boleh angka (boleh diawali +), 8-20 digit.',
            'id_outlet.required' => 'Cabang wajib dipilih.',
            'id_outlet.exists' => 'Cabang yang dipilih tidak ada.',
            'service_ids.*.exists' => 'Ada layanan yang dipilih tidak valid.',
        ];
    }
}
