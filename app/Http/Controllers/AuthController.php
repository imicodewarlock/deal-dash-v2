<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

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
            'avatar' => $avatarUrl
        ]);

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
     * @return @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return $this->sendSuccess(__('auth.signed_out'));
    }
}
