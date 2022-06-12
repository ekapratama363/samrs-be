<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationDetail extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

    protected $casts = [
        'id' => 'integer',
        'reservation_id' => 'integer',
        'material_id' => 'integer',
        'quantity' => 'integer',
        'delivery_quantity' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

	public function material()
    {
        return $this->hasOne('App\Models\Material', 'id', 'material_id');
    }
}
