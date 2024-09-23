<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRoles;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminRequest;
use App\Http\Requests\Auth\HostelAdminRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Mail\AccountValidationMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use function Laravel\Prompts\error;

class AuthController extends Controller
{
    //login
    public function login(LoginRequest $request): JsonResponse
    {
        $check = $request->only('email', 'password');
        $validatedData = $request->validated();
        $attempt = Auth::attempt($check);
        if (!$attempt) {
            return response()->json([
                "error"=>true,
                "message"=>'Vérifier vos informations de connexion',
            ],401);
        }

        $user = Auth::user();
        $token = $user->createToken($request->getClientIp())->plainTextToken;
        return response()->json([
            "error"=>false,
            "message"=>"vous êtes authentifié",
            'user'=>$user,
            "token_type"=>"Bearer",
            "token"=>$token,
        ],200);
    }
    // register Admin user for hostel
    public function registerAdmin(StoreUserRequest $request) : JsonResponse
    {
        $validatedData = $request->validated();
        // store images
        $picture = $request->file('picture');
        $fileName = "Avatar_".$request->nom ."_". $request->prenom ."_". time().".".$picture->getClientOriginalExtension();
        $filePath = $picture->storeAs('images/Avatar',$fileName,'public');
        $user = User::query()->create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'tel' => $request->tel,
            'picture' => $filePath,
            'password' => Hash::make($request->password),
            'role' => UserRoles::ADMIN->value,
            'status'=>UserStatus::EMAIL_CONFIRMATION_PENDING->value,
        ]);
//        Mail::to($user['email'])->send(new AccountValidationMail($user));
        return response()->json([
            "error"=>false,
            "message"=>"votre compte a été créé avec succès.",
            'user'=>$user
        ],200);
    }
    // logout
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            "error"=>false,
            "message"=>"Déconnexion réussite",
        ],200);
    }
}
