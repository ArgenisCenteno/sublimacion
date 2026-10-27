<?php

namespace App\Http\Requests;

use App\Rules\PersonName;
use App\Rules\PhoneNumber;
use App\Rules\VenezuelanCedulaRif;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new PersonName()],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
            'phone' => ['nullable', 'string', 'max:30', new PhoneNumber()],
            'cedula_rif' => ['nullable', 'string', 'max:20', new VenezuelanCedulaRif()],
        ];
    }
}
