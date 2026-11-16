<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BarberScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_barber' => 'required|exists:barber,id_barber',
            'hari' => [
                'required', 'integer', 'between:1,7',
                // satu barber hanya boleh punya satu jadwal per hari
                Rule::unique('barber_schedule', 'hari')
                    ->where(fn ($q) => $q->where('id_barber', $this->input('id_barber')))
                    ->ignore($this->route('id'), 'id_schedule'),
            ],
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ];
    }

    public function messages(): array
    {
        return [
            'id_barber.required' => 'Barber wajib dipilih.',
            'hari.required' => 'Hari wajib dipilih.',
            'hari.unique' => 'Barber ini sudah punya jadwal di hari tersebut. Ubah jadwal yang lama saja.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai harus HH:MM.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai harus HH:MM.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ];
    }
}
