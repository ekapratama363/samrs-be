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

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderDetail;
use App\Models\DeliveryOrderSerial;
use App\Models\Reservation;
use App\Models\ReservationDetail;
use App\Models\StockDetail;
use App\Models\Stock;

use App\Helpers\HashId;

class DeliveryOrderController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['delivery-order-view']);

        $delivery_order = (new DeliveryOrder)->newQuery();

        $delivery_order->with('reservation');

        $delivery_order->with([
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
            $delivery_order->whereHas('reservation', function($query) use($room_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $delivery_order->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $plant_id);
            });
        }

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);
        if (count($vendor_id) > 0) {
            $delivery_order->whereHas('reservation', function($query) use($vendor_id) {
                $query->whereIn('vendor_id', $vendor_id);
            });
        }

        if (request()->has('code_do')) {
            $delivery_order->whereIn('code', request()->input('code_do'));
        }

        if (request()->has('code')) {
            $code = request()->input('code');
            $delivery_order->whereHas('reservation', function($query) use($code) {
                $query->whereIn('code', $code);
            });
        }

        if (request()->has('plant_id')) {
            $plant_id = request()->input('plant_id');
            $delivery_order->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $plant_id);
            });
        }

        if (request()->has('room_id')) {
            $room_id = request()->input('room_id');
            $delivery_order->whereHas('reservation', function($query) use($room_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        if (request()->has('vendor_id')) {
            $vendor_id = request()->input('vendor_id');
            $delivery_order->whereHas('reservation', function($query) use($vendor_id) {
                $query->whereIn('vendor_id', $vendor_id);
            });
        }

        if (request()->has('status')) {
            $delivery_order->whereIn('status', request()->input('status'));
        }

        if (request()->has('created_at')) {
            $start = trim(request()->created_at[0], '"');
            $end   = trim(request()->created_at[1], '"');
            $delivery_order->whereBetween('created_at', [$start, $end]);
        }

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $delivery_order->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $delivery_order->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $delivery_order->orderBy('id', 'desc');
        }

        $delivery_order = $delivery_order->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $delivery_order;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['delivery-order-view']);

        $delivery_order = (new DeliveryOrder)->newQuery();

        $delivery_order->with('reservation');

        $delivery_order->with([
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
            $delivery_order->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $delivery_order->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $room_id);
            });
        }

        if (request()->has('code_do')) {
            $delivery_order->whereIn('code', request()->input('code_do'));
        }

        if (request()->has('code')) {
            $code = request()->input('code');
            $delivery_order->whereHas('reservation', function($query) use($code) {
                $query->whereIn('code', $code);
            });
        }

        if (request()->has('plant_id')) {
            $plant_id = request()->input('plant_id');
            $delivery_order->whereHas('reservation', function($query) use($plant_id) {
                $query->whereIn('plant_id', $plant_id);
            });
        }

        if (request()->has('room_id')) {
            $room_id = request()->input('room_id');
            $delivery_order->whereHas('reservation', function($query) use($room_id) {
                $query->whereIn('room_id', $room_id);
            });
        }

        if (request()->has('vendor_id')) {
            $vendor_id = request()->input('vendor_id');
            $delivery_order->whereHas('reservation', function($query) use($vendor_id) {
                $query->whereIn('vendor_id', $vendor_id);
            });
        }

        if (request()->has('created_at')) {
            $start = trim(request()->created_at[0], '"');
            $end   = trim(request()->created_at[1], '"');
            $delivery_order->whereBetween('created_at', [$start, $end]);
        }

        if (request()->has('q') && request()->input('q')) {
            $q = strtolower(request()->input('q'));
            $delivery_order->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $delivery_order->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $delivery_order->orderBy('id', 'desc');
        }

        $delivery_order = $delivery_order->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($delivery_order['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $delivery_order['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $delivery_order;
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['delivery-order-view']);

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
    
    public function process($reservation_id, Request $request)
    {
        Auth::user()->cekRoleModules(['delivery-order-process']);

        $response = [
            'message' => 'Data invalid',
            'status' => false
        ];

        try {
            $id = HashId::decode($reservation_id);
        } catch(\Exception $ex) {
            $response['message'] = 'ID is not valid. ERROR:'.$ex->getMessage();
            return response()->json($response, 400);
        }

        $reservation = Reservation::find($id);

        if (!$reservation) {
            $response['errors']['materials'] = 'Reservation not found';
            return response()->json($response, 400);
        }

        $this->validate(request(), [
            'materials'                     => 'required|array',
            'materials.*.material_id'       => 'required|exists:materials,id',
            // 'materials.*.delivery_quantity' => 'required|numeric|min:1',
        ]);

        $delivery_orders = [];
        foreach($request->materials as $material) {
            $delivery_orders[$material['id']] = $material;
            $delivery_orders[$material['id']]['require_serial_number'] = $material['material']['serial_number'];
        }

        $serials = [];
        foreach($request->serials as $index => $serial) {
            $serials[$serial['id']][$serial['key']] = $serial['val'];
        }

        $all_serials = [];
        foreach($serials as $serial_number) {
            foreach($serial_number as $serial) {
                $all_serials[] = $serial;
            }
        }

        foreach($delivery_orders as $index => $delivery_order) {
            $delivery_orders[$index]['serials'] = isset($serials[$delivery_order['id']]) ? $serials[$delivery_order['id']] : [];
        }

        $validation = $this->validation($delivery_orders, $reservation, $all_serials);

        if (!$validation['status']) {
            return response()->json($validation, 400);
        }

        DB::beginTransaction();
        try {

            $do = DeliveryOrder::create([
                'code'           => 'DO/' . date('ymd/His'),
                'reservation_id' => $reservation->id,
                'note'           => $request->note,
                'status'         => 0, //waiting received
                'created_by'     => Auth::user()->id,
                'updated_by'     => Auth::user()->id
            ]);

            foreach($delivery_orders as $material) { 

                $do_detail = DeliveryOrderDetail::create([
                    'delivery_order_id' => $do->id,
                    'reservation_detail_id' => $material['id'],
                    'status' => 0, //dikirim
                    'created_by'          => Auth::user()->id,
                    'updated_by'          => Auth::user()->id
                ]);

                ReservationDetail::where('id', $material['id'])
                    ->update(['delivery_quantity' => $material['delivery_quantity']]);

                if ($reservation->type == 0) { //request to vendor
                    $sender_stock = Stock::select('id')
                        ->where('material_id', $material['material_id'])
                        ->where('room_id', $reservation->room_sender)
                        ->first();

                    $receive_stock = Stock::where('material_id', $material['material_id'])
                        ->where('room_id', $reservation->room_id)
                        ->first();

                    if ($receive_stock) { //jika id material sudah ada di table stock
                        $receive_stock->update([
                            'quantity_in_transit' => $receive_stock->quantity_in_transit + $material['delivery_quantity'],
                            'updated_by'          => Auth::user()->id
                        ]); 
                    } else { //jika id material belum ada di table stock
                        $receive_stock = Stock::create([
                            'material_id'         => $material['material_id'],
                            'room_id'             => $reservation->room_id,
                            'quantity_in_transit' => $material['delivery_quantity'],
                            'created_by'          => Auth::user()->id,
                            'updated_by'          => Auth::user()->id
                        ]);
                    }

                    if (count($material['serials']) > 0) {
                        foreach($material['serials'] as $serial) { //jika material punya serial, makan buat stock detail pakai serial
                            StockDetail::create([
                                'code'          => 'MT/' . $material['material_id'] . '/' . $serial,
                                'stock_id'      => $receive_stock->id,
                                'serial_number' => $serial,
                                'status'        => 2, //in transit
                                'created_by'    => Auth::user()->id,
                                'updated_by'    => Auth::user()->id
                            ]);

                            DeliveryOrderSerial::create([
                                'delivery_order_detail_id' => $do_detail->id,
                                'serial_number' => $serial,
                                'created_by'    => Auth::user()->id,
                                'updated_by'    => Auth::user()->id
                            ]);
                        }
                    } else {
                        for($i = 0; $i < $material['delivery_quantity']; $i++) {
                            StockDetail::create([ //jika material tidak punya serial, makan buat stock detail tanpa serial
                                'code' => 'MT/' . $material['material_id'],
                                'serial_number' => null,
                                'stock_id' => $receive_stock->id,
                                'status'   => 2, //in transit
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                    }
                } 
            }

            DB::commit();

            return $delivery_orders;
        } catch (\Throwable $th) {
            DB::rollback();
            $response['message'] = $th->getMessage();
            return response()->json($response, 400);
        }
    }

    private function processStockOut($material, $reservation)
    {
        $sender_stock = Stock::where('material_id', $material['material_id'])
            ->where('room_id', $reservation->room_sender)
            ->first();

        $sender_stock->update([
            'stock' => $sender_stock->stock - $material['delivery_quantity']
        ]);
    }

    private function processStockIn($material, $reservation)
    {
        $sender_stock = Stock::select('id')
            ->where('material_id', $material['material_id'])
            ->where('room_id', $reservation->room_sender)
            ->first();

        $receive_stock = Stock::where('material_id', $material['material_id'])
            ->where('room_id', $reservation->room_id)
            ->first();

        if ($receive_stock) {
            $receive_stock->update([
                'quantity_in_transit' => $receive_stock->quantity_in_transit + $material['delivery_quantity'],
                'updated_bys'    => Auth::user()->id
            ]); 
        } else {
            $receive_stock = Stock::create([
                'material_id' => $material['material_id'],
                'room_id' => $reservation->room_id,
                'quantity_in_transit' => $material['delivery_quantity'],
                'created_by'    => Auth::user()->id,
                'updated_bys'    => Auth::user()->id
            ]);
        }

        if (count($material['serials']) > 0) {
            foreach($material['serials'] as $serial) {
                // if ($reservation->type > 0) { //bukan request ke vendor
                //     StockDetail::where('stock_id', $sender_stock->id)
                //         ->where('serial_number', $serial)
                //         ->update([
                //             'stock_id' => $receive_stock->id,
                //             'status'   => 2, //in transit
                //             'updated_by'    => Auth::user()->id
                //         ]);
                // } 
                // else {
                    StockDetail::create([
                        'code'          => 'MT/' . $material['material_id'] . '/' . $serial,
                        'stock_id'      => $receive_stock->id,
                        'serial_number' => $serial,
                        'status'        => 2, //in transit
                        'created_by'    => Auth::user()->id,
                        'updated_by'    => Auth::user()->id
                    ]);
                // }
            }
        } else {
            for($i = 0; $i < $material['delivery_quantity']; $i++) {
                // if ($reservation->type > 0) { //bukan request ke vendor
                //     StockDetail::where('stock_id', $sender_stock->id)
                //         ->update([
                //             'stock_id' => $receive_stock->id,
                //             'status'   => 2, //in transit
                //             'updated_by'    => Auth::user()->id
                //         ]);
                // } else {
                    StockDetail::create([
                        'code' => 'MT/' . $material['material_id'],
                        'serial_number' => null,
                        'stock_id' => $receive_stock->id,
                        'status'   => 2, //in transit
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                // }
            }
        }
    }

    private function validation($delivery_orders, $reservation, $all_serials)
    {
        $response = [
            'message' => 'Data invalid',
            'status' => false
        ];

        $duplicate_serials = $this->uniqueSerials($all_serials);
        if (isset($duplicate_serials[0])) {
            $response['errors']['material'] = ['duplicate serial number '. $duplicate_serials[0]];
            return $response;
        }

        $index = 1;
        foreach($delivery_orders as $material) {
            $reservation_details = ReservationDetail::with('material')
                ->where('reservation_id', $reservation->id)
                ->where('material_id', $material['material_id'])->first();

            if (!$reservation_details) {
                $response['errors']['materials.' . $index] = ['material ke ' . $index. ', material tidak terdaftar pada reservasi ' . $reservation->code];
                return $response;
            }

            // if ($reservation->type > 0) { //bukan request ke vendor
            //     $stock = Stock::with(['material', 'room'])->where('material_id', $material['material_id'])
            //         ->where('room_id', $reservation->room_sender)->first();

            //     if (!$stock) {
            //         $response['errors']['materials.' . $index] = [
            //             $stock->material->material_code . 'tidak dimiliki oleh ' . $stock->room->name
            //         ];
            //         return $response;
            //     }
                
            //     if ($stock->stock < $material['delivery_quantity']) {
            //         $response['errors']['materials.' . $index] = [
            //             'stock ' . $stock->material->material_code . ' hanya tersedia ' . $stock->stock . ' tidak mencukupi untuk dikirim ' . $material['delivery_quantity']
            //         ];
            //         return $response;
            //     }
            // }

            if ($material['delivery_quantity'] < $reservation_details->quantity) {
                $response['errors']['materials.' . $index] = ['material ke ' . $index. ', delivery quantity tidak boleh kurang dari ' . $reservation_details->quantity];
                return $response;
            }

            if ($material['delivery_quantity'] > $reservation_details->quantity) {
                $response['errors']['materials.' . $index] = ['material ke ' . $index. ', delivery quantity tidak boleh lebih besar dari ' . $reservation_details->quantity];
                return $response;
            }

            if (
                $reservation_details->material->serial_number == 1 
                && 
                count($material['serials']) < $material['delivery_quantity']
            ) {
                $response['errors']['materials.' . $index] = ['material ke ' . $index. ', serial yang dipilih kurang dari ' . $material['delivery_quantity']];
                return $response;
            }

            if (
                $reservation_details->material->serial_number == 1 
                && 
                count($material['serials']) > $material['delivery_quantity']
            ) {
                $response['errors']['materials.' . $index] = ['material ke ' . $index. ', serial yang dipilih tidak boleh lebih dari ' . $material['delivery_quantity'] . ' jenis'];
                return $response;
            }

            if (count($material['serials']) > 0) {
                $duplicate_serials = $this->uniqueSerials($material['serials']);
                if (isset($duplicate_serials[0])) {
                    $response['errors']['material.' . $index] = ['material ke ' . $index. ', duplicate serial number '. $duplicate_serials[0]];
                    return $response;
                }
            }

            if (
                $reservation_details->material->serial_number == 1 
                && 
                count($material['serials']) > 0
            ) {
                foreach($material['serials'] as $serial) {
                    $stock_details = StockDetail::with(['stock', 'stock.room', 'stock.material'])->where('serial_number', $serial)->first();
                    if ($reservation->type == 0 && $stock_details) {
                        $response['errors']['materials.' . $index] = ['serial ' . $serial . ' sudah pernah digunakan, silahkan input serial lain'];
                        return $response;
                    }

                    //  else if ($reservation->type > 0) { // bukan request ke vendor
                    //     if (!$stock_details) {
                    //         $response['errors']['materials.' . $index] = [
                    //             'serial ' . $serial . ' tidak ditemukan pada stock material pengirim'
                    //         ];
                    //         return $response;
                    //     }
                    //     if ($stock_details && $stock_details->stock->room_id != $reservation->room_sender) {
                    //         return $response;
                    //     }
                    // }
                }
            }

            $index++;
        }

        return $response = [
            'status' => true,
            'message' => null,
        ];
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
}
