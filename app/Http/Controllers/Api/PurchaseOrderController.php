<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use Auth;
use DB;
use Storage;

use App\Models\Reservation;
use App\Models\ReservationDetail;

use App\Helpers\HashId;

class PurchaseOrderController extends Controller
{

    public function index()
    {
        Auth::user()->cekRoleModules(['reservation-view']);

        $reservation = (new Reservation)->newQuery();

        $reservation->where('status', 1); //approve

        $reservation->with(['room_sender', 'room_receiver', 'vendor']);

        $reservation->has('details');
        $reservation->doesntHave('delivery_orders');

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $reservation->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $reservation->whereIn('plant_id', $plant_id);
        }

        if (request()->has('code')) {
            $reservation->whereIn('code', request()->input('code'));
        }

        if (request()->has('plant_id')) {
            $reservation->whereIn('plant_id', request()->input('plant_id'));
        }

        if (request()->has('room_id')) {
            $reservation->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('vendor_id')) {
            $reservation->whereIn('vendor_id', request()->input('vendor_id'));
        }

        if (request()->has('type')) {
            $reservation->whereIn('type', request()->input('type'));
        }

        if (request()->has('status')) {
            $reservation->whereIn('status', request()->input('status'));
        }

        if (request()->has('delivery_date')) {
            $start = trim(request()->delivery_date[0], '"');
            $end   = trim(request()->delivery_date[1], '"');
            $reservation->whereBetween('delivery_date', [$start, $end]);
        }

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $reservation->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $reservation->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $reservation->orderBy('id', 'desc');
        }

        $reservation = $reservation->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $reservation;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['reservation-view']);

        $reservation = (new Reservation)->newQuery();

        $reservation->with([
            'room_sender', 
            'room_receiver', 
            'vendor',
            'plant'
        ]);

        $reservation->where('status', 1); //approve

        $reservation->with('room_sender.plant');
        $reservation->with('room_receiver.plant');

        $reservation->has('details');
        $reservation->doesntHave('delivery_orders');

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $reservation->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $reservation->whereIn('plant_id', $plant_id);
        }

        if (request()->has('code')) {
            $reservation->whereIn('code', request()->input('code'));
        }

        if (request()->has('plant_id')) {
            $reservation->whereIn('plant_id', request()->input('plant_id'));
        }

        if (request()->has('room_id')) {
            $reservation->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('vendor_id')) {
            $reservation->whereIn('vendor_id', request()->input('vendor_id'));
        }

        if (request()->has('type')) {
            $reservation->whereIn('type', request()->input('type'));
        }

        if (request()->has('status')) {
            $reservation->whereIn('status', request()->input('status'));
        }

        if (request()->has('delivery_date')) {
            $start = trim(request()->delivery_date[0], '"');
            $end   = trim(request()->delivery_date[1], '"');
            $reservation->whereBetween('delivery_date', [$start, $end]);
        }

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $reservation->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $reservation->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $reservation->orderBy('id', 'desc');
        }

        $reservation = $reservation->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($reservation['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $reservation['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $reservation;
    }
}
