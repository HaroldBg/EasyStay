<?php

namespace App\Http\Requests\Chambre;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AvailableRoomRequest extends FormRequest
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
            'date_deb' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after:date_debut',
            'nmb_per' => 'required|integer|min:1',
        ];
    }
    public function messages() : array
    {
        return [
            'email.email' => 'Mail invalide',
//            "user_id.exists"=>"L'utilisateur n'existe pas.",
            "hotel_id.exists"=>"L'hôtel n'existe pas.",
            "chambre_id.exists"=>"La chambre est non existante",
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
