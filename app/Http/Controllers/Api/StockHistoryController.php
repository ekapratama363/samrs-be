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
use App\Models\StockHistory;

use App\Helpers\HashId;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;

class StockHistoryController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $stock_hitory = (new StockHistory)->newQuery();

        $stock_hitory->with(['stock']);
        $stock_hitory->with(['stock.material']);

        if (request()->has('status')) {
            $stock->whereIn('status', request()->input('status'));
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock_hitory->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(serial_number)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_hitory->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_hitory->orderBy('id', 'desc');
        }

        $stock_hitory = $stock_hitory->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $stock_hitory;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $stock_hitory = (new StockHistory)->newQuery();

        $stock_hitory->with(['stock']);
        $stock_hitory->with(['stock.material']);

        if (request()->has('status')) {
            $stock->whereIn('status', request()->input('status'));
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock_hitory->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(serial_number)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_hitory->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_hitory->orderBy('id', 'desc');
        }

        $stock_hitory = $stock_hitory->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($stock_hitory['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $stock_hitory['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $stock_hitory;
    }

    public function stockHistoryByStockId($stock_id)
    {
        Auth::user()->cekRoleModules(['stock-view']);

        try {
            $stock_id = HashId::decode($stock_id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $stock_hitory = (new StockHistory)->newQuery();

        $stock_hitory->where('stock_id', $stock_id);
        $stock_hitory->whereIn('status', [1, 3]);

        $stock_hitory->with(['stock']);
        $stock_hitory->with(['stock.material']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock_hitory->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(serial_number)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_hitory->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_hitory->orderBy('id', 'desc');
        }

        $stock_hitory = $stock_hitory->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($stock_hitory['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $stock_hitory['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $stock_hitory;
    }

    public function pdf()
    {
        // Auth::user()->cekRoleModules(['stock-view']);

        // $this->validate(request(), [
        //     'id'          => 'required|array',
        // ]);

        // $data = [];
        // foreach (request()->input('id') as $key => $ids) {
        //     try {
        //         $ids = HashId::decode($ids);
        //     } catch(\Exception $ex) {
        //         return response()->json([
        //             'message'   => 'Data invalid',
        //             'errors'    => [
        //                 'id.'.$key  => ['id not found']
        //             ]
        //         ], 422);
        //     }

        //     $data[] = $ids;
        // }

        // request()->merge(['id' => $data]);

        $this->validate(request(), [
            'id'          => 'required|array',
            'id.*'        => 'required|exists:stocks,id',
            'size'        => 'required|in:m24,s24',
        ]);

        $stock_histories = (new StockHistory)->newQuery();

        $stock_histories->whereIn('id', request()->input('id'));

        $stock_histories->with(['stock']);
        $stock_histories->with(['stock.material']);
        $stock_histories->with(['stock.material.classification']);
        $stock_histories->with(['stock.room']);
        
        $stock_histories = $stock_histories->get();

        $data['stock_histories'] = $stock_histories;
        $data['remove_text'] = '<?xml version="1.0" encoding="UTF-8"?>';

        $size = request()->input('size');
        $pdf = PDF::loadView("report.pdf.qrcode_$size" , $data);
        return $pdf->stream('document.pdf');
    }

    public function getStockHistorySerialNumberByStockId($stock_id)
    {
        Auth::user()->cekRoleModules(['stock-view']);

        try {
            $stock_id = HashId::decode($stock_id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $stock_hitory = (new StockHistory)->newQuery();

        $stock_hitory->select('serial_number');

        $stock_hitory->where('stock_id', $stock_id);

        if (request()->has('status')) {
            $stock_hitory->whereIn('status', request()->input('status'));
        }

        $stock_hitory->orderBy('id', 'desc');

        $stock_hitory = $stock_hitory->get();

        return $stock_hitory;
    }
}
