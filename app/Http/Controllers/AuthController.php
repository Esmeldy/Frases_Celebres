<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar nuevo usuario",
     *     tags={"Acceso"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Nombre de usuario",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Correo electrónico",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Contraseña",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Usuario registrado correctamente"),
     *     @OA\Response(response="401", description="Error de validación de datos")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        return response()->json(['message' => 'Usuario creado correctamente', 'user' => $user]);
    }

    /**
     *
     * @OA\Post(
     *      path="/api/login",
     *      tags={"Acceso"},
     *      summary="Login de usuarios",
     *      description="Login con datos de usuarios registrados previamente.",
     *
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Correo electrónico",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         example= "info@jose.com"
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="Contraseña",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         example="Jose1234"
     *     ),
     *      example={"email": "info@jose.com", "password": Jose1234}
     *
     *     @OA\Response(response="200", description="Usuario logueado correctamente"),
     *     @OA\Response(response="401", description="No autorizado.")
     * )
     *
     */
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'No autorizado'],401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Hola ' . $user->name,
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     *
     *   @OA\Get(
     *      path="/api/logout",
     *      tags={"Acceso"},
     *      summary="Cerrar sessión de usuario",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Response(response="200", description="Sessión cerrada correctamente", @OA\JsonContent()),
     *      @OA\Response(response="401", description="No autorizado",@OA\JsonContent())
     *   )
     *
     */
    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return [
            'message' => 'Has cerrado sesión correctamente y el token de acceso fue eliminado'
        ];
    }
}
