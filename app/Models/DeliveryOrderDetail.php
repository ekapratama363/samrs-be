<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderDetail extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

    protected $casts = [
        'id' => 'integer',
        'delivery_order_id' => 'integer',
        'reservation_detail_id' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

	public function serial_numbers()
    {
        return $this->hasMany('App\Models\DeliveryOrderSerial', 'delivery_order_detail_id');
    }
}
