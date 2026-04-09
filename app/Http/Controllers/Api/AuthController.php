<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Services\Support\ActivityLogger;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $payload =[
            'iss' => "FundacionUnifranz",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 3600
        ];

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        ActivityLogger::log(
            'Usuario registrado desde API',
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'guard' => 'api',
            ],
            $user,
            $user,
            'auth',
            'registered'
        );

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {

        if($request->query()){
            return response()->json([
                'error' => 'No se permiten query parameters'
             ], 400);
        }
        
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json(['error' => 'Credenciales incorrectas papu'], 401);
        }

        $payload =[
            'iss' => "FundacionUnifranz",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 3600
        ];

        $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        ActivityLogger::log(
            'Inicio de sesión API',
            [
                'guard' => 'api',
            ],
            $user,
            $user,
            'auth',
            'login'
        );

        return response()->json(['token' => $jwt]);
    }

    public function me(Request $request)
    {
        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

        $user = User::find($decoded->sub);

        return response()->json($user);
    }

    public function refresh(Request $request)
    {
        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
        $user = User::find($decoded->sub);

        $payload =[
            'iss' => "FundacionUnifranz",
            'sub' => $decoded->sub,
            'iat' => time(),
            'exp' => time() + 3600
        ];

        $newToken = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        if ($user !== null) {
            ActivityLogger::log(
                'Token API renovado',
                [
                    'guard' => 'api',
                ],
                $user,
                $user,
                'auth',
                'token_refreshed'
            );
        }

        return response()->json(['token' => $newToken]);
    }

    public function logout()
    {
        if (auth()->user() !== null) {
            ActivityLogger::log(
                'Cierre de sesión API',
                [
                    'guard' => 'api',
                ],
                auth()->user(),
                auth()->user(),
                'auth',
                'logout'
            );
        }

        return response()->json(['message' => 'Cerraste sesión']);
    }
}
