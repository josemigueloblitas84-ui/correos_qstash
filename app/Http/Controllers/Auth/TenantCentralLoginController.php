<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantCentralLoginController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $tokenHash = hash('sha256', $token);
        $currentTenantId = tenant('id');

        $loginToken = tenancy()->central(function () use ($tokenHash, $currentTenantId) {
            return DB::table('tenant_login_tokens')
                ->where('token_hash', $tokenHash)
                ->where('tenant_id', $currentTenantId)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->first();
        });

        if (! $loginToken) {
            return redirect('/login')->with('error', 'El enlace de acceso al tenant expiro o no es valido.');
        }

        app(RolesAndPermissionsSeeder::class)->run();

        $user = User::query()
            ->where('email', $loginToken->central_user_email)
            ->first();

        if (! $user) {
            $user = new User();
            $user->name = $loginToken->central_user_name;
            $user->email = $loginToken->central_user_email;
            $user->password = Hash::make(Str::random(48));
            $user->email_verified_at = now();
            if (Schema::hasColumn('users', 'is_central_user')) {
                $user->is_central_user = true;
            }
            if (Schema::hasColumn('users', 'estado')) {
                $user->estado = 1;
            }
            $user->save();
        } else {
            $attributes = [
                'name' => $loginToken->central_user_name,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ];

            if (Schema::hasColumn('users', 'is_central_user')) {
                $attributes['is_central_user'] = true;
            }

            if (Schema::hasColumn('users', 'estado')) {
                $attributes['estado'] = 1;
            }

            $user->forceFill($attributes)->save();
        }

        if (! $user->hasRole('SuperAdministrador')) {
            $user->assignRole('SuperAdministrador');
        }

        tenancy()->central(function () use ($loginToken) {
            DB::table('tenant_login_tokens')
                ->where('id', $loginToken->id)
                ->update([
                    'used_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $request->session()->put([
            'central_impersonation' => true,
            'central_impersonation_name' => $loginToken->central_user_name,
            'central_impersonation_email' => $loginToken->central_user_email,
        ]);

        return redirect('/dashboard');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Sesion tenant desconectada correctamente.');
    }
}
