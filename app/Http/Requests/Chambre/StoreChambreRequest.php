<?php

namespace App\Http\Requests\Chambre;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreChambreRequest extends FormRequest
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
            'images' => 'array',
            'images.*' => 'image|mimes:jpg,png,jpeg|max:2048',
            "num"=>"required|string",
            "description"=>"required|string",
            "hotel_id"=>"exists:hotels,id",
            "users_id"=>"exists:users,id",
            "types_chambres_id"=>"exists:types_chambres,id",
        ];
    }

    public function messages() : array
    {
        return [
            'images.required' => 'Veuillez ajouter au moins une image.',
            'images.array' => 'Les images doivent être un tableau de fichiers.',
            'images.*.image' => 'Chaque fichier doit être une image.',
            'images.*.mimes' => 'Les images doivent être au format jpg, jpeg ou png.',
            'images.*.max' => 'Chaque image doit faire moins de 2 Mo.',
            'name.required' => 'Le nom  de votre type de chambre .',
            'capacity.required' => 'La capacité de la chambre requise.',
            "user_id.exists"=>"L'utilisateur n'existe pas.",
            "hotel_id.exists"=>"L'hôtel n'existe pas.",
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
