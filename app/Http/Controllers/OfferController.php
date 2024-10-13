<?php

namespace App\Http\Controllers;

use App\Events\OfferCreated;
use App\Models\Offer;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Requests\UpdateOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends BaseController
{
    /**
     * Display a listing of offers
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

        $offers = Offer::withTrashed()->paginate($perPage);

        if ($offers->isEmpty()) {
            return $this->sendError(__('offer.all_records_err'));
        }

        return $this->sendSuccess(__('offer.all_records'), $offers->items(), Response::HTTP_OK,
            [
                'current_page' => $offers->currentPage(),
                'total_count' => $offers->total(),
                'per_page' => $offers->perPage(),
                'total_pages' => $offers->lastPage(),
                'has_more_pages' => $offers->hasMorePages(),
            ],
            [
                'next_page_url' => $offers->nextPageUrl(),
                'prev_page_url' => $offers->previousPageUrl(),
                'first_page_url' => $offers->url(1),
                'last_page_url' => $offers->url($offers->lastPage()),
            ]
        );
    }

    /**
     * Store a newly created offer
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
            'store_id'      => 'required|numeric',
            'image'         => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address'       => 'required|string|max:255',
            'about'         => 'required',
            'price'         => 'required|numeric',
            'latitude'      => 'required|numeric',
            'longitude'     => 'required|numeric',
            'start_date'    => 'required|date_format:Y-m-d H:i:s',
            'end_date'      => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('offer.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imageUrl = $this->getUploadedFileUrl($request->file('image'), 'img/offers');

            if (!$imageUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $offer = Offer::create([
            'name'          => $request->name,
            'store_id'      => $request->store_id,
            'image'         => $imageUrl,
            'address'       => $request->address,
            'about'         => $request->about,
            'price'         => $request->price,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
        ]);

        event(new OfferCreated($offer));
        // OfferCreated::dispatch($offer);

        return $this->sendSuccess(__('offer.added'), $offer, Response::HTTP_CREATED);
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
        $offer = Offer::withTrashed()->find($id);

        if (!$offer) {
            return $this->sendError(__('offer.not_found'));
        }

        return $this->sendSuccess(__('offer.found'), $offer);
    }

    /**
     * Update a specific offer
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $offer = Offer::withTrashed()->find($id);

        if (!$offer) {
            return $this->sendError(__('offer.not_found'));
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'store_id'      => 'required|numeric',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address'       => 'required|string|max:255',
            'about'         => 'required',
            'price'         => 'required|numeric',
            'latitude'      => 'required|numeric',
            'longitude'     => 'required|numeric',
            'start_date'    => 'required',
            'end_date'      => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('Offer.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }


        $imageUrl = null;

        if ($request->hasFile('image')) {
            // first unlink the old avatar
            if ($offer->image) {
                $this->removeOldFile($offer->image);
            }

            // Next Update the avatar
            $imageUrl = $this->getUploadedFileUrl($request->file('image'), 'img/offers');

            if (!$imageUrl) {
                return $this->sendError(__('image.store_failed'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $offer->name        = $request->name ?? $offer->name;
        $offer->store_id    = $request->store_id ?? $offer->store_id;
        $offer->address     = $request->address ?? $offer->address;
        $offer->about       = $request->about ?? $offer->about;
        $offer->price       = $request->price ?? $offer->price;
        $offer->latitude    = $request->latitude ?? $offer->latitude;
        $offer->longitude   = $request->longitude ?? $offer->longitude;
        $offer->start_date  = $request->start_date ?? $offer->start_date;
        $offer->end_date    = $request->end_date ?? $offer->end_date;
        $offer->image       = $imageUrl ?? $offer->image;

        $offer->update();

        return $this->sendSuccess(__('offer.updated'), $offer);
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
        $offer = Offer::withoutTrashed()->find($id);

        if (!$offer) {
            return $this->sendError(__('offer.not_found'));
        }

        $offer->delete();

        return $this->sendSuccess(__('offer.disabled'));
    }

    /**
     * Display all soft-deleted offers
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

        $offers = Offer::onlyTrashed()->paginate($perPage);

        if ($offers->isEmpty()) {
            return $this->sendError(__('offer.disabled_records_err'));
        }

        return $this->sendSuccess(__('offer.disabled_records'), $offers->items(), Response::HTTP_OK,
            [
                'current_page' => $offers->currentPage(),
                'total_count' => $offers->total(),
                'per_page' => $offers->perPage(),
                'total_pages' => $offers->lastPage(),
                'has_more_pages' => $offers->hasMorePages(),
            ],
            [
                'next_page_url' => $offers->nextPageUrl(),
                'prev_page_url' => $offers->previousPageUrl(),
                'first_page_url' => $offers->url(1),
                'last_page_url' => $offers->url($offers->lastPage()),
            ]
        );
    }

    /**
     * Restore a soft-deleted offer
     *
     * Undocumented function long description
     *
     * @param mixed $id Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $offer = Offer::onlyTrashed()->find($id);

        if (!$offer) {
            return $this->sendError(__('offer.not_found'));
        }

        $offer->restore();

        return $this->sendSuccess(__('offer.restored'));
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
        $offer = Offer::onlyTrashed()->find($id);

        if (!$offer) {
            return $this->sendError(__('offer.not_found'));
        }

        if ($offer->image) {
            $this->removeOldFile($offer->image);
        }

        $offer->forceDelete();

        return $this->sendSuccess(__('offer.deleted'));
    }

    /**
     * Display a specific offer
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSingleOffer($id)
    {
        $offer = Offer::withoutTrashed()->with('store.category')->find($id);

        if (!$offer) {
            return $this->sendError(__('offer.not_found'));
        }

        return $this->sendSuccess(__('offer.found'), new OfferResource($offer));
    }

    /**
     * Display a listing of nearby offers
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearbyOffers(Request $request)
    {
        // Validate the request input (latitude and longitude)
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius'    => 'nullable|numeric', // (Optional) radius in meters
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('offer.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $latitude   = $request['latitude'];
        $longitude  = $request['longitude'];
        $radius     = $request['radius'] ?? 10000; // Default to 10km if not provided
        $perPage    = $request['per_page'] ?? 10;

        $offers = Offer::nearbyOffers($latitude, $longitude, $radius)
                       ->with('store.category')
                       ->paginate($perPage);

        if ($offers->isEmpty()) {
            return $this->sendError(__('offer.nearby_records_err'));
        }

        return $this->sendSuccess(__('offer.nearby_records'), OfferResource::collection($offers->items()), Response::HTTP_OK,
            [
                'current_page' => $offers->currentPage(),
                'total_count' => $offers->total(),
                'per_page' => $offers->perPage(),
                'total_pages' => $offers->lastPage(),
                'has_more_pages' => $offers->hasMorePages(),
            ],
            [
                'next_page_url' => $offers->nextPageUrl(),
                'prev_page_url' => $offers->previousPageUrl(),
                'first_page_url' => $offers->url(1),
                'last_page_url' => $offers->url($offers->lastPage()),
            ]
        );
    }

    /**
     * Display a listing of nearby offers by category ID
     *
     * Undocumented function long description
     *
     * @param Request $request Description
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearbyOffersByCategory(Request $request, $id)
    {
        // Validate the request input (latitude and longitude)
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius'    => 'nullable|numeric', // (Optional) radius in meters
        ]);

        if ($validator->fails()) {
            return $this->sendError(__('offer.failed'), Response::HTTP_BAD_REQUEST, $validator->errors());
        }

        $category = Category::withoutTrashed()->find($id);

        if (!$category) {
            return $this->sendError(__('category.not_found'));
        }

        $latitude   = $request['latitude'];
        $longitude  = $request['longitude'];
        $radius     = $request['radius'] ?? 10000; // Default to 10km if not provided
        $perPage    = $request['per_page'] ?? 10;

        $offers = Offer::withoutTrashed()
                        ->whereHas('store', function ($query) use ($id) {
                            $query->where('category_id', $id);
                        })
                        ->with('store.category') // Eager-load the store relation
                        ->nearbyOffers($latitude, $longitude, $radius) // Apply the nearby scope for distance calculation
                        ->paginate($perPage);

        if ($offers->isEmpty()) {
            return $this->sendError(__('offer.nearby_records_err'));
        }

        return $this->sendSuccess(__('offer.nearby_records'), OfferResource::collection($offers->items()), Response::HTTP_OK,
            [
                'current_page' => $offers->currentPage(),
                'total_count' => $offers->total(),
                'per_page' => $offers->perPage(),
                'total_pages' => $offers->lastPage(),
                'has_more_pages' => $offers->hasMorePages(),
            ],
            [
                'next_page_url' => $offers->nextPageUrl(),
                'prev_page_url' => $offers->previousPageUrl(),
                'first_page_url' => $offers->url(1),
                'last_page_url' => $offers->url($offers->lastPage()),
            ]
        );
    }
}
