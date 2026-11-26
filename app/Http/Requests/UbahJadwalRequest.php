<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UbahJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        // akses dibatasi oleh middleware role:pelanggan di route; kepemilikan booking dicek di controller
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date_format:Y-m-d',
            'jam_mulai' => 'required|date_format:H:i',
            'alasan' => 'required|string|min:5|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal baru wajib dipilih.',
            'tanggal.date_format' => 'Format tanggal baru tidak valid.',
            'jam_mulai.required' => 'Jam baru wajib dipilih.',
            'jam_mulai.date_format' => 'Format jam baru tidak valid.',
            'alasan.required' => 'Alasan perubahan jadwal wajib diisi.',
            'alasan.min' => 'Alasan minimal 5 karakter.',
            'alasan.max' => 'Alasan maksimal 255 karakter.',
        ];
    }
}
