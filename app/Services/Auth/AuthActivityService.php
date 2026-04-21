<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\Support\ActivityLogger;

class AuthActivityService
{
    public function logWebLogin(User $user): void
    {
        ActivityLogger::log(
            'Inicio de sesion web',
            [
                'attributes' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'guard' => 'web',
                ],
            ],
            $user,
            $user,
            'auth',
            'login'
        );
    }

    public function logWebLogout(User $user): void
    {
        ActivityLogger::log(
            'Cierre de sesion web',
            [
                'attributes' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'guard' => 'web',
                ],
            ],
            $user,
            $user,
            'auth',
            'logout'
        );
    }

    public function logRegistered(User $user): void
    {
        ActivityLogger::log(
            'Usuario registrado desde formulario web',
            [
                'attributes' => [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
            $user,
            $user,
            'auth',
            'registered'
        );
    }

    public function logVerificationLinkSent(User $user): void
    {
        ActivityLogger::log(
            'Enlace de verificacion reenviado',
            [
                'attributes' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ],
            ],
            $user,
            $user,
            'auth',
            'verification_link_sent'
        );
    }

    public function logPasswordReset(User $user): void
    {
        ActivityLogger::log(
            'Contrasena restablecida desde recovery',
            [
                'attributes' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ],
            ],
            $user,
            $user,
            'auth',
            'password_reset'
        );
    }

    public function logPasswordUpdated(User $user): void
    {
        ActivityLogger::log(
            'Contrasena actualizada',
            [
                'attributes' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ],
            ],
            $user,
            $user,
            'auth',
            'password_updated'
        );
    }

    public function logEmailVerified(User $user): void
    {
        ActivityLogger::log(
            'Correo electronico verificado',
            [
                'attributes' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ],
            ],
            $user,
            $user,
            'auth',
            'email_verified'
        );
    }
}
