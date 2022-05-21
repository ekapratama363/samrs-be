<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;

use App\Models\MaterialSourcing;

use App\Helpers\HashId;

class MaterialSourcingController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['material-sourcing-view']);

        $material_sourcing = (new MaterialSourcing)->newQuery();

        $material_sourcing->with(['material', 'room', 'vendor']);
        $material_sourcing->with(['material.uom']);
        $material_sourcing->with(['material.classification']);

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

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);
        if (count($vendor_id) > 0) {
            $material_sourcing->whereIn('vendor_id', $vendor_id);
        }

        if (request()->has('plant_id')) {
            $material_sourcing->whereIn('plant_id', request()->input('plant_id'));
        }

        if (request()->has('room_id')) {
            $material_sourcing->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('vendor_id')) {
            $material_sourcing->whereIn('vendor_id', request()->input('vendor_id'));
        }

        if (request()->has('created_at')) {
            $material_sourcing->whereBetween('created_at', [request()->created_at[0], request()->created_at[1]]);
        }

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $material_sourcing->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
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

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $material_sourcing->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
            });
        }

        $material_sourcing->with(['material', 'room', 'vendor']);
        $material_sourcing->with(['material.uom']);
        $material_sourcing->with(['material.classification']);

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

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);
        if (count($vendor_id) > 0) {
            $material_sourcing->whereIn('vendor_id', $vendor_id);
        }

        if (request()->has('plant_id')) {
            $material_sourcing->whereIn('plant_id', request()->input('plant_id'));
        }

        if (request()->has('room_id')) {
            $material_sourcing->whereIn('room_id', request()->input('room_id'));
        }

        if (request()->has('vendor_id')) {
            $material_sourcing->whereIn('vendor_id', request()->input('vendor_id'));
        }

        if (request()->has('created_at')) {
            $material_sourcing->whereBetween('created_at', [request()->created_at[0], request()->created_at[1]]);
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

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['material-sourcing-create']);

        $this->validate(request(), [
            'material_code' => 'required|max:30',
            'description' => 'required',
            'classification_id' => 'required|exists:classifications,id',
            'serial_number' => 'nullable|between:0,1',
        ]);

        DB::beginTransaction();
        try {
            $material_sourcing = MaterialSourcing::withTrashed()->whereRaw('LOWER(material_code) = ?', strtolower($request->material_code))->first();
    
            if ($material_sourcing) {
                if($material_sourcing->deleted_at){
                    $material_sourcing->restore();
                    $save = $material_sourcing->update([
                        'material_code' => $request->material_code,
                        'description' => $request->description,
                        'classification_id' => $request->classification_id,
                        'unit_of_measurement_id' => $request->unit_of_measurement_id,
                        'serial_number' => $request->serial_number ? $request->serial_number : 0,
                        'updated_by'    => Auth::user()->id
                    ]);
                    $save = $material_sourcing;
                } else {
                    return response()->json([
                        'message' => 'Data invalid',
                        'errors' => [
                            'material_code' => ['Material code already taken']
                        ]
                    ],422);
                }
            } else {
                $save = MaterialSourcing::create([
                    'material_code' => $request->material_code,
                    'description' => $request->description,
                    'classification_id' => $request->classification_id,
                    'unit_of_measurement_id' => $request->unit_of_measurement_id,
                    'serial_number' => $request->serial_number ? $request->serial_number : 0,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);
            }
    
            if (count($request->parameters) > 0) {
                foreach($request->parameters as $parameters) {
                    foreach($parameters as $key => $value) {
                        MaterialParameter::create([
                            'material_id' => $save->id,
                            'classification_parameter_id' => $key,
                            'value' => $value,
                        ]);
                    }
                }
            }

            DB::commit();

            $save->id_hash = HashId::encode($save->id);
            return $save;
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
            'uom', 'classification', 'classification.parameters', 'material_images', 
            'material_parameters', 'material_parameters.classification_parameter', 'createdBy', 'updatedBy'
        ])->find($id);

        if ($material_sourcing) {
            $material_sourcing->classification_id_hash = HashId::encode($material_sourcing->id);
        }

        return $material_sourcing;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['material-sourcing-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $material_sourcing = MaterialSourcing::with('material_parameters')->findOrFail($id);

        $this->validate(request(), [
            'material_code' => 'required|max:30|unique:materials,material_code,'. $id .'',
            'description' => 'required',
            'classification_id' => 'required|exists:classifications,id',
            'serial_number' => 'nullable|between:0,1',
        ]);

        DB::beginTransaction();
        try {

            $material_sourcing->update([
                'material_code' => $request->material_code,
                'description' => $request->description,
                'classification_id' => $request->classification_id,
                'unit_of_measurement_id' => $request->unit_of_measurement_id,
                'serial_number' => $request->serial_number ? $request->serial_number : 0,
                'updated_by'    => Auth::user()->id
            ]);

            if (count($material_sourcing->material_parameters) > 0) {
                foreach($material_sourcing->material_parameters as $parameter) {
                    MaterialParameter::where('material_id', $material_sourcing->id)
                        ->where('classification_parameter_id', $parameter->classification_parameter_id)
                        ->delete();
                }
            }
        
            if (count($request->parameters) > 0) {
                foreach($request->parameters as $parameters) {
                    foreach($parameters as $key => $value) {
                        MaterialParameter::create([
                            'material_id' => $material_sourcing->id,
                            'classification_parameter_id' => $key,
                            'value' => $value,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return $material_sourcing;

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['material-sourcing-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $material_sourcing = MaterialSourcing::findOrFail($id);

        $delete = $material_sourcing->delete();

        if ($delete) {
            return response()->json($delete);
        } else {
            return response()->json([
                'message' => 'Failed Delete Data',
            ], 400);
        }
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
            'id.*'        => 'required|exists:materials,id',
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
