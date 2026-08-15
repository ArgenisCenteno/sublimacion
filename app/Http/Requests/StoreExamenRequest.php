<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreExamenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            ...$this->reglasBase(),
            'codigo' => 'required|string|max:20|unique:examenes,codigo',
        ];
    }

    public function messages(): array { return $this->mensajes(); }

    private function reglasBase(): array
    {
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

            // Valores de referencia (array opcional)
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

    private function mensajes(): array
    {
        return [
            'codigo.unique'                            => 'Ya existe un examen con ese código.',
            'nombre.required'                          => 'El nombre del examen es obligatorio.',
            'categoria.required'                       => 'La categoría es obligatoria.',
            'muestra.required'                         => 'El tipo de muestra es obligatorio.',
            'tiempo_resultado.required'                => 'El tiempo de resultado es obligatorio.',
            'precio.required'                          => 'El precio es obligatorio.',
            'valores_referencia.*.sexo.required'       => 'El sexo del rango es obligatorio.',
            'valores_referencia.*.edad_min.required'   => 'La edad mínima es obligatoria.',
            'valores_referencia.*.edad_max.required'   => 'La edad máxima es obligatoria.',
            'valores_referencia.*.edad_max.gte'        => 'La edad máxima debe ser mayor o igual a la mínima.',
            'valores_referencia.*.valor_max.gte'       => 'El valor máximo debe ser mayor o igual al mínimo.',
        ];
    }
}