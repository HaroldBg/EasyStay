<?php

namespace App\Http\Requests\Hotel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DemandeHotel extends FormRequest
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
            "motif"=>"string",
            "nom"=>"required|string",
            "email"=>"required|email",
            "adresse"=>"required|string",
            "user_id"=>"exists:users,id"
        ];
    }

    public function messages() : array
    {
        return [
            'nom.required' => 'Le nom  de votre Hotel est requis.',
            'adresse.required' => 'Votre adresse est requise.',
            'email.required' => 'Votre mail est requis.',
            'email.unique' => "Le mail existe déjà .",
            'email.email' => 'Mail invalide',
            "user_id.exists"=>"L'utilisateur n'existe pas."
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        // Throwing a custom HttpResponseException to return JSON or redirect response
        throw new HttpResponseException(
            response()->json([
                'error' => true,
                'message' => 'Validation échoué',
                'errors' => $errors,
            ], 422)
        );
    }
}
