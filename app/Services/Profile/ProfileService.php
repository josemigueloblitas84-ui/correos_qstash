<?php

namespace App\Services\Profile;

use App\Models\User;
use App\Services\Support\ActivityLogger;

class ProfileService
{
    public function updateProfile(User $user, array $data): User
    {
        $before = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        ActivityLogger::log(
            'Perfil actualizado',
            [
                'old' => $before,
                'attributes' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
            $user,
            logName: 'perfil',
            event: 'updated'
        );

        return $user;
    }

    public function deleteAccount(User $user): void
    {
        $properties = [
            'old' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        $causer = $user;

        $user->delete();

        ActivityLogger::log(
            'Cuenta eliminada',
            $properties,
            $user,
            $causer,
            'perfil',
            'deleted'
        );
    }
}
