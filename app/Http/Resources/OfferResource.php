<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "store_id" => $this->store_id,
            "image" => $this->image,
            "address" => $this->address,
            "about" => $this->about,
            "price" => $this->price,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "created_at" => $this->created_at,
            "store" => [
                "id" => $this->store->id,
                "name" => $this->store->name,
                "category_id" => $this->store->category_id,
                "image" => $this->store->image,
                "address" => $this->store->address,
                "about" => $this->store->about,
                "phone" => $this->store->phone,
                "latitude" => $this->store->latitude,
                "longitude" => $this->store->longitude,
                "place_id" => $this->store->place_id,
                "created_at" => $this->store->created_at,
                "category" => [
                    "id" => $this->store->category->id,
                    "name" => $this->store->category->name,
                    "type" => $this->store->category->type,
                    "image" => $this->store->category->image,
                ]
            ]
        ];
    }
}
