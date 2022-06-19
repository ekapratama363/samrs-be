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
        if (!empty($this->created_at) && !empty($this->reservation->updated_at)) {
            $start  = new Carbon($this->reservation->updated_at);
            $end    = new Carbon($this->created_at);

            $diff = $start->diff($end);
            $message = "$diff->d days {$diff->h} hours {$diff->m} minutes {$diff->s} seconds";

            return $message;
        }
    }

	public function reservation()
    {
        return $this->hasOne('App\Models\Reservation', 'id', 'reservation_id');
    }

	public function details()
    {
        return $this->hasMany('App\Models\DeliveryOrderDetail', 'delivery_order_id');
    }
}
