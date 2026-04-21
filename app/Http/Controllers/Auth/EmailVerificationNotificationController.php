<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\Auth\AuthActivityService;

class EmailVerificationNotificationController extends Controller
{
    public function __construct(
        protected AuthActivityService $authActivityService
    ) {
    }
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $request->user()->sendEmailVerificationNotification();

        $this->authActivityService->logVerificationLinkSent($request->user());

        return back()->with('status', 'verification-link-sent');
    }
}

