<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $paging = $this->resource->toArray();
        if ($paging['data']) {
            unset($paging['data']);
        }
        $listData = $this->collection;
    
        return   [ 
            'data' =>  $listData, 
            'paging' => $paging
        ];

    }
}
