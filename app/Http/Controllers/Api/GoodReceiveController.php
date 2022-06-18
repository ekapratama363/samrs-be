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

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderDetail;
use App\Models\DeliveryOrderSerial;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\StockDetail;
use App\Models\Stock;

use App\Helpers\HashId;

class GoodReceiveController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['good-receive-view']);

        $gr = (new DeliveryOrder)->newQuery();

        $gr->with('reservation');

        $gr->with([
            'reservation.room_sender', 
            'reservation.room_sender.plant',
            'reservation.room_receiver', 
            'reservation.room_receiver.plant',
            'reservation.vendor',
            'reservation.plant'
        ]);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $gr->whereHas('reservation', function($query) use($room_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $gr->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $plant_id);
            });
        }

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);
        if (count($vendor_id) > 0) {
            $gr->whereHas('reservation', function($query) use($vendor_id) {
                $query->whereIn('vendor_id', $vendor_id);
            });
        }

        if (request()->has('code_do')) {
            $gr->whereIn('code', request()->input('code_do'));
        }

        if (request()->has('code')) {
            $code = request()->input('code');
            $gr->whereHas('reservation', function($query) use($code) {
                $query->whereIn('code', $code);
            });
        }

        if (request()->has('plant_id')) {
            $plant_id = request()->input('plant_id');
            $gr->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $plant_id);
            });
        }

        if (request()->has('room_id')) {
            $room_id = request()->input('room_id');
            $gr->whereHas('reservation', function($query) use($room_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        if (request()->has('vendor_id')) {
            $vendor_id = request()->input('vendor_id');
            $gr->whereHas('reservation', function($query) use($vendor_id) {
                $query->whereIn('vendor_id', $vendor_id);
            });
        }

        if (request()->has('status')) {
            $gr->whereIn('status', request()->input('status'));
        }

        if (request()->has('created_at')) {
            $start = trim(request()->created_at[0], '"');
            $end   = trim(request()->created_at[1], '"');
            $gr->whereBetween('created_at', [$start, $end]);
        }

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $gr->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $gr->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $gr->orderBy('id', 'desc');
        }

        $gr = $gr->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $gr;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['good-receive-view']);

        $gr = (new DeliveryOrder)->newQuery();

        $gr->with('reservation');

        $gr->with([
            'reservation.room_sender', 
            'reservation.room_sender.plant',
            'reservation.room_receiver', 
            'reservation.room_receiver.plant',
            'reservation.vendor',
            'reservation.plant'
        ]);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $gr->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $gr->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $room_id);
            });
        }

        if (request()->has('code_do')) {
            $gr->whereIn('code', request()->input('code_do'));
        }

        if (request()->has('code')) {
            $code = request()->input('code');
            $gr->whereHas('reservation', function($query) use($code) {
                $query->whereIn('code', $code);
            });
        }

        if (request()->has('plant_id')) {
            $plant_id = request()->input('plant_id');
            $gr->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $plant_id);
            });
        }

        if (request()->has('room_id')) {
            $room_id = request()->input('room_id');
            $gr->whereHas('reservation', function($query) use($room_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        if (request()->has('vendor_id')) {
            $vendor_id = request()->input('vendor_id');
            $gr->whereHas('reservation', function($query) use($vendor_id) {
                $query->whereIn('vendor_id', $vendor_id);
            });
        }

        if (request()->has('created_at')) {
            $start = trim(request()->created_at[0], '"');
            $end   = trim(request()->created_at[1], '"');
            $gr->whereBetween('created_at', [$start, $end]);
        }

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $gr->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $gr->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $gr->orderBy('id', 'desc');
        }

        $gr = $gr->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($gr['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $gr['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $gr;
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['good-receive-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $do = DeliveryOrder::with([
            'reservation.room_sender', 
            'reservation.room_receiver', 
            'reservation.vendor',
            'reservation.plant',
            'reservation.room_sender.plant',
            'reservation.room_sender.responsible_person',
            'reservation.room_receiver.plant',
            'reservation.room_receiver.responsible_person',
            'reservation.details',
            'reservation.details.do_detail.serial_numbers',
            'reservation.details.material',
            'reservation.details.material.uom'
        ])->find($id);

        if (count($do->reservation->details) > 0) {
            foreach($do->reservation->details as $index => $detail) {
                $detail->material_code = $detail->material->material_code;
                $detail->description = $detail->material->description;
                $detail->uom = $detail->material->uom;
            }
        }

        return $do;
    }
}
