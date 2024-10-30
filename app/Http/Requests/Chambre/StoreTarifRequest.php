<?php

namespace App\Http\Requests\Chambre;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTarifRequest extends FormRequest
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
            "prix"=>"required|string",
            "saison"=>"required|string",
            "date_deb"=>"required|date",
            "date_fin"=>"required|date",
            "users_id"=>"exists:users,id",
            "types_chambres_id"=>"exists:types_chambres,id",
        ];
    }

    public function messages() : array
    {
        return [
            'prix.required' => 'Le prix est requis .',
            'saison.required' => 'La saison est requise .',
            'date_deb.required' => 'La date de début de la saison est requise.',
            'date_fin.required' => 'La date de fin de la saison est requise.',
            "user_id.exists"=>"L'utilisateur n'existe pas.",
            "types_chambres_id.exists"=>"Type de chambre nn existante",
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
