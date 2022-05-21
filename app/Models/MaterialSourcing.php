<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialSourcing extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

	public function material()
    {
        return $this->hasOne('App\Models\Material', 'id', 'material_id')->withTrashed();
    }

	public function vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id')->withTrashed();
    }

	public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'room_id')->withTrashed();
    }
}
