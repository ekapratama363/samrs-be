<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderSerial extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

    protected $casts = [
        'id' => 'integer',
        'delivery_order_detail_id' => 'integer',
        'reservation_detail_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];
}
