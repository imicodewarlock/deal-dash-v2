<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    /**
     * Display a listing of users
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Set default per page value or get from request (with a fallback)
        $perPage = $request->get('per_page', 10);

        $users = User::withTrashed()->paginate($perPage);

        if ($users->isEmpty()) {
            return $this->sendError(__('user.all_records_err'));
        }
        
        return $this->sendSuccess(__('user.all_records'), $users->items(), Response::HTTP_OK,
            [
                'current_page' => $users->currentPage(),
                'total_count' => $users->total(),
                'per_page' => $users->perPage(),
                'total_pages' => $users->lastPage(),
                'has_more_pages' => $users->hasMorePages(),
            ],
            [
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
                'first_page_url' => $users->url(1),
                'last_page_url' => $users->url($users->lastPage()),
            ]
        );
    }

    /**
     * Store a newly created user
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'phone'     => 'required|unique:users|numeric',
            'password'  => 'required|string|min:6',
            'role'      => 'required|string',
            'avatar'    => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('user.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $avatarUrl = null;

        if ($request->hasFile('avatar')) {
            $avatarUrl = $this->getUploadedFileUrl($request->file('avatar'), 'img/avatars');

            if (!$avatarUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'avatar'    => $avatarUrl
        ]);

        return $this->sendSuccess(__('user.added'), $user, Response::HTTP_CREATED);
    }

    /**
     * Display a specific user
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return $this->sendError(__('user.not_found'));
        }
        
        return $this->sendSuccess(__('user.found'), $user);
    }

    /**
     * Update a specific user
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return $this->sendError(__('user.not_found'));
        }

        $validator = Validator::make($request->all(), [
            'name'      => 'string|max:255',
            'email'     => 'string|email|max:255|unique:users,email,' . $user->id,
            'phone'     => 'numeric|unique:users,phone,' . $user->id,
            'password'  => 'string|min:6',
            'role'      => 'string',
            'avatar'    => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('user.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $avatarUrl = null;

        if ($request->hasFile('avatar')) {
            // first unlink the old avatar
            if ($user->avatar) {
                $this->removeOldFile($user->avatar);
            }

            // Next Update the avatar
            $avatarUrl = $this->getUploadedFileUrl($request->file('avatar'), 'img/avatars');

            if (!$avatarUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $user->name     = $request->name ?? $user->name;
        $user->email    = $request->email ?? $user->email;
        $user->phone    = $request->name ?? $user->phone;
        $user->password = Hash::make($request->password) ?? $user->password;
        $user->role     = $request->role ?? $user->role;
        $user->avatar   = $avatarUrl ?? $user->avatar;

        $user->update();

        return $this->sendSuccess(__('user.updated'), $user);
    }

    /**
     * Soft delete a specific user
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::withoutTrashed()->find($id);

        if (!$user) {
            return $this->sendError(__('user.not_found'));
        }

        $user->delete();

        return $this->sendSuccess(__('user.disabled'));
    }

    /**
     * Display all soft-deleted user
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function trashed(Request $request)
    {
        // Set default per page value or get from request (with a fallback)
        $perPage = $request->get('per_page', 10);

        $users = User::onlyTrashed()->paginate($perPage);

        if ($users->isEmpty()) {
            return $this->sendError(__('user.disabled_records_err'));
        }

        return $this->sendSuccess(__('user.disabled_records'), $users->items(), Response::HTTP_OK,
            [
                'current_page' => $users->currentPage(),
                'total_count' => $users->total(),
                'per_page' => $users->perPage(),
                'total_pages' => $users->lastPage(),
                'has_more_pages' => $users->hasMorePages(),
            ],
            [
                'next_page_url' => $users->nextPageUrl(),
                'prev_page_url' => $users->previousPageUrl(),
                'first_page_url' => $users->url(1),
                'last_page_url' => $users->url($users->lastPage()),
            ]
        );
    }

    /**
     * Restore a soft-deleted user
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return $this->sendError(__('user.not_found'));
        }
        
        $user->restore();

        return $this->sendSuccess(__('user.restored'));
    }

    /**
     * Permanently delete a user
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return $this->sendError(__('user.not_found'));
        }

        if ($user->avatar) {
            $this->removeOldFile($user->avatar);
        }

        $user->forceDelete();

        return $this->sendSuccess(__('user.deleted'));
    }
}
