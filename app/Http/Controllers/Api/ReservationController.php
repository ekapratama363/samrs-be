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

class ReservationController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['reservation-view']);

        $reservation = (new Reservation)->newQuery();

        $reservation->with(['room_sender', 'room_receive', 'vendor']);

        $reservation->has('details');

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

        $reservation->with(['room_sender', 'room_receive', 'vendor']);

        $reservation->has('details');

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

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['reservation-create']);

        $this->validate(request(), [
            'code' => 'nullable',
            'room_id' => 'required|exists:rooms,id',
            'type' => 'required|between:0,3',
            'note' => 'nullable',
        ]);

        if ($request->type == 1) {
            $this->validate(request(), [
                'vendor_id' => 'required|exists:vendors,id',
            ]);
        } else {
            $this->validate(request(), [
                'room_sender' => 'required|exists:rooms,id',
            ]);
        }

        if ($request->room_id == $request->room_sender) {
            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'room_sender' => ['Room sender cannot be same as room recipient']
                ]
            ],422);
        }

        $save = Reservation::create([
            'code'          => 'RES/' . date('ymd/His'),
            'room_id'       => $request->room_id,
            'plant_id'      => $request->plant_id,
            'vendor_id'     => $request->type == 1 ? $request->vendor_id : null,
            'room_sender'   => $request->type != 1 ? $request->room_sender : null,
            'delivery_date' => $request->delivery_date,
            'note'          => $request->note,
            'type'          => $request->type,
            'status'        => 0,
        ]);
        
        return $save;
    }
}
