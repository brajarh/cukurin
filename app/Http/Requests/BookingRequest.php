<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    protected $redirectRoute = 'booking.create';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_outlet' => 'required|exists:outlet,id_outlet',
            'id_barber' => 'required|exists:barber,id_barber',

            'id_service' => 'required|array|min:1',
            'id_service.*' => 'required|integer|distinct|exists:service,id_service',

            'tanggal' => 'required|date_format:Y-m-d',
            'jam_mulai' => 'required|date_format:H:i',
            'nama' => 'required|string|min:3|max:100',
            'no_hp' => [
                'required',
                'string',
                'regex:/^[0-9+]{8,20}$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_outlet.required' => 'Cabang wajib dipilih.',
            'id_barber.required' => 'Barber wajib dipilih.',
            'id_service.required' => 'Layanan wajib dipilih.',
            'id_service.min' => 'Pilih minimal satu layanan.',
            'id_service.*.distinct' => 'Layanan tidak boleh dipilih dua kali.',
            'id_service.*.exists' => 'Layanan tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib dipilih.',
            'jam_mulai.required' => 'Jam wajib dipilih.',
            'nama.required' => 'Nama wajib diisi.',
            'nama.min' => 'Nama minimal 3 karakter.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP tidak valid.',
        ];
    }
}