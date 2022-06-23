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
use PDF;

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

    public function approve($id, Request $request)
    {
        Auth::user()->cekRoleModules(['good-receive-approve']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $gr = DeliveryOrder::with('reservation.details')->find($id);

        if (!$gr) {
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'status'  => ['GR not found']
                ]
            ], 404);
        }

        if ($gr->status != 0) { //waiting approve
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'status'  => ['GR status must waiting approve']
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {

            foreach($gr->details as $detail) {
                if ($detail->status == 0) { //delivery
                    $gr_details_id[] = $detail->id;
                }
            } 

            $gr->update(['status' => 1]); //received
    
            foreach($gr->reservation->details as $detail) {
                $stock = Stock::where('material_id', $detail->material_id)
                    ->where('room_id', $gr->reservation->room_id)
                    ->first();

                $stock->update([
                    'stock' => $stock->stock + $detail->delivery_quantity,
                    'quantity_in_transit' => $stock->quantity_in_transit - $detail->delivery_quantity,
                ]);

                $stock_details = StockDetail::where('stock_id', $stock->id)
                    ->where('status', 2) //in transit
                    ->whereIn('delivery_order_detail_id', $gr_details_id)
                    ->get();

                foreach($stock_details as $stock_detail) {
                    $material_id = explode('/', $stock_detail->code)[1];
                    if ($detail->material_id == $material_id) {
                        $stock_details_id[] = $stock_detail->id;
                    }
                }
            }

            StockDetail::whereIn('id', $stock_details_id)
                ->update([
                    'status' => 1,// delivered,
                    'updated_by' => Auth::user()->id
                ]);

            DB::commit();

            return $gr;
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message'   => $th->getMessage(),
            ], 422);
        }
    }

    public function reject($id, Request $request)
    {
        Auth::user()->cekRoleModules(['good-receive-reject']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $gr = DeliveryOrder::find($id);

        if (!$gr) {
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'status'  => ['GR not found']
                ]
            ], 404);
        }

        if ($gr->status != 0) { //waiting approve
            return response()->json([
                'message'   => 'Data invalid',
                'errors'    => [
                    'status'  => ['GR status must waiting approve']
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {

            foreach($gr->details as $detail) {
                if ($detail->status == 0) { //delivery
                    $gr_details_id[] = $detail->id;
                }
            } 

            $gr->update([
                'status' => 2,
                'note' => $request->remark,
                'updated_by' => Auth::user()->id
            ]); //rejected
    
            foreach($gr->reservation->details as $detail) {
                $stock = Stock::where('material_id', $detail->material_id)
                    ->where('room_id', $gr->reservation->room_id)
                    ->first();

                $stock->update([
                    'quantity_in_transit' => $stock->quantity_in_transit - $detail->delivery_quantity,
                ]);

                $stock_details = StockDetail::where('stock_id', $stock->id)
                    ->where('status', 2) //in transit
                    ->whereIn('delivery_order_detail_id', $gr_details_id)
                    ->get();

                foreach($stock_details as $stock_detail) {
                    $material_id = explode('/', $stock_detail->code)[1];
                    if ($detail->material_id == $material_id) {
                        $stock_details_id[] = $stock_detail->id;
                    }
                }
            }

            StockDetail::whereIn('id', $stock_details_id)->delete();

            DB::commit();

            return $gr;
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message'   => $th->getMessage(),
            ], 422);
        }
    }

    public function pdf($code)
    {
        $do = DeliveryOrder::with([
            'reservation.room_sender', 
            'reservation.room_receiver', 
            'reservation.vendor',
            'reservation.plant',
            'reservation.room_sender.plant',
            'reservation.room_sender.responsible',
            'reservation.room_receiver.plant',
            'reservation.room_receiver.responsible',
            'reservation.details',
            'reservation.details.do_detail.serial_numbers',
            'reservation.details.material',
            'reservation.details.material.uom',
            'reservation.createdBy',
            'reservation.updatedBy',
            'details'
        ])
        ->where('code', $code)
        ->where('status', 1) //delivered
        ->first();

        if (count($do->reservation->details) > 0) {
            foreach($do->reservation->details as $index => $detail) {
                $detail->material_code = $detail->material->material_code;
                $detail->description = $detail->material->description;
                $detail->uom = $detail->material->uom;
            }
        }

        $data['do'] = $do;
        $pdf = PDF::chunkLoadView("<html-separator/>", "report.pdf.good_receives", $data, [], ['orientation' => 'L']);
        $pdf->getMpdf()->setFooter("Page {PAGENO} of {nb}");
        return $pdf->stream("good_receives_$code.pdf");
    }
}
