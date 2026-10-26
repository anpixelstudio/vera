<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

/**
 * Email links are opened while logged out. Fortify's default verify route
 * requires auth first, so guests only saw the login page. This endpoint
 * accepts a signed URL, marks the email verified, signs the user in, and
 * shows a success toast.
 */
class SignedEmailVerificationController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            abort(403, 'Invalid email verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        Auth::guard(config('fortify.guard', 'web'))->login($user, remember: true);
        $request->session()->regenerate();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Email confirmed. Your account is ready.',
        ]);

        return redirect()->intended(
            Fortify::redirects('email-verification').'?verified=1',
        );
    }
}
