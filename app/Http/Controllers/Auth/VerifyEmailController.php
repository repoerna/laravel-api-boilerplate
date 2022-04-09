<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
    //  * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @param $id
     * @param $hash
     * @return \Illuminate\Http\Response
    //  * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $id = $request->route('id');
        $hash = $request->route('hash');

        $user = auth()->loginUsingId($id);

        if (!$user) {
            return response()->json([
                'message' => 'user not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if (!($this->isHashValid($user, $hash))) {
            return response()->json([
                'message' => 'invalid verification data'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($request->user()->hasVerifiedEmail()) {

            return response()->json([
                'message' => 'user email already verified'
            ], Response::HTTP_OK);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json([
            'message' => 'user email successfully verified'
        ], Response::HTTP_CREATED);
    }

    protected function isHashValid($user, $hash)
    {
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return false;
        }

        return true;
    }
}
