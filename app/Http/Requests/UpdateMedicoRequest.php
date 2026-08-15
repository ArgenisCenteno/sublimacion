<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'       => 'required|string|max:100',
            'apellido'     => 'required|string|max:100',
            'especialidad' => 'nullable|string|max:100',
            'telefono'     => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:100',
            'consultorio'  => 'nullable|string|max:150',
            'activo'       => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre del médico es obligatorio.',
            'apellido.required' => 'El apellido del médico es obligatorio.',
        ];
    }
}