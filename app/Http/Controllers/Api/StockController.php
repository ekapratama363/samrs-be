<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;

use App\Models\MaterialSourcing as Stock;

use App\Helpers\HashId;

class StockController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $stock = (new Stock)->newQuery();

        $stock->with(['material', 'room', 'vendor']);
        $stock->with(['material.uom']);
        $stock->with(['material.classification']);
        $stock->with(['room.plant']);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $stock->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $stock->whereIn('plant_id', $plant_id);
        }

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);
        if (count($vendor_id) > 0) {
            $stock->whereIn('vendor_id', $vendor_id);
        }

        if (request()->has('plant_id')) {
            $stock->whereHas('room', function($q) {
                $q->whereIn('plant_id', request()->input('plant_id'));
            });
        }

        if (request()->has('room_id')) {
            $stock->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('vendor_id')) {
            $stock->whereIn('vendor_id', request()->input('vendor_id'));
        }

        if (request()->has('material_id')) {
            $stock->whereIn('material_id', request()->input('material_id'));
        }

        if (request()->input('ready_stock') === 'true') {
            $stock->where('stock', '>', 0);
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock->whereHas('material', function ($data) use ($q) {
                $data->where(function($query) use ($q) {
	                $query->orWhere(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
	            });
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock->orderBy('id', 'desc');
        }

        $stock = $stock->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $stock;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $stock = (new Stock)->newQuery();

        $stock->with(['material', 'room', 'vendor']);
        $stock->with(['material.uom']);
        $stock->with(['material.classification']);
        $stock->with(['room.plant']);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $stock->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $stock->whereIn('plant_id', $plant_id);
        }

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);
        if (count($vendor_id) > 0) {
            $stock->whereIn('vendor_id', $vendor_id);
        }

        if (request()->has('plant_id')) {
            $stock->whereHas('room', function($q) {
                $q->whereIn('plant_id', request()->input('plant_id'));
            });
        }

        if (request()->has('room_id')) {
            $stock->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('vendor_id')) {
            $stock->whereIn('vendor_id', request()->input('vendor_id'));
        }

        if (request()->has('material_id')) {
            $stock->whereIn('material_id', request()->input('material_id'));
        }

        if (request()->input('ready_stock') === 'true') {
            $stock->where('stock', '>', 0);
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock->whereHas('material', function ($data) use ($q) {
                $data->where(function($query) use ($q) {
	                $query->orWhere(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
	            });
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock->orderBy('id', 'desc');
        }

        $stock = $stock->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($stock['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $stock['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $stock;
    }
}
