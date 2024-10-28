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
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connexion Utilisateur",
     *     description="Connexion d'un utilisateur et retour d'un token ",
     *     operationId="login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Identifiants de connexion",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "password"},
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
     *    @OA\Response(
     *          response=200,
     *          description="Login successful",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="error", type="boolean", example=false, description="Indicates if there was an error"),
     *              @OA\Property(property="message", type="string", example="vous êtes authentifié", description="Authentication message"),
     *              @OA\Property(
     *                  property="user",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="nom", type="string", example="Sudo"),
     *                  @OA\Property(property="prenom", type="string", example="Admin"),
     *                  @OA\Property(property="email", type="string", example="sudo@admin.hotel"),
     *                  @OA\Property(property="adresse", type="string", nullable=true, example=null),
     *                  @OA\Property(property="tel", type="string", example="+229 91461545"),
     *                  @OA\Property(property="picture", type="string", example="images/blank_profile.jpeg"),
     *                  @OA\Property(property="role", type="string", example="Sudo"),
     *                  @OA\Property(property="status", type="string", example="Enable"),
     *                  @OA\Property(property="hotels_id", type="integer", nullable=true, example=null),
     *                  @OA\Property(property="email_verified_at", type="string", nullable=true, example=null),
     *                  @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z")
     *              ),
     *              @OA\Property(property="token_type", type="string", example="Bearer"),
     *              @OA\Property(property="token", type="string", example="5|2YaDKuz5UPdqAR2RoVOb9sxbz92a9b5JJGvGzgGs29e54f58")
     *          )
     *      ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Missing required fields"
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
    /**
     * @OA\Post(
     *     path="/api/auth/storeAdmin",
     *     summary="Enregistrement d'un nouvel Administrateur",
     *     description="Crée un nouveau compte utilisateur administrateur et renvoie les détails de l'utilisateur",
     *     operationId="storeAdmin",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données d'enregistrement de l'administrateur",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nom", "prenom", "email", "password", "tel"},
     *             @OA\Property(property="nom", type="string", example="Sudo", description="User's last name"),
     *             @OA\Property(property="prenom", type="string", example="Admin", description="User's first name"),
     *             @OA\Property(property="email", type="string", format="email", example="sudo@admin.hotel", description="User's email address"),
     *             @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St", description="User's address"),
     *             @OA\Property(property="tel", type="string", example="+229 91461545", description="User's phone number"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="User's password"),
     *             @OA\Property(property="picture", type="string", format="binary", description="User's profile picture")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin account successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false, description="Indicates if there was an error"),
     *             @OA\Property(property="message", type="string", example="votre compte a été créé avec succès.", description="Success message"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Sudo"),
     *                 @OA\Property(property="prenom", type="string", example="Admin"),
     *                 @OA\Property(property="email", type="string", example="sudo@admin.hotel"),
     *                 @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St"),
     *                 @OA\Property(property="tel", type="string", example="+229 91461545"),
     *                 @OA\Property(property="picture", type="string", example="images/Avatar/Avatar_Sudo_Admin_1633021012.jpeg"),
     *                 @OA\Property(property="role", type="string", example="Admin"),
     *                 @OA\Property(property="status", type="string", example="EMAIL_CONFIRMATION_PENDING"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-10-25T01:08:30.000000Z")
     *             )
     *         )
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
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de création client",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nom", "prenom", "email", "password", "tel"},
     *             @OA\Property(property="nom", type="string", example="Sudo", description="User's last name"),
     *             @OA\Property(property="prenom", type="string", example="Admin", description="User's first name"),
     *             @OA\Property(property="email", type="string", format="email", example="sudo@client.hotel", description="User's email address"),
     *             @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St", description="User's address"),
     *             @OA\Property(property="tel", type="string", example="+229 91461545", description="User's phone number"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="User's password"),
     *             @OA\Property(property="picture", type="string", format="binary", description="User's profile picture")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client account successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="boolean", example=false, description="Indicates if there was an error"),
     *             @OA\Property(property="message", type="string", example="votre compte a été créé avec succès.", description="Success message"),
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
     *         description="Bad request - Invalid input"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Missing required fields"
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
     *     tags={"Front Desk"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Front desk agent registration data",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nom", "prenom", "email", "password", "tel"},
     *             @OA\Property(property="nom", type="string", example="Sudo", description="Agent's last name"),
     *             @OA\Property(property="prenom", type="string", example="Admin", description="Agent's first name"),
     *             @OA\Property(property="email", type="string", format="email", example="sudo@frontdesk.hotel", description="Agent's email address"),
     *             @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St", description="Agent's address"),
     *             @OA\Property(property="tel", type="string", example="+229 91461545", description="Agent's phone number"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Agent's password"),
     *             @OA\Property(property="picture", type="string", format="binary", description="Agent's profile picture")
     *         )
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
        // get hotel id
        $user = User::find($user->id);
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
     *     security={{"bearerAuth": {}}},  // Assurez-vous que le token d'accès est requis
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
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            "error"=>false,
            "message"=>"Déconnexion réussite",
        ],200);
    }
}
