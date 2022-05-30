<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;

use App\Models\Stock;
use App\Models\StockOpname;
use App\Models\StockDetail;

use App\Helpers\HashId;

class StockOpnameController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['stock-opname-view']);

        $stock_opname = (new StockOpname)->newQuery();

        $stock_opname->with(['room', 'createdBy', 'updatedBy']);
        $stock_opname->with(['room.plant']);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $stock_opname->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $stock_opname->whereHas('room.plant', function($query) use($plant_id) {
                $query->whereIn('id', $plant_id);
            });
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock_opname->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_opname->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_opname->orderBy('id', 'desc');
        }

        $stock_opname = $stock_opname->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $stock_opname;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['stock-opname-view']);

        $stock_opname = (new StockOpname)->newQuery();

        $stock_opname->with(['room', 'createdBy', 'updatedBy']);
        $stock_opname->with(['room.plant']);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $stock_opname->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $stock_opname->whereHas('room.plant', function($query) use($plant_id) {
                $query->whereIn('id', $plant_id);
            });
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock_opname->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_opname->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_opname->orderBy('id', 'desc');
        }

        $stock_opname = $stock_opname->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($stock_opname['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $stock_opname['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $stock_opname;
    }
}
