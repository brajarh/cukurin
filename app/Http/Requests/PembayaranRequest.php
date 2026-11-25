<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_booking' => 'required|exists:booking,id_booking',
            'metode' => 'required|in:tunai,transfer,qris',
        ];
    }

    public function messages(): array
    {
        return [
            'id_booking.required' => 'Booking wajib dipilih.',
            'id_booking.exists' => 'Booking tidak ditemukan.',
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'metode.in' => 'Metode pembayaran tidak valid.',
        ];
    }
}