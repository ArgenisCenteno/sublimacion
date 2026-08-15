<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'paciente_id'             => 'required|exists:pacientes,id',
            'medico_id'               => 'nullable|exists:medicos,id',
            'descuento'               => 'nullable|numeric|min:0',
            'observaciones'           => 'nullable|string|max:500',
            'fecha_entrega_estimada'  => 'nullable|date',

            'items'                   => 'required|array|min:1',
            'items.*.examen_id'       => 'required|exists:examenes,id',
            'items.*.precio'          => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'paciente_id.required'    => 'El paciente es obligatorio.',
            'paciente_id.exists'      => 'El paciente seleccionado no existe.',
            'items.required'          => 'La orden debe tener al menos un examen.',
            'items.min'               => 'La orden debe tener al menos un examen.',
            'items.*.examen_id.exists'=> 'Uno de los exámenes seleccionados no existe.',
        ];
    }
}