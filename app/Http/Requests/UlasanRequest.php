<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UlasanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_booking' => 'required|exists:booking,id_booking',
            'rating' => 'required|integer|between:1,5',
            'komentar' => 'nullable|string|max:300',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Rating wajib dipilih.',
            'rating.between' => 'Rating harus antara 1 sampai 5.',
            'komentar.max' => 'Komentar maksimal 300 karakter.',
        ];
    }
}