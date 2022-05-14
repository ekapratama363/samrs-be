<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialImage extends Model
{
    protected $guarded = [
        'id', 'created_at', 'updated_at', 
    ];

    protected $appends = [
        'image_url_full'
    ];

    public function getImageUrlFullAttribute()
    {
        if ($this->image && \Storage::disk('public')->exists($this->image)) {
            return \Storage::disk('public')->url($this->image);
        } else {
            return null;
        }
    }
}
