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

    public function details()
    {
        return $this->hasMany('App\Models\ReservationDetail', 'reservation_id');
    }

	public function room_receive()
    {
        return $this->hasOne('App\Models\Room', 'id', 'room_receive');
    }

	public function room_sender()
    {
        return $this->hasOne('App\Models\Room', 'id', 'room_sender');
    }

	public function vendor()
    {
        return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
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
