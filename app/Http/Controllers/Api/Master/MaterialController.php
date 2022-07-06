<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

use App\Models\Material;
use App\Models\MaterialParameter;
use App\Models\MaterialImage;

use App\Helpers\HashId;

class MaterialController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['material-view']);

        $material = (new Material)->newQuery();

        $material->with(['uom', 'classification', 'material_images', 'material_parameters', 'createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $material->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $material->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $material->orderBy('id', 'desc');
        }

        $material = $material->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $material;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['material-view']);

        $material = (new Material)->newQuery();

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $material->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(material_code)"), 'LIKE', "%".$q."%");
            });
        }

        $material->with(['uom', 'classification', 'material_images', 'material_parameters', 'createdBy', 'updatedBy']);

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $material->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $material->orderBy('id', 'desc');
        }

        $material = $material->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($material['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $material['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $material;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['material-create']);

        $this->validate(request(), [
            'material_code' => 'required|max:190',
            'description' => 'required',
            'classification_id' => 'required|exists:classifications,id',
            'unit_of_measurement_id' => 'required|exists:unit_of_measurements,id',
            'quantity_uom' => 'required|min:1',
            'serial_number' => 'nullable|between:0,1',
        ]);

        DB::beginTransaction();
        try {
            $material = Material::withTrashed()->whereRaw('LOWER(material_code) = ?', strtolower($request->material_code))->first();
    
            if ($material) {
                if($material->deleted_at){
                    $material->restore();
                    $save = $material->update([
                        'material_code' => $request->material_code,
                        'description' => $request->description,
                        'classification_id' => $request->classification_id,
                        'unit_of_measurement_id' => $request->unit_of_measurement_id,
                        'quantity_uom' => $request->quantity_uom,
                        'serial_number' => $request->serial_number ? $request->serial_number : 0,
                        'updated_by'    => Auth::user()->id
                    ]);
                    $save = $material;
                } else {
                    return response()->json([
                        'message' => 'Data invalid',
                        'errors' => [
                            'material_code' => ['Material code already taken']
                        ]
                    ],422);
                }
            } else {
                $save = Material::create([
                    'material_code' => $request->material_code,
                    'description' => $request->description,
                    'classification_id' => $request->classification_id,
                    'unit_of_measurement_id' => $request->unit_of_measurement_id,
                    'quantity_uom' => $request->quantity_uom,
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
        Auth::user()->cekRoleModules(['material-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $material = Material::with([
            'uom', 'classification', 'classification.parameters', 'material_images', 
            'material_parameters', 'material_parameters.classification_parameter', 'createdBy', 'updatedBy'
        ])->find($id);

        if ($material) {
            $material->classification_id_hash = HashId::encode($material->id);
        }

        return $material;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['material-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $material = Material::with('material_parameters')->findOrFail($id);

        $this->validate(request(), [
            'material_code' => 'required|max:190|unique:materials,material_code,'. $id .'',
            'description' => 'required',
            'classification_id' => 'required|exists:classifications,id',
            'unit_of_measurement_id' => 'required|exists:unit_of_measurements,id',
            'quantity_uom' => 'required|min:1',
            'serial_number' => 'nullable|between:0,1',
        ]);

        DB::beginTransaction();
        try {

            $material->update([
                'material_code' => $request->material_code,
                'description' => $request->description,
                'classification_id' => $request->classification_id,
                'unit_of_measurement_id' => $request->unit_of_measurement_id,
                'quantity_uom' => $request->quantity_uom,
                'serial_number' => $request->serial_number ? $request->serial_number : 0,
                'updated_by'    => Auth::user()->id
            ]);

            if (count($material->material_parameters) > 0) {
                foreach($material->material_parameters as $parameter) {
                    MaterialParameter::where('material_id', $material->id)
                        ->where('classification_parameter_id', $parameter->classification_parameter_id)
                        ->delete();
                }
            }
        
            if (count($request->parameters) > 0) {
                foreach($request->parameters as $parameters) {
                    foreach($parameters as $key => $value) {
                        MaterialParameter::create([
                            'material_id' => $material->id,
                            'classification_parameter_id' => $key,
                            'value' => $value,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return $material;

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['material-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $material = Material::findOrFail($id);

        $delete = $material->delete();

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
        Auth::user()->cekRoleModules(['material-update']);

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
                $delete = Material::findOrFail($ids)->delete();
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

    public function listImage($material_id)
    {
        Auth::user()->cekRoleModules(['material-view']);

        try {
            $id = HashId::decode($material_id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $material = (new MaterialImage)->newQuery();

        $material->where('material_id', $id);

        $material = $material->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $material;
    }


    public function uploadImage($material_id, Request $request)
    {
        Auth::user()->cekRoleModules(['material-update']);

        try {
            $id = HashId::decode($material_id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate($request, [
            'image' => 'required|image'
        ]);

        if (request()->has('image')) {
            $image_data = request()->file('image');
            $image_name = md5(time()) . $id . ".jpg";
            $image_path = 'images/material';

            $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);

            $save = MaterialImage::create([
                'material_id' => $id,
                'image'   => $uploaded,
            ]);

            if ($uploaded) {
                return $save;
            } else {
                return response()->json([
                    'message' => 'Unable to upload profile Image'
                ], 422);
            }
        }
    }

    public function deleteImage($id)
    {
        Auth::user()->cekRoleModules(['material-update']);

        // try {
        //     $id = HashId::decode($id);
        // } catch(\Exception $ex) {
        //     return response()->json([
        //         'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
        //     ], 400);
        // }

        $material = MaterialImage::find($id);

        if (Storage::disk('public')->exists($material->image)) {
            Storage::disk('public')->delete($material->image);
        }

        $material->delete();

        if ($material) {
            return $material;
        } else {
            return response()->json([
                'message' => 'Unable to delete Image'
            ], 422);
        }
    }
}
