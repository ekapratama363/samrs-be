<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

    protected $casts = [
        'id' => 'integer',
        'material_id' => 'integer',
        'room_id' => 'integer',
        'stock' => 'integer',
        'minimum_stock' => 'integer',
        'quantity_in_transit' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

	public function material()
    {
        return $this->hasOne('App\Models\Material', 'id', 'material_id')->withTrashed();
    }
    
    public function stock_details()
    {
        return $this->hasMany('App\Models\StockDetail', 'stock_id');
    }

	// public function vendor()
    // {
    //     return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id')->withTrashed();
    // }

	public function room()
    {
        return $this->hasOne('App\Models\Room', 'id', 'room_id')->withTrashed();
    }

	public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }
}
