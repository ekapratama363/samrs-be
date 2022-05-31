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

    protected $appends = ['serials'];

    public function getSerialsAttribute()
    {
        return $this->serial_numbers ? json_decode($this->serial_numbers) : [];
    }

	public function stock()
    {
        return $this->hasOne('App\Models\Stock', 'id', 'stock_id')->withTrashed();
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
