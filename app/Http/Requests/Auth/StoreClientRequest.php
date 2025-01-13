<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom'=> 'required|string',
            "prenom"=>"required|string",
            'email'=> 'required|email|unique:users,email',
            "adresse"=>"string",
            "tel"=>"required|string",
        ];
    }
    public function messages() : array
    {
        return [
            'nom.required' => 'Votre nom est requis.',
            'prenom.required' => 'Votre prenom est requis.',
            'tel.required' => 'Votre numéro de téléphone est requis.',
            'email.required' => 'Votre mail est requis.',
            'email.exists' => "Le mail fourni n'existe pas.",
            'email.email' => 'Mail invalide',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        // Throwing a custom HttpResponseException to return JSON or redirect response
        throw new HttpResponseException(
            response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422)
        );
    }
}
