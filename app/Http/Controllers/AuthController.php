<?php

namespace App\Http\Controllers;

use App\Events\RegisteredUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use App\Notifications\VerifyEmailNotification;

class AuthController extends BaseController
{
    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|max:255|email|unique:users',
            'phone'     => 'required|numeric|unique:users',
            'password'  => 'required|string|min:6|confirmed',
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('validation.failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
        }

        $avatarUrl = null;

        if ($request->hasFile('avatar')) {
            $avatarUrl = $this->getUploadedFileUrl($request->file('avatar'), 'img/avatars');

            if (!$avatarUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'avatar' => $avatarUrl,
            'verification_token' => Str::random(60),
        ]);

        // Fire the RegisteredUser event
        event(new RegisteredUser($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendSuccess(__('auth.signed_up'), compact('user','token'), Response::HTTP_CREATED);
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return $this->sendError(__('auth.invalid_verification_token'), Response::HTTP_BAD_REQUEST);
        }

        // Mark user as verified
        $user->email_verified_at = now();
        $user->verification_token = null;  // Invalidate the token
        $user->save();

        return $this->sendSuccess(__('auth.email_verified'));
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('validation.failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified != null) {
            return $this->sendError(__('auth.email_already_verified'), Response::HTTP_BAD_REQUEST);
        }

        $user->verification_token = Str::random(60); // Regenerate the token
        $user->save();

        // $user->notify(new VerifyEmailNotification($user->verification_token));
        event(new RegisteredUser($user));

        return $this->sendSuccess(__('auth.email_verification_sent'));
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'email'     => 'required|string|email|max:255',
            'password'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('validation.failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
        }

        $user = User::withTrashed()->where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError(__('auth.failed'), Response::HTTP_BAD_REQUEST);
        }

        if ($user->trashed()) {
            return $this->sendError(__('auth.soft_deleted'), Response::HTTP_BAD_REQUEST);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->sendError(__('auth.password'), Response::HTTP_BAD_REQUEST);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendSuccess(__('auth.signed_in'), compact('user','token'));
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return $this->sendSuccess(__('user.found'), $request->user());
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password'  => 'required',
            'new_password'      => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('validation.failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
        }

        // Check if the provided current password matches the authenticated user's password
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError(__('auth.password_not_match'), Response::HTTP_BAD_REQUEST);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return $this->sendSuccess(__('auth.password_updated'));
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('validation.failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->sendSuccess(__($status))
            : $this->sendError(__($status), Response::HTTP_BAD_REQUEST);
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token'     => 'required',
            'email'     => 'required|string|email|max:255',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('validation.failed'), Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->sendSuccess(__($status))
            : $this->sendError(__($status), Response::HTTP_BAD_REQUEST);
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return $this->sendSuccess(__('auth.signed_out'));
    }
}
