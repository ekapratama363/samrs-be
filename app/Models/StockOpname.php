<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpname extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];  

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
    
    public function details()
    {
        return $this->hasMany('App\Models\StockOpnameDetail', 'stock_opname_id');
    }
}
