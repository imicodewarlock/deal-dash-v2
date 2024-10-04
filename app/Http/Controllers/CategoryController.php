<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends BaseController
{
    /**
     * Display a listing of categories
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

        $categories = Category::withTrashed()->paginate($perPage);

        if ($categories->isEmpty()) {
            return $this->sendError(__('category.all_records_err'));
        }
        
        return $this->sendSuccess(__('category.all_records'), $categories->items(), Response::HTTP_OK,
            [
                'current_page' => $categories->currentPage(),
                'total_count' => $categories->total(),
                'per_page' => $categories->perPage(),
                'total_pages' => $categories->lastPage(),
                'has_more_pages' => $categories->hasMorePages(),
            ],
            [
                'next_page_url' => $categories->nextPageUrl(),
                'prev_page_url' => $categories->previousPageUrl(),
                'first_page_url' => $categories->url(1),
                'last_page_url' => $categories->url($categories->lastPage()),
            ]
        );
    }

    /**
     * Store a newly created category
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
            'name'  => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('category.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $this->getUploadedFileUrl($request->file('image'), 'img/categories');

            if (!$imageUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $category = Category::create([
            'name'  => $request->name,
            'image' => $imageUrl,
        ]);

        return $this->sendSuccess(__('category.added'), $category, Response::HTTP_CREATED);
    }

    /**
     * Display a specific category
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return $this->sendError(__('category.not_found'));
        }
        
        return $this->sendSuccess(__('category.found'), $category);
    }

    /**
     * Update a specific category
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
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return $this->sendError(__('category.not_found'));
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('category.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            // first unlink the old avatar
            if ($category->image) {
                $this->removeOldFile($category->image);
            }

            // Next Update the avatar
            $imageUrl = $this->getUploadedFileUrl($request->file('image'), 'img/categories');

            if (!$imageUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $category->name     = $request->name ?? $category->name;
        $category->image    = $imageUrl ?? $category->image;

        $category->update();

        return $this->sendSuccess(__('category.updated'), $category);
    }

    /**
     * Soft delete a specific category
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $category = Category::withoutTrashed()->find($id);

        if (!$category) {
            return $this->sendError(__('category.not_found'));
        }

        $category->delete();

        return $this->sendSuccess(__('category.disabled'));
    }

    /**
     * Display all soft-deleted categories
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

        $categories = Category::onlyTrashed()->paginate($perPage);

        if ($categories->isEmpty()) {
            return $this->sendError(__('category.disabled_records_err'));
        }

        return $this->sendSuccess(__('category.disabled_records'), $categories->items(), Response::HTTP_OK,
            [
                'current_page' => $categories->currentPage(),
                'total_count' => $categories->total(),
                'per_page' => $categories->perPage(),
                'total_pages' => $categories->lastPage(),
                'has_more_pages' => $categories->hasMorePages(),
            ],
            [
                'next_page_url' => $categories->nextPageUrl(),
                'prev_page_url' => $categories->previousPageUrl(),
                'first_page_url' => $categories->url(1),
                'last_page_url' => $categories->url($categories->lastPage()),
            ]
        );
    }

    /**
     * Restore a soft-deleted category
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $category = Category::onlyTrashed()->find($id);

        if (!$category) {
            return $this->sendError(__('category.not_found'));
        }
        
        $category->restore();

        return $this->sendSuccess(__('category.restored'));
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
        $category = Category::onlyTrashed()->find($id);

        if (!$category) {
            return $this->sendError(__('category.not_found'));
        }

        if ($category->image) {
            $this->removeOldFile($category->image);
        }

        $category->forceDelete();

        return $this->sendSuccess(__('category.deleted'));
    }

    /**
     * Display a listing of categories (only non-deleted ones)
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableCategories(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $categories = Category::withoutTrashed()->with('stores.offers')->paginate($perPage);

        if ($categories->isEmpty()) {
            return $this->sendError(__('category.all_records_err'));
        }

        return $this->sendSuccess(__('category.all_records'), $categories->items(), Response::HTTP_OK,
            [
                'current_page' => $categories->currentPage(),
                'total_count' => $categories->total(),
                'per_page' => $categories->perPage(),
                'total_pages' => $categories->lastPage(),
                'has_more_pages' => $categories->hasMorePages(),
            ],
            [
                'next_page_url' => $categories->nextPageUrl(),
                'prev_page_url' => $categories->previousPageUrl(),
                'first_page_url' => $categories->url(1),
                'last_page_url' => $categories->url($categories->lastPage()),
            ]
        );
    }

    /**
     * Display a specific category
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingleCategory($id)
    {
        $category = Category::withoutTrashed()->with('stores.offers')->find($id);

        if (!$category) {
            return $this->sendError(__('category.not_found'));
        }
        
        return $this->sendSuccess(__('category.found'), $category);
    }
}
