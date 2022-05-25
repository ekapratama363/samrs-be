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

        $stock->with(['material', 'room']);
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

        $stock->with(['material', 'room']);
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
        $stocks->with(['stock_histories']);

        $stocks->whereIn('id', request()->input('id'));
        
        $stocks = $stocks->get();

        $contents = Excel::raw(new StocksExport('report.excel.stock', $stocks), \Maatwebsite\Excel\Excel::XLSX);
        
        $response = [
            'name' => 'Stocks.xls', 
            'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," .base64_encode($contents)
        ];
    
        return $response;
    }
}
