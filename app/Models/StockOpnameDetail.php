<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameDetail extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ]; 

    protected $casts = [
        'id' => 'integer',
        'stock_opname_id' => 'integer',
        'stock_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];  

	public function stock()
    {
        return $this->hasOne('App\Models\Stock', 'id', 'stock_id')->withTrashed();
    }  

	public function stock_opname()
    {
        return $this->hasOne('App\Models\StockOpname', 'id', 'stock_opname_id')->withTrashed();
    }  

	public function serials()
    {
        return $this->hasMany('App\Models\StockOpnameSerial', 'stock_opname_detail_id');
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
