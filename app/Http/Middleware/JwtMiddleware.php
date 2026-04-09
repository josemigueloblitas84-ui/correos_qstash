<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       $token = $request->bearerToken();

       if(!$token){
        return response()->json(['error'=>'Token requerido'], 401);
       }

       try {
        $decoded = JWT::decode($token,new Key(env('JWT_SECRET'), 'HS256'));

        $user = User::find($decoded->sub);
        if(!$user){
            return response()->json(['error'=>'Usuario no encontrado'], 404);
        }

        Auth::login($user);

       }catch(ExpiredException $e){
        return response()->json(['message'=>'Token expirado!'], 401);
       }catch (Exception $e){
        return response()->json(['error'=>'Token invalido'], 401);
       }

       return $next($request);
    }
}
