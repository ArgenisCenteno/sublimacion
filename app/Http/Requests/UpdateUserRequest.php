<?php

namespace App\Http\Requests;

use App\Rules\PersonName;
use App\Rules\PhoneNumber;
use App\Rules\VenezuelanCedulaRif;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255', new PersonName()],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => ['nullable', 'string', 'max:30', new PhoneNumber()],
            'cedula_rif' => ['nullable', 'string', 'max:20', new VenezuelanCedulaRif()],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'active' => 'boolean',
        ];
    }
}
