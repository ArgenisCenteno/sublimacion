<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerfilRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $perfil = $this->route('perfil');

        return [
            'nombre'         => 'required|string|max:150',
            'categoria'      => 'nullable|string|max:80',
            'precio'         => 'required|numeric|min:0',
            'activo'         => 'boolean',
            'examenes_ids'   => 'nullable|array',
            'examenes_ids.*' => 'exists:examenes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique'         => 'Ya existe otro perfil con ese código.',
            'nombre.required'       => 'El nombre del perfil es obligatorio.',
            'precio.required'       => 'El precio es obligatorio.',
            'examenes_ids.*.exists' => 'Uno de los exámenes seleccionados no existe.',
        ];
    }
}