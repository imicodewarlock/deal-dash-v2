<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Store;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function search(Request $request)
    {
        $query      = $request->input('query');
        $perPage    = $request->input('per_page', 10); // Items per page, default is 10

        // Perform a search on categories, stores, and offers
        // $categories = Category::search($query)->paginate($perPage);
        // $stores     = Store::search($query)->paginate($perPage);
        $offers     = Offer::search($query)->paginate($perPage);

        if ($offers->isEmpty()) {
            return $this->sendError(__('search.all_records_err'));
        }

        return $this->sendSuccess(__('search.all_records'), $offers->items(), Response::HTTP_OK,
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
