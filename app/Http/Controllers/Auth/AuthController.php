<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\error;

class AuthController extends Controller
{
    //login
    public function login(Request $request): JsonResponse
    {
        $check = $request->only('email', 'password');
        $validator =  Validator::make($check,[
            'email' => "required|exists:users,email",
            'password' => 'required',
        ],[
            "email.required" => "Votre mail n'est pas valide",
            'password.required' => 'Votre mot de passe est obligatoire',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error"=>true,
                "message"=>"Vos informations de connection sont invalides",
                "validator_error"=>$validator->errors()->messages(),
            ],400);
//            return JsonResponse::send(true,"Vos informations de conection sont invalides",$validator->errors()->messages(),400);
        }
        $attempt = Auth::attempt($check);
        if (!$attempt) {
            return response()->json([
                "error"=>true,
                "message"=>'Vérifier vos informations de connexion',
            ],401);
        }

        $user = Auth::user();
        return response()->json([
            "error"=>false,
            "message"=>"vous êtes authentifié",
            'user'=>$user,
            'user_role'=>$user->role,
            'user_status'=>$user->statut,
        ],200);
    }
    // register Admin user for hostel
    public function registerHostelAdmin(Request $request) : JsonResponse
    {

    }
    // logout
    public function logout()
    {
        Auth::logout();
        return response()->json([
            "error"=>false,
            "message"=>"Déconnexion réussite",
        ],200);
    }
}
