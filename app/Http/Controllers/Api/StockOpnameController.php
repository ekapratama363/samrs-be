<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;

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

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['stock-opname-create']);

        $this->validate(request(), [
            'plant_id' => 'required|exists:plants,id',
            'room_id' => 'required|exists:rooms,id',
            'stocks' => 'required|array',
            'stocks.*.id'  => 'required|exists:stocks,id,deleted_at,NULL',
        ]);

        DB::beginTransaction();
        try {
            $save = StockOpname::create([
                'code' => 'SO/' . date('ymd/His'),
                'room_id' => $request->room_id,
                'status' => 0, //draft
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            foreach($request->stocks as $stock) {
                StockOpnameDetail::create([
                    'stock_opname_id' => $save->id,
                    'stock_id' => $stock['id'],
                    'system_stock' => $stock['stock'],
                    'actual_stock' => 0,
                    'total_scanned' => 0,
                    'serial_numbers' => null,
                    'note' => null,
                    'remark' => null,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id

                ]);
            }

            DB::commit();

            return $request->all();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['stock-opname-create']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            // 'plant_id' => 'required|exists:plants,id',
            // 'room_id' => 'required|exists:rooms,id',
            'stocks' => 'required|array',
            'stocks.*.id'  => 'required|exists:stocks,id,deleted_at,NULL',
        ]);


        DB::beginTransaction();
        try {

            $actual_stocks = [];
            foreach($request->stocks as $stock) {
                $actual_stocks[$stock['id']] = $stock['actual_stock'] > 0 ? range(0, $stock['actual_stock'] - 1) : [];
            }

            $serials = [];
            foreach($request->serials as $serial) {
                $serials[$serial['id']][$serial['key']] = $serial;
            }

            $serial_stocks = [];
            foreach($actual_stocks as $key_1 => $value_1) {
                foreach($value_1 as $key_2 => $value_2) {
                    if (isset($serials[$key_1][$key_2])) {
                        $serial_stocks[$key_1][$key_2] = $serials[$key_1][$key_2]['val'];
                    } 
                }
            }

            $stock_opname = StockOpname::find($id);

            $stock_opname->update([
                'status' => 1, //waiting approve
                'updated_by' => Auth::user()->id
            ]);

            foreach($actual_stocks as $key => $stock) {
                StockOpnameDetail::where('stock_opname_id', $id)
                    ->where('stock_id', $key)
                    ->update([
                        'actual_stock' => isset($actual_stocks[$key]) ? count($actual_stocks[$key]) : 0,
                        'serial_numbers' => isset($serial_stocks[$key]) ? json_encode($serial_stocks[$key]) : null,
                    ]);
            }

            DB::commit();

            return $stock_opname;
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['stock-opname-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $stock_opname = StockOpname::with([
            'room', 'details', 
            'details.stock.material.classification', 
            'details.stock.material.uom',
            'details.stock.stock_details',
            'room.plant',
            'createdBy', 'updatedBy',
        ])->find($id);

        return $stock_opname;
    }

    public function multipleDelete()
    {
        Auth::user()->cekRoleModules(['stock-opname-update']);

        $data = [];
        foreach (request()->id as $key => $ids) {
            try {
                $ids = HashId::decode($ids);
            } catch(\Exception $ex) {
                return response()->json([
                    'message'   => 'Data invalid',
                    'errors'    => [
                        'id.'.$key  => ['id not found']
                    ]
                ], 422);
            }

            $data[] = $ids;
        }

        request()->merge(['id' => $data]);

        $this->validate(request(), [
            'id'          => 'required|array',
            'id.*'        => 'required|exists:stock_opnames,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = StockOpname::findOrFail($ids)->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Success delete data'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'error delete data',
                'detail' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);
        }
    }
}
