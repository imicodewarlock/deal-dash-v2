<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserFcmToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $request->hasHeader('FCM-Token')) {
            // Get the FCM token from the request header
            $fcmToken = $request->header('FCM-Token');

            // Check if the token is different from the one stored in the database
            if ($fcmToken && $fcmToken !== $user->fcm_token) {
                // Update the user's FCM token
                $user->fcm_token = $fcmToken;
                $user->save();
            }
        }

        return $next($request);
    }
}
