<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\Support\ActivityLogger;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request): View
    {
        return view('profile.profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $before = [
            'name' => $user->name,
            'email' => $user->email,
        ];

        $user->fill($request->validated());

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

        return Redirect::route('profile.show')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $properties = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        Auth::logout();

        $user->delete();

        ActivityLogger::log(
            'Cuenta eliminada',
            $properties,
            null,
            $user,
            'perfil',
            'deleted'
        );

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
