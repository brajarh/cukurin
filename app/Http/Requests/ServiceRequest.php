<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_service' => 'required|string|min:3|max:100',
            'harga_service' => 'required|integer|min:0',
            'durasi' => 'required|integer|min:5|max:480',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_service.required' => 'Nama layanan wajib diisi.',
            'nama_service.min' => 'Nama layanan minimal 3 karakter.',
            'harga_service.required' => 'Harga wajib diisi.',
            'harga_service.integer' => 'Harga harus berupa angka bulat.',
            'durasi.required' => 'Durasi wajib diisi.',
            'durasi.integer' => 'Durasi harus berupa angka (menit).',
            'durasi.min' => 'Durasi minimal 5 menit.',
            'durasi.max' => 'Durasi maksimal 480 menit.',
        ];
    }
}
