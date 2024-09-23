<?php

namespace App\Http\Requests\Hotel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RejectHotel extends FormRequest
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
            "motif"=>"required|string",
        ];
    }

    public function messages() : array
    {
        return [
            'motif.required' => 'Motif de rejet requis.',
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
