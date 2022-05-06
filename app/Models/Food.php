<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'type',
        'active'
    ];

    protected $appends = ['rating'];
    public function category()
    {
        return $this->belongsToMany(Category::class, 'category_food');
    }

    public function foodTag()
    {
        return $this->belongsToMany(Tag::class, 'food_tags');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function review()
    {
        return $this->hasMany(Review::class, 'food_id', 'id');
    }

    public function ingredient()
    {
        return $this->hasMany(Ingredient::class, 'food_id', 'id');
    }

    public function getRatingAttribute()
    {
        return $this->review()->avg('start') ?: 0;
    }

}
