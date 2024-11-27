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
use App\Models\Admin;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use function Laravel\Prompts\error;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connexion Utilisateur",
     *     description="Connexion d'un utilisateur et retour d'un token",
     *     operationId="login",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Adresse email de l'utilisateur",
     *         @OA\Schema(type="string", format="email", example="user@example.com")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         description="Mot de passe de l'utilisateur",
     *         @OA\Schema(type="string", format="password", example="password123")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Identifiants de connexion (facultatif si fournis en paramètres)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="user@example.com",
     *                 description="Adresse email de l'utilisateur"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 example="password123",
     *                 description="Mot de passe de l'utilisateur"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false, description="Indique s'il y a une erreur"),
     *             @OA\Property(property="message", type="string", example="Vous êtes authentifié", description="Message d'authentification"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Sudo"),
     *                 @OA\Property(property="prenom", type="string", example="Admin"),
     *                 @OA\Property(property="email", type="string", example="sudo@admin.hotel"),
     *                 @OA\Property(property="adresse", type="string", nullable=true, example=null),
     *                 @OA\Property(property="tel", type="string", example="+229 91461545"),
     *                 @OA\Property(property="picture", type="string", example="images/blank_profile.jpeg"),
     *                 @OA\Property(property="role", type="string", example="Sudo"),
     *                 @OA\Property(property="status", type="string", example="Enable"),
     *                 @OA\Property(property="hotels_id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="email_verified_at", type="string", nullable=true, example=null),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z")
     *             ),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="token", type="string", example="5|2YaDKuz5UPdqAR2RoVOb9sxbz92a9b5JJGvGzgGs29e54f58")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé - Identifiants invalides"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation - Champs requis manquants"
     *     )
     * )
     */

    //login
    public function login(LoginRequest $request): JsonResponse
    {
        $check = $request->only('email', 'password');
        $validatedData = $request->validated();
        $attempt = Auth::attempt($check);
        if (!$attempt) {
            return response()->json([
                "error"=>true,
                "message"=>'Email ou mot de passe incorrect!',
            ],401);
        }

        $user = Auth::user();
        $token = $user->createToken($request->getClientIp())->plainTextToken;
        //let check user's role
        if ($user->role->value == UserRoles::ADMIN->value || $user->role->value == UserRoles::FRONTDESKAGENT->value){
            $hotel = Hotel::query()->find($user->hotels_id);
            return response()->json([
                "error"=>false,
                "message"=>"vous êtes authentifié",
                'user'=>$user,
                "token_type"=>"Bearer",
                "token"=>$token,
                "hotel"=>$hotel,
            ],200);
        }
        return response()->json([
            "error"=>false,
            "message"=>"vous êtes authentifié",
            'user'=>$user,
            "token_type"=>"Bearer",
            "token"=>$token,
        ],200);
    }
    /**
     * @OA\Post(
     *     path="/api/auth/registerUser",
     *     summary="Enregistrement d'un nouvel utilisateur",
     *     description="Crée un nouveau compte utilisateur et renvoie les détails de l'utilisateur",
     *     operationId="registerUser",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="nom",
     *         in="query",
     *         required=true,
     *         description="Nom de famille de l'utilisateur",
     *         @OA\Schema(type="string", example="Doe")
     *     ),
     *     @OA\Parameter(
     *         name="prenom",
     *         in="query",
     *         required=true,
     *         description="Prénom de l'utilisateur",
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Adresse email de l'utilisateur",
     *         @OA\Schema(type="string", format="email", example="john.doe@example.com")
     *     ),
     *     @OA\Parameter(
     *         name="tel",
     *         in="query",
     *         required=true,
     *         description="Numéro de téléphone de l'utilisateur",
     *         @OA\Schema(type="string", example="+33 612345678")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         description="Mot de passe de l'utilisateur",
     *         @OA\Schema(type="string", format="password", example="SecurePass123")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur enregistré avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false, description="Indique s'il y a une erreur"),
     *             @OA\Property(property="message", type="string", example="Votre compte a été créé avec succès.", description="Message de succès"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Doe"),
     *                 @OA\Property(property="prenom", type="string", example="John"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="adresse", type="string", nullable=true, example="45 Rue de Paris"),
     *                 @OA\Property(property="tel", type="string", example="+33 612345678"),
     *                 @OA\Property(property="role", type="string", example="User"),
     *                 @OA\Property(property="status", type="string", example="ACTIVE"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-15T01:08:30.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-15T01:08:30.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête incorrecte - Données non valides"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation - Champs requis manquants"
     *     )
     * )
     */

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

    /**
     * @OA\Post(
     *     path="/api/auth/storeClient",
     *     summary="Enregistrement client",
     *     description="Crée un nouveau compte utilisateur client et renvoie les détails de l'utilisateur",
     *     operationId="storeClient",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="nom",
     *         in="query",
     *         required=true,
     *         description="Nom de famille du client",
     *         @OA\Schema(type="string", example="Sudo")
     *     ),
     *     @OA\Parameter(
     *         name="prenom",
     *         in="query",
     *         required=true,
     *         description="Prénom du client",
     *         @OA\Schema(type="string", example="Admin")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Adresse email du client",
     *         @OA\Schema(type="string", format="email", example="sudo@client.hotel")
     *     ),
     *     @OA\Parameter(
     *         name="adresse",
     *         in="query",
     *         required=false,
     *         description="Adresse du client",
     *         @OA\Schema(type="string", nullable=true, example="123 Main St")
     *     ),
     *     @OA\Parameter(
     *         name="tel",
     *         in="query",
     *         required=true,
     *         description="Numéro de téléphone du client",
     *         @OA\Schema(type="string", example="+229 91461545")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         description="Mot de passe du client",
     *         @OA\Schema(type="string", format="password", example="password123")
     *     ),
     *     @OA\Parameter(
     *         name="picture",
     *         in="query",
     *         required=false,
     *         description="Image de profil du client",
     *         @OA\Schema(type="string", format="binary", example="images/profile.jpg")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client account successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false, description="Indicates if there was an error"),
     *             @OA\Property(property="message", type="string", example="Votre compte a été créé avec succès.", description="Message de succès"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="John"),
     *                 @OA\Property(property="prenom", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@gmail.com"),
     *                 @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St"),
     *                 @OA\Property(property="tel", type="string", example="+229 91461545"),
     *                 @OA\Property(property="picture", type="string", example="images/Avatar/Avatar_Sudo_Admin_1633021012.jpeg"),
     *                 @OA\Property(property="role", type="string", example="Client"),
     *                 @OA\Property(property="status", type="string", example="EMAIL_CONFIRMATION_PENDING"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête incorrecte - Données non valides"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation - Champs requis manquants"
     *     )
     * )
     */

    public function storeClient(StoreUserRequest $request) : JsonResponse
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
            'role' => UserRoles::CLIENT->value,
            'status'=>UserStatus::EMAIL_CONFIRMATION_PENDING->value,
        ]);
//        Mail::to($user['email'])->send(new AccountValidationMail($user));
        return response()->json([
            "error"=>false,
            "message"=>"votre compte a été créé avec succès.",
            'user'=>$user
        ],200);
    }
    // store Front Desk Agent
    /**
     * @OA\Post(
     *     path="/api/auth/storeFDA",
     *     summary="Register a new front desk agent",
     *     description="Creates a new front desk agent account. Only admin can perform this action.",
     *     operationId="storeFrontDeskAgent",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="nom",
     *         in="query",
     *         required=true,
     *         description="Last name of the front desk agent",
     *         @OA\Schema(type="string", example="Sudo")
     *     ),
     *     @OA\Parameter(
     *         name="prenom",
     *         in="query",
     *         required=true,
     *         description="First name of the front desk agent",
     *         @OA\Schema(type="string", example="Admin")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Email address of the front desk agent",
     *         @OA\Schema(type="string", format="email", example="sudo@frontdesk.hotel")
     *     ),
     *     @OA\Parameter(
     *         name="adresse",
     *         in="query",
     *         required=false,
     *         description="Address of the front desk agent",
     *         @OA\Schema(type="string", nullable=true, example="123 Main St")
     *     ),
     *     @OA\Parameter(
     *         name="tel",
     *         in="query",
     *         required=true,
     *         description="Phone number of the front desk agent",
     *         @OA\Schema(type="string", example="+229 91461545")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         required=true,
     *         description="Password for the front desk agent",
     *         @OA\Schema(type="string", format="password", example="password123")
     *     ),
     *     @OA\Parameter(
     *         name="picture",
     *         in="query",
     *         required=false,
     *         description="Profile picture of the front desk agent",
     *         @OA\Schema(type="string", format="binary", example="images/profile.jpg")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Front desk agent account successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false, description="Indicates if there was an error"),
     *             @OA\Property(property="message", type="string", example="Votre compte a été créé avec succès.", description="Success message"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="nom", type="string", example="Sudo"),
     *                 @OA\Property(property="prenom", type="string", example="Admin"),
     *                 @OA\Property(property="email", type="string", example="sudo@frontdesk.hotel"),
     *                 @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St"),
     *                 @OA\Property(property="tel", type="string", example="+229 91461545"),
     *                 @OA\Property(property="picture", type="string", example="images/Avatar/Avatar_Sudo_Admin_1633021012.jpeg"),
     *                 @OA\Property(property="role", type="string", example="Front Desk Agent"),
     *                 @OA\Property(property="status", type="string", example="EMAIL_CONFIRMATION_PENDING"),
     *                 @OA\Property(property="hotels_id", type="integer", example=1, description="ID of the hotel the agent belongs to"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Only admin can create a front desk agent"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Invalid input"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Missing required fields"
     *     )
     * )
     */

    public function storeFrontDeskAgent(StoreUserRequest $request) : JsonResponse
    {
        $user = Auth::user();
        abort_if($user->role !== UserRoles::ADMIN, 403,"Accès Refusé" );
//        abort_if($user->statsus === UserStatus::EMAIL_CONFIRMATION_PENDING,400,"Utilisateur non vérifié");
        $validatedData = $request->validated();
        // store images
        $picture = $request->file('picture');
        $fileName = "Avatar_".$request->nom ."_". $request->prenom ."_". time().".".$picture->getClientOriginalExtension();
        $filePath = $picture->storeAs('images/Avatar',$fileName,'public');
//        dd($user->hotels_id);
        $user = User::query()->create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'adresse' => $request->adresse,
            'tel' => $request->tel,
            'picture' => $filePath,
            'password' => Hash::make($request->password),
            'role' => UserRoles::FRONTDESKAGENT->value,
            'status'=>UserStatus::EMAIL_CONFIRMATION_PENDING->value,
            "hotels_id"=>$user->hotels_id,
        ]);
//        Mail::to($user['email'])->send(new AccountValidationMail($user));
        return response()->json([
            "error"=>false,
            "message"=>"Votre compte a été créé avec succès.",
            'user'=>$user
        ],200);
    }
    // logout
    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout the authenticated user",
     *     description="Deletes the current access token and logs out the user.",
     *     operationId="logout",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false, description="Indicates if there was an error"),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussite", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not authenticated"
     *     )
     * )
     */
    public function logout()
    {
        $user = Auth::user();
        if ($user->role == UserRoles::ADMIN->value || $user->role == UserRoles::CLIENT->value){
            Session::forget('hotel');
        }
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            "error"=>false,
            "message"=>"Déconnexion réussite",
        ],200);
    }
}
