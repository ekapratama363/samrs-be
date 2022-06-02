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
use App\Models\StockOpnameSerial;
use App\Models\StockDetail;
use App\Models\Stock;

use App\Helpers\HashId;

class StockOpnameController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['stock-opname-view']);

        $stock_opname = (new StockOpname)->newQuery();

        $stock_opname->with(['details.serials', 'room', 'createdBy', 'updatedBy']);
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

        $stock_opname->with(['details.serials', 'room', 'createdBy', 'updatedBy']);
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
            'note'  => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $save = StockOpname::create([
                'code' => 'SO/' . date('ymd/His'),
                'room_id' => $request->room_id,
                'note' => $request->note,
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
            'note'  => 'nullable',
        ]);

        $stock_opname = StockOpname::find($id);

        if (!in_array($stock_opname->status, [0, 1])) {
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'material'  => ["Stock Opname status must draft or waiting approve"]
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {

            $actual_stocks = [];
            foreach($request->stocks as $stock) {
                $actual_stocks[$stock['id']] = $stock['actual_stock'] > 0 ? range(0, $stock['actual_stock'] - 1) : [];
            }

            $serials = [];
            $serials_value = [];
            foreach($request->serials as $serial) {
                $serials_value[$serial['id']][$serial['key']] = $serial['val'];
                $serials[$serial['id']][$serial['key']] = $serial;
            }

            $duplicate_serials = [];
            foreach($serials_value as $value) {
                foreach($value as $v) {
                    $duplicate_serials[] = $v;
                }
            }

            if (count($duplicate_serials) != count(array_unique($duplicate_serials))) {

                $duplicate_value = array_values(
                    array_diff(
                        $duplicate_serials, 
                        array_unique($duplicate_serials)) + array_diff_assoc($duplicate_serials, array_unique($duplicate_serials)
                    )
                );
                
                return response()->json([
                    'message'   => 'Data invalid',
                    'errors'    => [
                        'material'  => ["Duplicate Serial number {$duplicate_value[0]}"]
                    ]
                ], 422);
            }

            $serial_stocks = [];
            foreach($actual_stocks as $stock_id => $stock) {

                $get_stock = Stock::with('material')->find($stock_id);

                if (
                    empty($serials[$stock_id]) 
                    && 
                    count($actual_stocks[$stock_id]) > 0
                    && 
                    $get_stock->material->serial_number == 1
                ) {
                    return response()->json([
                        'message'   => 'Data invalid',
                        'errors'    => [
                            'material'  => ["Serial number for {$get_stock->material->material_code} cannot be empty"]
                        ]
                    ], 422);
                }

                // if (
                //     isset($serials[$stock_id])
                //     && 
                //     count($serials[$stock_id]) 
                //     != 
                //     count($actual_stocks[$stock_id])
                // ) {
                //     return response()->json([
                //         'message'   => 'Data invalid',
                //         'errors'    => [
                //             'material'  => ["Serial number for {$get_stock->material->material_code} cannot be empty"]
                //         ]
                //     ], 422);
                // }

                foreach(array_keys($stock) as $position_serial) {

                    if (isset($serials[$stock_id][$position_serial])) {
                        $serial = $serials[$stock_id][$position_serial]['val'];

                        // jika serial == ''
                        if (!$serial) {
                            return response()->json([
                                'message'   => 'Data invalid',
                                'errors'    => [
                                    'material'  => ["Serial number for {$get_stock->material->material_code} cannot be empty"]
                                ]
                            ], 422);
                        }

                        $serial_exist_stock = StockDetail::with(['stock.material', 'stock.room'])->where('serial_number', $serial)->first();

                        if ($serial_exist_stock) {
                            if ($serial_exist_stock->stock->room_id != $request->room_id) {
                                return response()->json([
                                    'message'   => 'Data invalid',
                                    'errors'    => [
                                        'material'  => [
                                            "Serial number {$serial} has been used by {$serial_exist_stock->stock->material->material_code} in {$serial_exist_stock->stock->room->name}"
                                        ]
                                    ]
                                ], 422);
                            }
                        }

                        $serial_stocks[$stock_id][$position_serial] = $serial;
                    } 
                }
            }

            $stock_opname = StockOpname::find($id);

            $stock_opname->update([
                'note' => $request->note,
                'status' => 1, //waiting approve
                'updated_by' => Auth::user()->id
            ]);

            foreach($actual_stocks as $key => $stock) {

                $stock_opname_detail =  StockOpnameDetail::where('stock_opname_id', $id)
                    ->where('stock_id', $key)->first();

                if ($stock_opname_detail) {
                    $stock_opname_detail->update([
                        'actual_stock' => isset($actual_stocks[$key]) ? count($actual_stocks[$key]) : 0,
                        'serial_numbers' => isset($serial_stocks[$key]) ? json_encode($serial_stocks[$key]) : null,
                        'updated_by' => Auth::user()->id
                    ]);
                    
                    if (isset($serial_stocks[$key])) {

                        foreach($serial_stocks[$key] as $serial) {
                            $serial_exist_stock_opname = StockOpnameSerial::with('stock_opname_detail.stock_opname')->where('serial_number', $serial)->first();
                            
                            if (
                                $serial_exist_stock_opname 
                                && 
                                ($serial_exist_stock_opname->stock_opname_detail->id != $stock_opname_detail->id) 
                                &&
                                (in_array($serial_exist_stock_opname->stock_opname_detail->stock_opname->status, [0, 1]))
                            ) {
                                    return response()->json([
                                    'message'   => 'Data invalid',
                                    'errors'    => [
                                        'material'  => [
                                            "Serial number {$serial} has been used by Stock Opname {$serial_exist_stock_opname->stock_opname_detail->stock_opname->code}"
                                        ]
                                    ]
                                ], 422);
                            }

                            StockOpnameSerial::updateOrCreate(
                                [
                                    'stock_opname_detail_id' => $stock_opname_detail->id,
                                    'serial_number' => $serial
                                ],
                                [
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id
                                ]
                            );
                        }

                        StockOpnameSerial::where('stock_opname_detail_id', $stock_opname_detail->id)
                            ->whereNotIn('serial_number', $serial_stocks[$key])->delete();
                    }
                }
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
            'room', 'details', 'details.serials',
            'details.stock.material.classification', 
            'details.stock.material.uom',
            'details.stock.stock_details',
            'room.plant',
            'createdBy', 'updatedBy',
        ])->find($id);

        return $stock_opname;
    }

    public function reject($id, Request $request)
    {
        Auth::user()->cekRoleModules(['stock-opname-reject']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $stock_opname = StockOpname::find($id);

        $stock_opname->update([
            'status' => 3, //reject
            'remark' => $request->remark
        ]);

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
