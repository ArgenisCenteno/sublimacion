<?php

namespace App\Http\Requests;

use App\Rules\PersonName;
use App\Rules\PhoneNumber;
use App\Rules\VenezuelanCedulaRif;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $requiresDelivery = $this->cartRequiresDelivery();

        return [
            'customer_name' => ['required', 'string', 'max:255', new PersonName()],
            'customer_phone' => ['required', 'string', 'max:30', new PhoneNumber()],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_cedula_rif' => ['nullable', 'string', new VenezuelanCedulaRif()],
            'delivery_type' => [$requiresDelivery ? 'required' : 'nullable', 'in:retiro_tienda,envio_local,envio_nacional'],
            'shipping_address' => ['nullable', 'required_if:delivery_type,envio_local,envio_nacional', 'string', 'max:500'],
            'shipping_city' => ['nullable', 'required_if:delivery_type,envio_nacional', 'string', 'max:255'],
            'shipping_carrier' => ['nullable', 'required_if:delivery_type,envio_nacional', 'string', 'max:255'],
            'payment_method' => ['required', 'in:pago_movil,transferencia,efectivo,paypal'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function cartRequiresDelivery(): bool
    {
        $cart = $this->user()->cart;

        if (! $cart) {
            return false;
        }

        return $cart->items()
            ->whereHas('product', fn ($q) => $q->where('type', 'producto'))
            ->exists();
    }
}
