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
use App\Models\StockDetail;

use App\Helpers\HashId;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;

class StockDetailController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $stock_detail = (new StockDetail)->newQuery();

        $stock_detail->with(['stock']);
        $stock_detail->with(['stock.material']);

        if (request()->has('status')) {
            $stock->whereIn('status', request()->input('status'));
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock_detail->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(serial_number)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_detail->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_detail->orderBy('id', 'desc');
        }

        $stock_detail = $stock_detail->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $stock_detail;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['stock-view']);

        $stock_detail = (new StockDetail)->newQuery();

        $stock_detail->with(['stock']);
        $stock_detail->with(['stock.material']);

        if (request()->has('status')) {
            $stock->whereIn('status', request()->input('status'));
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $stock_detail->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(serial_number)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_detail->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_detail->orderBy('id', 'desc');
        }

        $stock_detail = $stock_detail->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($stock_detail['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $stock_detail['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $stock_detail;
    }

    public function StockDetailByStockId($stock_id)
    {
        Auth::user()->cekRoleModules(['stock-view']);

        try {
            $stock_id = HashId::decode($stock_id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $stock_detail = (new StockDetail)->newQuery();

        $stock_detail->where('stock_id', $stock_id);
        $stock_detail->whereIn('status', [1, 3]);

        $stock_detail->with(['stock']);
        $stock_detail->with(['stock.material']);

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $stock_detail->where(DB::raw("LOWER(serial_number)"), 'LIKE', "%".$q."%");

            $stock_detail->orWhereHas('stock.material', function($query) use($q) {
                $query->where(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $stock_detail->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $stock_detail->orderBy('id', 'desc');
        }

        $stock_detail = $stock_detail->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($stock_detail['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $stock_detail['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $stock_detail;
    }

    public function qrcode()
    {
        // Auth::user()->cekRoleModules(['stock-view']);

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
            'size'        => 'required|in:m24,s24',
        ]);

        $stock_details = (new StockDetail)->newQuery();

        $stock_details->whereIn('id', request()->input('id'));

        $stock_details->with(['stock']);
        $stock_details->with(['stock.material']);
        $stock_details->with(['stock.material.classification']);
        $stock_details->with(['stock.room']);
        
        $stock_details = $stock_details->get();

        $data['stock_details'] = $stock_details;
        $data['remove_text'] = '<?xml version="1.0" encoding="UTF-8"?>';

        $size = request()->input('size');
        $pdf = PDF::loadView("report.pdf.qrcode_$size" , $data);
        return $pdf->stream('document.pdf');
    }

    public function getStockDetailSerialNumberByStockId($stock_id)
    {
        Auth::user()->cekRoleModules(['stock-view']);

        try {
            $stock_id = HashId::decode($stock_id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $stock_detail = (new StockDetail)->newQuery();

        $stock_detail->select('serial_number');

        $stock_detail->where('stock_id', $stock_id);

        if (request()->has('status')) {
            $stock_detail->whereIn('status', request()->input('status'));
        }

        $stock_detail->orderBy('id', 'desc');

        $stock_detail = $stock_detail->get();

        return $stock_detail;
    }
}
