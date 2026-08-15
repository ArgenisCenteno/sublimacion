<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $examen = $this->route('examen');

        return [
            'nombre'            => 'required|string|max:150',
            'nombre_corto'      => 'nullable|string|max:20',
            'categoria'         => 'required|string|max:80',
            'muestra'           => 'required|string|max:80',
            'tubo'              => 'nullable|string|max:50',
            'tiempo_resultado'  => 'required|integer|min:1|max:720',
            'unidad'            => 'nullable|string|max:30',
            'metodo'            => 'nullable|string|max:100',
            'precio'            => 'required|numeric|min:0',
            'activo'            => 'boolean',
            'instrucciones'     => 'nullable|string|max:500',
            'valores_referencia'                => 'nullable|array',
            'valores_referencia.*.sexo'         => 'required|in:M,F,ambos',
            'valores_referencia.*.edad_min'     => 'required|integer|min:0',
            'valores_referencia.*.edad_max'     => 'required|integer|min:0|gte:valores_referencia.*.edad_min',
            'valores_referencia.*.valor_min'    => 'nullable|numeric',
            'valores_referencia.*.valor_max'    => 'nullable|numeric|gte:valores_referencia.*.valor_min',
            'valores_referencia.*.valor_texto'  => 'nullable|string|max:100',
            'valores_referencia.*.etiqueta'     => 'nullable|string|max:50',
        ];
    }
}