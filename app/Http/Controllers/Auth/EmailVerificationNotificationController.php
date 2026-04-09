<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\Support\ActivityLogger;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $request->user()->sendEmailVerificationNotification();

        ActivityLogger::log(
            'Enlace de verificacion reenviado',
            [],
            $request->user(),
            $request->user(),
            'auth',
            'verification_link_sent'
        );

        return back()->with('status', 'verification-link-sent');
    }
}
