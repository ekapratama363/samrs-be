<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

    protected $casts = [
        'id' => 'integer',
        'room_id' => 'integer',
        'plant_id' => 'integer',
        'vendor_id' => 'integer',
        'room_sender' => 'integer',
        'type' => 'integer',
        'status' => 'integer',
        'total_price' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    public function details()
    {
        return $this->hasMany('App\Models\ReservationDetail', 'reservation_id');
    }

    public function delivery_orders()
    {
        return $this->hasMany('App\Models\DeliveryOrder', 'reservation_id');
    }

	public function room_receiver()
    {
        return $this->hasOne('App\Models\Room', 'id', 'room_id');
    }

	public function room_sender()
    {
        return $this->hasOne('App\Models\Room', 'id', 'room_sender');
    }

	public function plant()
    {
        return $this->hasOne('App\Models\Plant', 'id', 'plant_id');
    }

	public function vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
    }

	public function approved()
    {
        return $this->hasOne('App\Models\User', 'id', 'approved_or_rejected_by')->where('status', 1); //approved
    }

	public function rejected()
    {
        return $this->hasOne('App\Models\User', 'id', 'approved_or_rejected_by')->where('status', 2); //rejected
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
