<?php

namespace App\Http\Requests;

use App\Rules\PersonName;
use App\Rules\PhoneNumber;
use App\Rules\VenezuelanCedulaRif;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new PersonName()],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => ['nullable', 'string', 'max:30', new PhoneNumber()],
            'cedula_rif' => ['nullable', 'string', 'max:20', new VenezuelanCedulaRif()],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'active' => 'boolean',
        ];
    }
}
