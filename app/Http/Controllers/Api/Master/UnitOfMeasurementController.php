<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use App\Models\UnitOfMeasurement;
use App\Helpers\HashId;

class UnitOfMeasurementController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['unit-of-measurement-view']);

        $uom = (new UnitOfMeasurement)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $uom->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $uom->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $uom->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $uom = $uom->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $uom = $uom->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }

        return $uom;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['unit-of-measurement-view']);

        $uom = (new UnitOfMeasurement)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $uom->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $uom->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $uom->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $uom = $uom->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $uom = $uom->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }
        
        foreach($uom['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $uom['data'][$k] = $v;
            }
            catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 401);
            }
        }

        return $uom;
    }

    public function store(Request $request)
    {
    	Auth::user()->cekRoleModules(['unit-of-measurement-create']);

    	$this->validate($request, [
            'name' => 'required|max:20',
            'description' => 'nullable',
    	]);

        //update if add data already exsist but soft deleted
    	$uom = UnitOfMeasurement::withTrashed()->where('name', $request->name)->first();
        
        if ($uom) {
            if($uom->deleted_at){
                $uom->restore();
                $uom->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_by' => Auth::user()->id
                ]);

                $save = $uom;
            } else {
                return response()->json([
                    'message' => 'Data invalid',
                    'errors' => [
                        'name' => ['name already exists']
                    ]
                ],422);
            }
        } else {
            $uom = [
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ];

            $save = UnitOfMeasurement::create($uom);            
        }

        return $save;
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['unit-of-measurement-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $uom = UnitOfMeasurement::findOrFail($id);

        return $uom;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['unit-of-measurement-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $uom = UnitOfMeasurement::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|max:20|unique:unit_of_measurements,name,'. $id .'',
            'description' => 'nullable',
        ]);

        $save = $uom->update([
            'name' => $request->name,
            'description' => $request->description,
            'updated_by' => Auth::user()->id,
        ]);

        if ($save) {
            return $uom;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function destroy($id)
    {
        Auth::user()->cekRoleModules(['unit-of-measurement-delete']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $uom = UnitOfMeasurement::findOrFail($id);

        $delete = $uom->delete();

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
        Auth::user()->cekRoleModules(['unit-of-measurement-delete']);

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
            'id.*'        => 'required|exists:units,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = UnitOfMeasurement::findOrFail($ids)->delete();
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
