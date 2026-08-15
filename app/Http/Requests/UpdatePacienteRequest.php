<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\Paciente $paciente */
        $paciente = $this->route('paciente');

        $esMenor = $this->fecha_nacimiento
            ? Carbon::parse($this->fecha_nacimiento)->age < 18
            : $paciente->es_menor;

        return [
            'tipo_documento'   => 'required|in:V,E,P,J',
            'cedula'           => ['required', 'string', 'max:20', Rule::unique('pacientes', 'cedula')->ignore($paciente->id)],
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'sexo'             => 'required|in:M,F',
            'fecha_nacimiento' => 'required|date|before:today',
            'telefono'         => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:100',
            'direccion'        => 'nullable|string|max:255',
            'medico_referente' => 'nullable|string|max:150',

            'rep_nombres'    => $esMenor ? 'required|string|max:100' : 'nullable|string|max:100',
            'rep_apellidos'  => $esMenor ? 'required|string|max:100' : 'nullable|string|max:100',
            'rep_cedula'     => $esMenor ? 'required|string|max:20'  : 'nullable|string|max:20',
            'rep_telefono'   => $esMenor ? 'required|string|max:20'  : 'nullable|string|max:20',
            'rep_parentesco' => $esMenor ? 'required|in:madre,padre,abuelo,abuela,tutor_legal,otro' : 'nullable|in:madre,padre,abuelo,abuela,tutor_legal,otro',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required'  => 'El tipo de documento es obligatorio.',
            'tipo_documento.in'        => 'El tipo de documento debe ser V, E, P o J.',
            'cedula.required'          => 'La cédula es obligatoria.',
            'cedula.unique'            => 'Ya existe otro paciente registrado con esa cédula.',
            'nombres.required'         => 'El nombre es obligatorio.',
            'apellidos.required'       => 'El apellido es obligatorio.',
            'sexo.required'            => 'El sexo es obligatorio.',
            'fecha_nacimiento.required'=> 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before'  => 'La fecha de nacimiento debe ser anterior a hoy.',

            'rep_nombres.required'    => 'El paciente es menor de edad. El nombre del representante es obligatorio.',
            'rep_apellidos.required'  => 'El apellido del representante es obligatorio.',
            'rep_cedula.required'     => 'La cédula del representante es obligatoria.',
            'rep_telefono.required'   => 'El teléfono del representante es obligatorio.',
            'rep_parentesco.required' => 'Indica la relación del representante con el paciente.',
        ];
    }
}