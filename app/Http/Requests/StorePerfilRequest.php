<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerfilRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            ...$this->reglasBase(),
            'codigo' => 'required|string|max:20|unique:perfiles,codigo',
        ];
    }

    public function messages(): array { return $this->mensajes(); }

    private function reglasBase(): array
    {
        return [
            'nombre'      => 'required|string|max:150',
            'categoria'   => 'nullable|string|max:80',
            'precio'      => 'required|numeric|min:0',
            'activo'      => 'boolean',
            'examenes_ids'   => 'nullable|array',
            'examenes_ids.*' => 'exists:examenes,id',
        ];
    }

    private function mensajes(): array
    {
        return [
            'codigo.unique'           => 'Ya existe un perfil con ese código.',
            'nombre.required'         => 'El nombre del perfil es obligatorio.',
            'precio.required'         => 'El precio es obligatorio.',
            'examenes_ids.*.exists'   => 'Uno de los exámenes seleccionados no existe.',
        ];
    }
}