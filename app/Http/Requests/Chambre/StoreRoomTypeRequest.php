<?php

namespace App\Http\Requests\Chambre;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRoomTypeRequest extends FormRequest
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
            "name"=>"required|string",
            "capacity"=>"required|string",
            "hotel_id"=>"exists:hotels,id",
        ];
    }

    public function messages() : array
    {
        return [
            'name.required' => 'Le nom  de votre type de chambre .',
            'capacity.required' => 'La capacité de la chambre requise.',
            "user_id.exists"=>"L'utilisateur n'existe pas.",
            "hotel_id.exists"=>"L'hôtel n'existe pas.",
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
