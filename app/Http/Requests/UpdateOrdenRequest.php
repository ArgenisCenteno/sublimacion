<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrdenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'medico_id'              => 'nullable|exists:medicos,id',
            'descuento'              => 'nullable|numeric|min:0',
            'observaciones'          => 'nullable|string|max:500',
            'fecha_entrega_estimada' => 'nullable|date',

            'items'                  => 'required|array|min:1',
            'items.*.examen_id'      => 'required|exists:examenes,id',
            'items.*.precio'         => 'required|numeric|min:0',
        ];
    }
}