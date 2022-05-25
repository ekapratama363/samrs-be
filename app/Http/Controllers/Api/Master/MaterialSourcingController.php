<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;

use App\Models\Stock as MaterialSourcing;

use App\Helpers\HashId;

class MaterialSourcingController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['material-sourcing-view']);

        $material_sourcing = (new MaterialSourcing)->newQuery();

        $material_sourcing->with(['material', 'room']);
        $material_sourcing->with(['material.uom']);
        $material_sourcing->with(['material.classification']);
        $material_sourcing->with(['room.plant']);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $material_sourcing->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $material_sourcing->whereIn('plant_id', $plant_id);
        }

        if (request()->has('plant_id')) {
            $material_sourcing->whereHas('room', function($q) {
                $q->whereIn('plant_id', request()->input('plant_id'));
            });
        }

        if (request()->has('room_id')) {
            $material_sourcing->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('created_at')) {
            $start = trim(request()->created_at[0], '"');
            $end   = trim(request()->created_at[1], '"');
            $material_sourcing->whereBetween('created_at', [$start, $end]);
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $material_sourcing->whereHas('material', function ($data) use ($q) {
                $data->where(function($query) use ($q) {
	                $query->orWhere(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
	            });
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $material_sourcing->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $material_sourcing->orderBy('id', 'desc');
        }

        $material_sourcing = $material_sourcing->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $material_sourcing;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['material-sourcing-view']);

        $material_sourcing = (new MaterialSourcing)->newQuery();

        $material_sourcing->with(['material', 'room']);
        $material_sourcing->with(['material.uom']);
        $material_sourcing->with(['material.classification']);
        $material_sourcing->with(['room.plant']);

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);
        if (count($room_id) > 0) {
            $material_sourcing->whereIn('room_id', $room_id);
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);
        if (count($plant_id) > 0) {
            $material_sourcing->whereIn('plant_id', $plant_id);
        }

        if (request()->has('plant_id')) {
            $material_sourcing->whereHas('room', function($q) {
                $q->whereIn('plant_id', request()->input('plant_id'));
            });
        }

        if (request()->has('room_id')) {
            $material_sourcing->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('created_at')) {
            $start = trim(request()->created_at[0], '"');
            $end   = trim(request()->created_at[1], '"');
            $material_sourcing->whereBetween('created_at', [$start, $end]);
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $material_sourcing->whereHas('material', function ($data) use ($q) {
                $data->where(function($query) use ($q) {
	                $query->orWhere(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
	            });
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $material_sourcing->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $material_sourcing->orderBy('id', 'desc');
        }

        $material_sourcing = $material_sourcing->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($material_sourcing['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $material_sourcing['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $material_sourcing;
    }

    public function updateOrCreate(Request $request)
    {
        Auth::user()->cekRoleModules(['material-sourcing-create']);

        $this->validate(request(), [
            'plant_id' => 'required|exists:plants,id',
            'room_id' => 'required|exists:rooms,id',
            'materials' => 'required|array',
            'materials.*.id'  => 'required|exists:materials,id,deleted_at,NULL',
            'materials.*.minimum_stock'  => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            foreach($request->materials as $material) {
                $materials_id[] = $material['id'];

                MaterialSourcing::updateOrCreate(
                    [
                        'material_id' => $material['id'],
                        'room_id' => $request->room_id
                    ],
                    [
                        'minimum_stock' => $material['minimum_stock'],
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]
                );
            }

            MaterialSourcing::where('room_id', $request->room_id)
                ->whereNotIn('material_id', $materials_id)->delete();

            DB::commit();

            return $request->all();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['material-sourcing-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $material_sourcing = MaterialSourcing::with([
            'material', 'material.uom', 'material.classification',
            'room', 'room.plant',
            'createdBy', 'updatedBy',
        ])->find($id);

        return $material_sourcing;
    }

    public function showMaterialByRoom($room_id)
    {
        Auth::user()->cekRoleModules(['material-sourcing-view']);

        $material_sourcing = MaterialSourcing::with([
            'material', 'material.uom', 'material.classification', 
            'room', 'room.plant',
            'createdBy', 'updatedBy',
        ])->where('room_id', $room_id)->get();

        if ($material_sourcing) {
            foreach($material_sourcing as $key => $value) {
                $material_sourcing[$key]['material']['minimum_stock'] = $value->minimum_stock;
            }
        }

        return $material_sourcing;
    }

    public function multipleDelete()
    {
        Auth::user()->cekRoleModules(['material-sourcing-update']);

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
            'id.*'        => 'required|exists:material_sourcings,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = MaterialSourcing::findOrFail($ids)->delete();
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
