<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FoodCollection;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // dd($this);
        $data = [
            'id' => $this->id,
            'store_name' => $this->store['name'],
            'store_des' => $this->store['desc'],
            'image' => $this->store['image'],
            'total_price' => $this->total_price,
            'delivery' => $this->day .' '. $this->time,
            'status' => $this->status,
            'list_food' => new FoodCollection($this->itemFood)
        ];
        return $data;
    }
}
