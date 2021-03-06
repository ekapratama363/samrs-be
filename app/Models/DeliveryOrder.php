<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Carbon\Carbon;

class DeliveryOrder extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id', 'created_at', 'updated_at', 'deleted_at'
    ];   

    protected $casts = [
        'id' => 'integer',
        'reservation_id' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];
    
    protected $appends = [
        'duration'
    ];

    public function getDurationAttribute()
    {
        if (!empty($this->created_at)) {
            $start  = new Carbon($this->created_at);
            $end    = $this->approved_or_rejected_at ? new Carbon($this->approved_or_rejected_at) : Carbon::now();

            $diff = $start->diff($end);
            $message = "$diff->d days {$diff->h} hours {$diff->i} minutes";

            return $message;
        }
    }

	public function reservation()
    {
        return $this->hasOne('App\Models\Reservation', 'id', 'reservation_id');
    }

	// public function good_receive()
    // {
    //     return $this->belongsTo('App\Models\DeliveryOrder', 'id')->where('status', 1); //received
    // }

	public function details()
    {
        return $this->hasMany('App\Models\DeliveryOrderDetail', 'delivery_order_id');
    }

	public function approved()
    {
        return $this->hasOne('App\Models\User', 'id', 'approved_or_rejected_by')->where('status', 1); //approved
    }

	public function rejected()
    {
        return $this->hasOne('App\Models\User', 'id', 'approved_or_rejected_by')->where('status', 2); //rejected
    }
}
