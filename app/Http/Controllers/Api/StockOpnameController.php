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
use App\Models\Material;

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

    private function uniqueSerials($serials) 
    {
        $unique_serials = array_values(
            array_diff(
                $serials, 
                array_unique($serials)) + array_diff_assoc($serials, array_unique($serials)
            )
        );

        return $unique_serials;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['stock-opname-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'plant_id' => 'required|exists:plants,id',
            'room_id' => 'required|exists:rooms,id',
            'stocks' => 'required|array',
            'stocks.*.id'  => 'required|exists:stocks,id,deleted_at,NULL',
            'note'  => 'nullable',
        ]);

        $response['message'] = 'Data invalid';

        $stock_opname = StockOpname::with('details')->find($id);

        if (!in_array($stock_opname->status, [0, 1])) {
            $response['errors']['status'] = ['Stock Opname status must draft or waiting approve'];
            return response()->json($response, 422);
        }

        $material_actual_stocks = [];
        foreach($request->stocks as $material) {
            $material_actual_stocks[$material['id']] = $material;
        }

        $serials = [];
        foreach($request->serials as $serial) {
            $serials[$serial['id']][$serial['key']] = $serial;
        }

        foreach($material_actual_stocks as $stock_id => $material) {
            $material_actual_stocks[$stock_id]['require_serial_number'] = $material['material']['serial_number'];
            $material_actual_stocks[$stock_id]['serials'] = isset($serials[$stock_id]) ? $serials[$stock_id] : [];
        }

        $all_serials = [];
        foreach($material_actual_stocks as $stock_id => $material) {
            for ($i = 0; $i < $material['actual_stock']; $i++) {
                if (isset($material['serials'][$i]) && $material['serials'][$i]['val']) {
                    $all_serials[] = $material['serials'][$i]['val'];
                    $material_actual_stocks[$stock_id]['serials_valid'][] = $material['serials'][$i]['val'];
                }
            }
        }

        $response = $this->validationSerial($material_actual_stocks, $all_serials, $request);
        if (!$response['status']) {
            $response['message'] = 'Data invalid';
            return response()->json($response, 422);
        }

        if (count($all_serials) > 0) {
            $stock_opname_serials = StockOpnameSerial::with('stock_opname_detail.stock_opname')->whereIn('serial_number', $all_serials)->get();
            if (count($stock_opname_serials) > 0) {

                $stock_opname_details_id = [];
                foreach($stock_opname->details as $stock_opname_detail) {
                    $stock_opname_details_id[] = $stock_opname_detail->id;
                }

                $stock_opname_statuses = [];
                foreach($stock_opname_serials as $stock_opname_serial) {
                    $stock_opname_statuses[] = $stock_opname_serial->stock_opname_detail->stock_opname->status;
                }

                $allowed_status = array_intersect([0, 1], $stock_opname_statuses);
                foreach($stock_opname_serials as $stock_opname_serial) {
                    $stock_opname_statuses[] = $stock_opname_serial->stock_opname_detail->stock_opname->status;
                    if (
                        !in_array($stock_opname_serial->stock_opname_detail->id, $stock_opname_details_id)
                        &&
                        count($allowed_status) > 0
                    ) 
                    {
                        $response['errors']['material'] = [
                            "Serial number {$stock_opname_serial->serial_number} sudah digunakan oleh stock opname {$stock_opname_serial->stock_opname_detail->stock_opname->code}"
                        ];
                        return response()->json($response, 422);
                    }
                }
            }
        }

        DB::beginTransaction();
        try {

            $stock_opname->update([
                'note' => $request->note,
                'status' => 1, //waiting approve
                'updated_by' => Auth::user()->id
            ]);

            foreach($material_actual_stocks as $stock_id => $material) {

                $stock_opname_detail =  StockOpnameDetail::where('stock_opname_id', $id)
                    ->where('stock_id', $stock_id)->first();

                if ($stock_opname_detail) {
                    $stock_opname_detail->update([
                        'actual_stock' => $material['actual_stock'] ? $material['actual_stock'] : 0,
                        'serial_numbers' => isset($material['serials_valid']) ? json_encode($material['serials_valid']) : null,
                        'updated_by' => Auth::user()->id
                    ]);
                    
                    if (isset($material['serials_valid'])) {
                        foreach($material['serials_valid'] as $serial) {
                            $serials_delete[] = $serial;
                            StockOpnameSerial::updateOrCreate(
                                [
                                    'stock_opname_detail_id' => $stock_opname_detail->id,
                                    'serial_number' => $serial,
                                ],
                                [
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id
                                ]
                            );
                        }
                        StockOpnameSerial::where('stock_opname_detail_id', $stock_opname_detail->id)
                            ->whereNotIn('serial_number', $serials_delete)->delete();
                    } else if (
                        empty($material['serials_valid']) 
                        && 
                        $material['require_serial_number'] == 1
                    ) {
                        StockOpnameSerial::where('stock_opname_detail_id', $stock_opname_detail->id)->delete();
                    }
                }
            }

            $stock_opname->actual_stocks = $material_actual_stocks;

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

        if ($stock_opname->status != 1) { //waiting approve
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'status'  => ['Stock opname status must waiting approve']
                ]
            ], 422);
        }

        $stock_opname->update([
            'status' => 3, //reject
            'remark' => $request->remark
        ]);

        return $stock_opname;
    }

    private function validationSerial($material_actual_stocks, $all_serials, $request)
    {
        $response = ['status' => true];
        foreach($material_actual_stocks as $stock_id => $material) {
            if (
                $material['actual_stock'] > 0
                &&
                $material['require_serial_number'] == 1 
                && 
                empty($material['serials_valid'])
            ) {
                $response['status'] = false;
                $response['errors']['material'] = ['Serial number ' . $material['material']['material_code'] . ' tidak boleh kosong'];
            }

            if (
                $material['actual_stock'] > 0
                &&
                $material['require_serial_number'] == 1 
                && 
                count($material['serials_valid']) != $material['actual_stock']
            ) {
                $response['status'] = false;
                $response['errors']['material'] = ['Serial number ' . $material['material']['material_code'] . ' ada yang belum terisi'];
            }


            if (isset($material['serials_valid'])) {
                $duplicate_serials = $this->uniqueSerials($material['serials_valid']);
                if (isset($duplicate_serials[0])) {
                    $response['status'] = false;
                    $response['errors']['material'] = ['Duplicate Serial number '. $duplicate_serials[0]];
                }
            }
        }

        $duplicate_serials = $this->uniqueSerials($all_serials);;
        if (isset($duplicate_serials[0])) {
            $response['status'] = false;
            $response['errors']['material'] = ['Duplicate Serial number '. $duplicate_serials[0]];
        }

        if (count($all_serials) > 0) {
            $stock_details = StockDetail::with(['stock.material', 'stock.room'])->whereIn('serial_number', $all_serials)->get();
            if (count($stock_details) > 0) {
                foreach ($stock_details as $stock_detail) {
                    if ($stock_detail->stock->room_id != $request->room_id) {
                        $response['status'] = false;
                        $response['errors']['material'] = [
                            "Serial number {$stock_detail->serial_number} sudah digunakan {$stock_detail->stock->material->material_code} di {$stock_detail->stock->room->name}"
                        ];
                    }
    
                }
            }
        }

        return $response;
    }


    public function scan($id, Request $request)
    {
        Auth::user()->cekRoleModules(['stock-opname-approve']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'code' => 'required|exists:stock_details,code',
        ]);

        $response = [
            'status'  => false,
            'message' => 'Data Invalid'
        ];

        $code = explode('/', $request->code);

        $material_id = $code[1];
        $serial_number = isset($code[2]) ? $code[2] : null;

        $stock_opname_detail = StockOpnameDetail::with('stock.material')
            ->whereHas('stock', function($query) use($material_id) {
                $query->where('material_id', $material_id);
            })
            ->where('stock_opname_id', $id)
            ->first();

        if (!$stock_opname_detail) {
            $response['errors']['material'] = 'code material qrcode tidak valid';
            return $response;
        }

        DB::beginTransaction();
        try {

            $stock_opname_detail->update([
                'actual_stock' => $stock_opname_detail->actual_stock + 1,
                'total_scanned' => $stock_opname_detail->total_scanned + 1,
            ]);
    
            if ($serial_number) {
                StockOpnameSerial::updateOrCreate(
                    [
                        'stock_opname_detail_id' => $stock_opname_detail->id,
                        'serial_number' => $serial_number
                    ],
                    [
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]
                );
            }

            DB::commit();
            
            $stock_opname_detail->serial = $serial_number;
            return $stock_opname_detail;
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function approve($id, Request $request)
    {
        Auth::user()->cekRoleModules(['stock-opname-approve']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $response['message'] = 'Data invalid';

        $stock_opname = StockOpname::with([
            'room', 'details', 'details.serials',
            'details.stock.material.classification', 
            'details.stock.material.uom',
            'details.stock.stock_details',
            'room.plant',
            'createdBy', 'updatedBy',
        ])->find($id)
        ->toArray();

        if ($stock_opname['status'] != 1) {
            $response['errors']['status'] = ["Stock Opname status must waiting approve"];
            return $response->json($response, 422);
        }

        $material_actual_stocks = [];
        $serials = [];
        foreach($stock_opname['details'] as $material) {
            $material_actual_stocks[$material['stock']['id']] = $material;
            $material_actual_stocks[$material['stock']['id']]['require_serial_number'] = $material['stock']['material']['serial_number'];

            if (count($material['serials']) > 0) {
                foreach($material['serials'] as $serial_key => $serial) {
                    $serials[$material['stock']['id']][$serial_key] = $serial;
                }
            }
        }

        $all_serials = [];
        foreach($material_actual_stocks as $stock_id => $material) {
            for ($i = 0; $i < $material['actual_stock']; $i++) {
                if (isset($material['serials'][$i]) && $material['serials'][$i]['serial_number']) {
                    $all_serials[] = $material['serials'][$i]['serial_number'];
                    $material_actual_stocks[$stock_id]['serials_valid'][] = $material['serials'][$i]['serial_number'];
                }
            }
        }

        $response = $this->validationSerial($material_actual_stocks, $all_serials, $request);
        if (!$response['status']) {
            return response()->json($response, 422);
        }

        DB::beginTransaction();
        try {
            foreach($material_actual_stocks as $stock_id => $material) {
                Stock::where('id', $stock_id)
                    ->where('material_id', $material['stock']['material_id'])
                    ->update([
                        'stock' => $material['actual_stock'] ? $material['actual_stock'] : 0
                    ]);

                StockDetail::where('stock_id', $stock_id)->delete();

                if (isset($material['serials_valid'])) {
                    foreach($material['serials_valid'] as $serial) {
                        StockDetail::create([
                            'code' => 'MT/' . $material['stock']['material_id'] . '/' . $serial,
                            'stock_id' => $stock_id,
                            'serial_number' => $serial,
                            'status' => 3, //stock opname
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                } else {
                    for($i = 0; $i < $material['actual_stock']; $i++) {
                        StockDetail::create([
                            'code' => 'MT/' . $material['stock']['material_id'],
                            'stock_id' => $stock_id,
                            'serial_number' => null,
                            'status' => 3, //stock opname
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                }
            }

            StockOpname::find($id)->update(['status' => 2]);//approved 

            DB::commit();
    
            return $material_actual_stocks;

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
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
