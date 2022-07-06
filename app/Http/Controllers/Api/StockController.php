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

use App\Helpers\HashId;

use App\Exports\StocksExport;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $stock = (new Stock)->newQuery();

        $stock->with(['material', 'room', 'stock_details']);
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

        if (request()->has('plant_id')) {
            $stock->whereHas('room', function($q) {
                $q->whereIn('plant_id', request()->input('plant_id'));
            });
        }

        if (request()->has('room_id')) {
            $stock->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('material_id')) {
            $stock->whereIn('material_id', request()->input('material_id'));
        }

        if (request()->input('ready_stock') === 'true') {
            $stock->where('stock', '>', 0);
        }

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $stock->whereHas('material', function ($data) use ($q) {
                $data->where(function($query) use ($q) {
	                $query->orWhere(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
	            });
            });

            $stock->orWhere(DB::raw("LOWER(stock)"), 'LIKE', "%".$q."%");
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

        $stock->with(['material', 'room', 'stock_details']);
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

        if (request()->has('plant_id')) {
            $stock->whereHas('room', function($q) {
                $q->whereIn('plant_id', request()->input('plant_id'));
            });
        }

        if (request()->has('room_id')) {
            $stock->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('material_id')) {
            $stock->whereIn('material_id', request()->input('material_id'));
        }

        if (request()->input('ready_stock') === 'true') {
            $stock->where('stock', '>', 0);
        }
        
        if (isset(request()->created_at[0]) && isset(request()->created_at[1])) {
            $created_at = request()->created_at;
            
            $stock->with(['stock_details' => function($q) use($created_at) {
                $start = trim($created_at[0], '"');
                $end   = trim($created_at[1], '"');

                $q->whereDate('created_at','>=',$start)
                    ->whereDate('created_at','<=',$end);
            }]);
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

        $stock->transform(function($item) {
            
            unset($item->stock);
            unset($item->quantity_in_transit);

            if(count($item->stock_details) > 0) {
                foreach($item->stock_details as $stock_detail) {
                    if (in_array($stock_detail->status, [1, 3])) {
                        $ready_stock[] = 1;
                    }
                    if (in_array($stock_detail->status, [2])) {
                        $in_transit[] = 1;
                    }
                }
            }
            
            $item->stock = isset($ready_stock) ? array_sum($ready_stock) : 0;
            $item->quantity_in_transit = isset($in_transit) ? array_sum($in_transit) : 0;

            return $item;
        });

        $stock = $stock->toArray();

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

    public function show($id)
    {
        Auth::user()->cekRoleModules(['stock-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $stock = Stock::with([
            'material', 'material.uom', 'material.classification',
            'room', 'room.plant',
            'createdBy', 'updatedBy',
        ])->find($id);

        return $stock;
    }

    public function export()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $this->validate(request(), [
            'id'          => 'required|array',
        ]);

        $data = [];
        foreach (request()->input('id') as $key => $ids) {
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
            'id.*'        => 'required|exists:stocks,id',
        ]);

        $stocks = (new Stock)->newQuery();

        $stocks->with(['material', 'room']);
        $stocks->with(['material.uom']);
        $stocks->with(['material.classification']);
        $stocks->with(['room.plant']);
        $stocks->with(['stock_details']);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $stocks->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $stocks->whereIn('plant_id', $plant_id);
        }

        if (request()->has('plant_id')) {
            $stocks->whereHas('room', function($q) {
                $q->whereIn('plant_id', request()->input('plant_id'));
            });
        }

        if (request()->has('room_id')) {
            $stocks->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('material_id')) {
            $stocks->whereIn('material_id', request()->input('material_id'));
        }

        if (request()->input('ready_stock') === 'true') {
            $stocks->where('stock', '>', 0);
        }
        
        if (isset(request()->created_at[0]) && isset(request()->created_at[1])) {
            $created_at = request()->created_at;
            
            $stocks->with(['stock_details' => function($q) use($created_at) {
                $start = trim($created_at[0], '"');
                $end   = trim($created_at[1], '"');

                $q->whereDate('created_at','>=',$start)
                    ->whereDate('created_at','<=',$end);
            }]);
        }

        $stocks->whereIn('id', request()->input('id'));
        
        $stocks = $stocks->get()->transform(function($item) {
            unset($item->stock);
            unset($item->quantity_in_transit);

            if(count($item->stock_details) > 0) {
                foreach($item->stock_details as $stock_detail) {
                    if (in_array($stock_detail->status, [1, 3])) {
                        $ready_stock[] = 1;
                    }
                    if (in_array($stock_detail->status, [2])) {
                        $in_transit[] = 1;
                    }
                }
            }
            
            $item->stock = isset($ready_stock) ? array_sum($ready_stock) : 0;
            $item->quantity_in_transit = isset($in_transit) ? array_sum($in_transit) : 0;

            return $item;
        });

        $contents = Excel::raw(new StocksExport('report.excel.stock', $stocks), \Maatwebsite\Excel\Excel::XLSX);
        
        $response = [
            'name' => 'Stocks.xlsx', 
            'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," .base64_encode($contents)
        ];
    
        return $response;
    }
}
