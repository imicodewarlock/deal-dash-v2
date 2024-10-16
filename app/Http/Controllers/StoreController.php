<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends BaseController
{
    /**
     * Display a listing of users (only non-deleted ones)
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

        $stores = Store::withTrashed()->paginate($perPage);

        if ($stores->isEmpty()) {
            return $this->sendError(__('store.all_records_err'));
        }

        return $this->sendSuccess(__('store.all_records'), $stores->items(), Response::HTTP_OK,
            [
                'current_page' => $stores->currentPage(),
                'total_count' => $stores->total(),
                'per_page' => $stores->perPage(),
                'total_pages' => $stores->lastPage(),
                'has_more_pages' => $stores->hasMorePages(),
            ],
            [
                'next_page_url' => $stores->nextPageUrl(),
                'prev_page_url' => $stores->previousPageUrl(),
                'first_page_url' => $stores->url(1),
                'last_page_url' => $stores->url($stores->lastPage()),
            ]
        );
    }

    /**
     * Store a newly created store
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
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|integer|min:1|exists:categories,id',
            'image'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address'       => 'required|string|max:255',
            'about'         => 'required|string|min:3|max:1000',
            'phone'         => 'required|numeric',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'place_id'      => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('store.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $this->getUploadedFileUrl($request->file('image'), 'img/stores');

            if (!$imageUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $store = Store::create([
            'name'          => $request->name,
            'category_id'   => $request->category_id,
            'image'         => $imageUrl,
            'address'       => $request->address,
            'about'         => $request->about,
            'phone'         => $request->phone,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'place_id'      => $request->place_id,
        ]);

        return $this->sendSuccess(__('store.added'), $store, Response::HTTP_CREATED);
    }

    /**
     * Display a specific offer
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $store = Store::withTrashed()->find($id);

        if (!$store) {
            return $this->sendError(__('store.not_found'));
        }

        return $this->sendSuccess(__('store.found'), $store);
    }

    /**
     * Update a specific offer
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
        $store = Store::withTrashed()->find($id);

        if (!$store) {
            return $this->sendError(__('store.not_found'));
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'nullable|string|max:255',
            'category_id'   => 'nullable|integer|min:1|exists:categories,id',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address'       => 'nullable|string|max:255',
            'about'         => 'nullable|string|min:3|max:1000',
            'phone'         => 'nullable|numeric',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'place_id'      => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('store.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            // first unlink the old avatar
            if ($store->image) {
                $this->removeOldFile($store->image);
            }

            // Next Update the avatar
            $imageUrl = $this->getUploadedFileUrl($request->file('image'), 'img/stores');

            if (!$imageUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $store->name        = $request->name ?? $store->name;
        $store->category_id = $request->category_id ?? $store->category_id;
        $store->address     = $request->address ?? $store->address;
        $store->about       = $request->about ?? $store->about;
        $store->phone       = $request->phone ?? $store->phone;
        $store->latitude    = $request->latitude ?? $store->latitude;
        $store->longitude   = $request->longitude ?? $store->longitude;
        $store->place_id    = $request->place_id ?? $store->place_id;
        $store->image       = $imageUrl ?? $store->image;

        $store->update();

        return $this->sendSuccess(__('store.updated'), $store);
    }

    /**
     * Soft delete a specific offer
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $store = Store::withoutTrashed()->find($id);

        if (!$store) {
            return $this->sendError(__('store.not_found'));
        }

        $store->delete();

        return $this->sendSuccess(__('store.disabled'));
    }

    /**
     * Display all soft-deleted store
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

        $stores = Store::onlyTrashed()->paginate($perPage);

        if ($stores->isEmpty()) {
            return $this->sendError(__('store.disabled_records_err'));
        }

        return $this->sendSuccess(__('store.disabled_records'), $stores->items(), Response::HTTP_OK,
            [
                'current_page' => $stores->currentPage(),
                'total_count' => $stores->total(),
                'per_page' => $stores->perPage(),
                'total_pages' => $stores->lastPage(),
                'has_more_pages' => $stores->hasMorePages(),
            ],
            [
                'next_page_url' => $stores->nextPageUrl(),
                'prev_page_url' => $stores->previousPageUrl(),
                'first_page_url' => $stores->url(1),
                'last_page_url' => $stores->url($stores->lastPage()),
            ]
        );
    }

    /**
     * Restore a soft-deleted store
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $store = Store::onlyTrashed()->find($id);

        if (!$store) {
            return $this->sendError(__('store.not_found'));
        }

        $store->restore();

        return $this->sendSuccess(__('store.restored'));
    }

    /**
     * Permanently delete an offer
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        $store = Store::onlyTrashed()->find($id);

        if (!$store) {
            return $this->sendError(__('store.not_found'));
        }

        if ($store->image) {
            $this->removeOldFile($store->image);
        }

        $store->forceDelete();

        return $this->sendSuccess(__('store.deleted'));
    }


    /**
     * Display a listing of stores (only non-deleted ones)
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableStores(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $stores = Store::withoutTrashed()
                        ->withCount(['favoriteByUsers as favorites_count'])
                        ->with('offers')
                        // ->orderBy('favorites_count', 'desc')
                        ->paginate($perPage);

        if ($stores->isEmpty()) {
            return $this->sendError(__('store.all_records_err'));
        }

        return $this->sendSuccess(__('store.all_records'), $stores->items(), Response::HTTP_OK,
            [
                'current_page' => $stores->currentPage(),
                'total_count' => $stores->total(),
                'per_page' => $stores->perPage(),
                'total_pages' => $stores->lastPage(),
                'has_more_pages' => $stores->hasMorePages(),
            ],
            [
                'next_page_url' => $stores->nextPageUrl(),
                'prev_page_url' => $stores->previousPageUrl(),
                'first_page_url' => $stores->url(1),
                'last_page_url' => $stores->url($stores->lastPage()),
            ]
        );
    }

    /**
     * Display a specific store
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingleStore($id)
    {
        $store = Store::withoutTrashed()
                      ->withCount(['favoriteByUsers as favorites_count'])
                      ->with('offers')
                      ->find($id);

        if (!$store) {
            return $this->sendError(__('store.not_found'));
        }

        return $this->sendSuccess(__('store.found'), $store);
    }

    /**
     * Display a listing of nearby stores
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearbyStores(Request $request)
    {
        // Validate the request input (latitude and longitude)
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius'    => 'nullable|numeric', // Optional radius in kilometers
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('store.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $perPage = $request['per_page'] ?? 10;

        $stores = Store::nearbyStores($request['latitude'], $request['longitude'], $request['radius'])
                       ->withCount(['favoriteByUsers as favorites_count'])
                       ->with('offers')
                       ->with('category:id,name')
                       ->paginate($perPage);

        // Return the stores as a JSON response
        if ($stores->isEmpty()) {
            return $this->sendError(__('store.nearby_records_err'));
        }

        return $this->sendSuccess(__('store.nearby_records'), $stores->items(), Response::HTTP_OK,
            [
                'current_page' => $stores->currentPage(),
                'total_count' => $stores->total(),
                'per_page' => $stores->perPage(),
                'total_pages' => $stores->lastPage(),
                'has_more_pages' => $stores->hasMorePages(),
            ],
            [
                'next_page_url' => $stores->nextPageUrl(),
                'prev_page_url' => $stores->previousPageUrl(),
                'first_page_url' => $stores->url(1),
                'last_page_url' => $stores->url($stores->lastPage()),
            ]
        );
    }

    /**
     * Display a listing of nearby stores for a given category.
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * @param \App\Models\Category $category Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearbyStoresByCategory(Request $request, Category $category)
    {
        // Validate the request input (latitude and longitude)
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius'    => 'nullable|numeric', // Optional radius in kilometers
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('store.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $latitude   = $request['latitude'];
        $longitude  = $request['longitude'];
        $radius     = $request['radius'] ?? 10000; // Default to 10km if not provided
        $perPage    = $request['per_page'] ?? 10;

        $stores = Store::nearbyStores($latitude, $longitude, $radius)
                       ->withCount(['favoriteByUsers as favorites_count'])
                       ->with('offers')
                       ->with('category:id,name')
                       ->where('category_id', $category->id)
                       ->paginate($perPage);

        // Return the stores as a JSON response
        if ($stores->isEmpty()) {
            return $this->sendError(__('store.nearby_records_err'));
        }

        return $this->sendSuccess(__('store.nearby_records'), $stores->items(), Response::HTTP_OK,
            [
                'current_page' => $stores->currentPage(),
                'total_count' => $stores->total(),
                'per_page' => $stores->perPage(),
                'total_pages' => $stores->lastPage(),
                'has_more_pages' => $stores->hasMorePages(),
            ],
            [
                'next_page_url' => $stores->nextPageUrl(),
                'prev_page_url' => $stores->previousPageUrl(),
                'first_page_url' => $stores->url(1),
                'last_page_url' => $stores->url($stores->lastPage()),
            ]
        );
    }

    /**
     * Mark a specific store as Favorite/Unfavorite
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     * @param mixed $id Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleFavorite(Request $request, $id)
    {
        $store = Store::withTrashed()->find($id);

        if (!$store) {
            return $this->sendError(__('store.not_found'));
        }

        $user = $request->user();

        if ($user->favoriteStores()->where('store_id', $store->id)->exists()) {
            // Unfavorite if already favorite
            $user->favoriteStores()->detach($store->id);
            return $this->sendSuccess(__('store.unfavorite'), ['favorites_count' => $store->favoritesCount()]);
        } else {
            // Favorite the store
            $user->favoriteStores()->attach($store->id);
            return $this->sendSuccess(__('store.favorite'), ['favorites_count' => $store->favoritesCount()]);
        }
    }

    /**
     * Display a listing of favorite offers
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoresFavoriteByUsers(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $user = $request->user();
        $favoriteStores = $user->favoriteStores()->paginate($perPage);

        if ($favoriteStores->isEmpty()) {
            return $this->sendError(__('store.favorite_records_err'));
        }
        // return $this->sendSuccess(__('store.favorite_records'), $favoriteStores);
        return $this->sendSuccess(__('store.favorite_records'), $favoriteStores->items(), Response::HTTP_OK,
            [
                'current_page' => $favoriteStores->currentPage(),
                'total_count' => $favoriteStores->total(),
                'per_page' => $favoriteStores->perPage(),
                'total_pages' => $favoriteStores->lastPage(),
                'has_more_pages' => $favoriteStores->hasMorePages(),
            ],
            [
                'next_page_url' => $favoriteStores->nextPageUrl(),
                'prev_page_url' => $favoriteStores->previousPageUrl(),
                'first_page_url' => $favoriteStores->url(1),
                'last_page_url' => $favoriteStores->url($favoriteStores->lastPage()),
            ]
        );
    }
}
