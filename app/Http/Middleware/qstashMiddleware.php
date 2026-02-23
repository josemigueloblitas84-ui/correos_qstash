<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class qstashMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $signature = $request->header('Upstash-Signature');
        
        //\Log::info('Signature: ' . $signature);

        if (!$signature) {
            return response()->json([
                'error' => 'No autorizado'
            ], 401);
        }

        $decodificado = null;

        try{
            $decodificado = JWT::decode($signature, new Key(env('QSTASH_CURRENT_SIGNING_KEY'), 'HS256'));
        } catch(\Exception $e){
            $decodificado = JWT::decode($signature, new Key(env('QSTASH_NEXT_SIGNING_KEY'), 'HS256'));
        }

        if(!$decodificado){
            return response()->json([
                'error' => 'JWT inválido'
            ], 401);
        }
        if (!isset($decodificado->iss) || $decodificado->iss !== "Upstash") {
            return response()->json([
                'error' => 'Issuer inválido'
            ], 401);
        }

        if (!isset($decodificado->jti)) {
            return response()->json([
                'error' => 'JWT inválido - sin jti'
            ], 401);
        }

        $jti = $decodificado->jti;

        if (cache()->has('qstash_jti_' . $jti)) {
            return response()->json([
                'error' => 'Mensaje ya procesado'
            ], 401);
        }
        
        cache()->put('qstash_jti_' . $jti, true, 10);

        Log::info('Verificacion: ' . json_encode($decodificado));
        Log::info('jwt del JWT: ' . $decodificado->jti);
        Log::info('URL generada: ' . url('/api/qstash/enviar-email'));
        Log::info('Guardando JTI: ' . $jti);
        Log::info('Middleware QStash ejecutado:' . $request);
        return $next($request);
    }
}
