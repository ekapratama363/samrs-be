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

use App\Models\ClassificationMaterial;
use App\Models\ClassificationParameter;

use App\Helpers\HashId;

class ClassificationController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['classification-material-view']);

        $classificationMat = (new ClassificationMaterial)->newQuery();

        $classificationMat->where('deleted', false)->with(['classification_type', 'parameters', 'createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $classificationMat->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('type')) {
            $classificationMat->whereIn('classification_type_id', request()->input('type'));
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $classificationMat->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $classificationMat->orderBy('name', 'asc');
        }

        $classificationMat = $classificationMat->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $classificationMat;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['classification-material-view']);

        $classificationMat = (new ClassificationMaterial)->newQuery();

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $classificationMat->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });

            // search classy type
            $classificationMat->where('classification_materials.deleted', false)->orWhereHas('classification_type', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            })
            ->with(['classification_type' => function ($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            }]);

            // search classy params
            $classificationMat->where('classification_materials.deleted', false)->orWhereHas('parameters', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            })
            ->with(['parameters' => function ($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            }]);
        }

        $classificationMat->where('classification_materials.deleted', false);
        $classificationMat->with(['classification_type', 'parameters', 'createdBy', 'updatedBy']);

        if (request()->has('type')) {
            $classificationMat->whereIn('classification_type_id', request()->input('type'));
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $sort_field = request()->input('sort_field');
            switch ($sort_field) {
                case 'classification_type':
                    $classificationMat->join('classification_types','classification_types.id','=','classification_materials.classification_type_id');
                    $classificationMat->select('classification_materials.*');
                    $classificationMat->orderBy('classification_types.name', $sort_order);
                break;

                default:
                    $classificationMat->orderBy($sort_field,$sort_order);
                break;
            }
        } else {
            $classificationMat->orderBy('name', 'asc');
        }

        $classificationMat = $classificationMat->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'))
            ->toArray();

        foreach($classificationMat['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $classificationMat['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $classificationMat;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['classification-material-create']);

        $this->validate(request(), [
            'name' => 'required|unique:classification_materials,name,NULL,NULL,deleted,false',
            'classification_type_id' => 'required|exists:classification_types,id',
        ]);

        //update if add data already exsist but soft deleted
        $classificationMaterial = ClassificationMaterial::where('name', $request->name)->first();

        if ($classificationMaterial) {
            $save = $classificationMaterial->update([
                'classification_type_id' => $request->classification_type_id,
                'deleted'       => 0,
                'updated_by'    => Auth::user()->id
            ]);

            $classificationMaterial->id_hash = HashId::encode($classificationMaterial->id);

            return $classificationMaterial;
        } else {
            $save = ClassificationMaterial::create([
                'name' => $request->name,
                'classification_type_id' => $request->classification_type_id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            $save->id_hash = HashId::encode($save->id);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['classification-material-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return ClassificationMaterial::with(['classification_type', 'parameters', 'createdBy', 'updatedBy'])
        ->findOrFail($id);
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['classification-material-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $log = (new \App\Models\ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'ClassificationMaterial')
            ->whereNotNull('causer_id')
            ->where('subject_id', $id);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $log->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(properties)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $log->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $log->orderBy('id', 'desc');
        }

        $log = $log->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        $log->transform(function ($data) {
            $data->properties = json_decode($data->properties);

            return $data;
        });

        return $log;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['classification-material-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $clasificationMaterial = ClassificationMaterial::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|unique:classification_materials,name,'. $id .'',
            'classification_type_id' => 'required|exists:classification_types,id'
        ]);

        $save = $clasificationMaterial->update([
            'name' => $request->name,
            'classification_type_id' => $request->classification_type_id,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $clasificationMaterial;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['classification-material-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = ClassificationMaterial::findOrFail($id)->update([
            'deleted' => true, 'updated_by' => Auth::user()->id
        ]);

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
        Auth::user()->cekRoleModules(['classification-material-update']);

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
            'id.*'        => 'required|exists:classification_materials,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = ClassificationMaterial::findOrFail($ids)->update([
                    'deleted' => true, 'updated_by' => Auth::user()->id
                ]);
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

    public function clasificationStoreParam($id, Request $request)
    {
        Auth::user()->cekRoleModules(['classification-material-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'name' => 'required',
            'type' => 'required|numeric|min:1',
            'reading_indicator' => 'nullable|boolean'
        ]);

        if (request()->input('type') == 1) {
            // char
            $this->validate(request(), [
                'length' => 'required|numeric'
            ]);
        } else if (request()->input('type') == 4) {
            // numeric
            $this->validate(request(), [
                'length' => 'required|numeric',
                'decimal' => 'required|numeric'
            ]);
        } else if (request()->input('type') == 5) {
            // list
            $this->validate(request(), [
                'value' => 'required'
            ]);
        }

        $classificationMaterial = ClassificationMaterial::findOrFail($id);

        if (request()->input('type') == 1) {
            // char
            $save = ClassificationParameter::create([
                'name' => $request->name,
                'type' => $request->type,
                'length' => $request->length,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'classification_id' => $classificationMaterial->id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 2) {
            // date
            $save = ClassificationParameter::create([
                'name' => $request->name,
                'type' => $request->type,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'classification_id' => $classificationMaterial->id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 3) {
            // time
            $save = ClassificationParameter::create([
                'name' => $request->name,
                'type' => $request->type,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'classification_id' => $classificationMaterial->id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 4) {
            // numeric
            $save = ClassificationParameter::create([
                'name' => $request->name,
                'type' => $request->type,
                'length' => $request->length,
                'decimal' => $request->decimal,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'classification_id' => $classificationMaterial->id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 5) {
            // list
            $save = ClassificationParameter::create([
                'name' => $request->name,
                'type' => $request->type,
                'value' => $request->value,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'classification_id' => $classificationMaterial->id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
        }

        if ($save) {
            return $save;
        } else {
            return response()->json([
                'message' => 'Failed Insert data',
            ], 400);
        }
    }

    public function showClassificationParameter($id)
    {
        Auth::user()->cekRoleModules(['classification-material-view']);

        return ClassificationParameter::findOrFail($id);
    }

    public function updateClassificationParameter($id, Request $request)
    {
        Auth::user()->cekRoleModules(['classification-material-update']);

        $this->validate(request(), [
            'name' => 'required',
            'type' => 'required|numeric|min:1',
            'reading_indicator' => 'nullable|boolean'
        ]);

        if (request()->input('type') == 1) {
            // char
            $this->validate(request(), [
                'length' => 'required|numeric'
            ]);
        } else if (request()->input('type') == 4) {
            // numeric
            $this->validate(request(), [
                'length' => 'required|numeric',
                'decimal' => 'required|numeric'
            ]);
        } else if (request()->input('type') == 5) {
            // list
            $this->validate(request(), [
                'value' => 'required'
            ]);
        }

        $param = ClassificationParameter::findOrFail($id);

        if (request()->input('type') == 1) {
            // char
            $save = $param->update([
                'name' => $request->name,
                'type' => $request->type,
                'length' => $request->length,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 2) {
            // date
            $save = $param->update([
                'name' => $request->name,
                'type' => $request->type,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 3) {
            // time
            $save = $param->update([
                'name' => $request->name,
                'type' => $request->type,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 4) {
            // numeric
            $save = $param->update([
                'name' => $request->name,
                'type' => $request->type,
                'length' => $request->length,
                'decimal' => $request->decimal,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'updated_by' => Auth::user()->id
            ]);
        } else if (request()->input('type') == 5) {
            // list
            $save = $param->update([
                'name' => $request->name,
                'type' => $request->type,
                'value' => $request->value,
                'reading_indicator' => $request->reading_indicator ? $request->reading_indicator : 0,
                'updated_by' => Auth::user()->id
            ]);
        }

        if ($save) {
            $param->classification_id = HashId::encode($param->classification_id);
            return $param;
        } else {
            return response()->json([
                'message' => 'Failed Update data',
            ], 400);
        }
    }

    public function deleteClassificationParameter($id)
    {
        Auth::user()->cekRoleModules(['classification-material-update']);

        $delete = ClassificationParameter::findOrFail($id)->delete();

        if ($delete) {
            return response()->json($delete);
        } else {
            return response()->json([
                'message' => 'Failed Delete Data',
            ], 400);
        }
    }

    public function multipleDeleteClassificationParam()
    {
        Auth::user()->cekRoleModules(['classification-material-update']);

        $this->validate(request(), [
            'id'          => 'required|array',
            'id.*'        => 'required|exists:classification_parameters,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = ClassificationParameter::findOrFail($ids)->delete();
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

    public function parameterByClassification($id)
    {
        $classification = ClassificationMaterial::findOrFail($id);

        return ClassificationParameter::where('classification_id', $classification->id)->where('deleted', false)->get();
    }
}
