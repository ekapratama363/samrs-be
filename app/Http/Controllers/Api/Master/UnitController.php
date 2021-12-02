<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use App\Models\Unit;
use App\Helpers\HashId;

class UnitController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['unit-view']);

        $unit = (new Unit)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $unit->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $unit->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $unit->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $unit = $unit->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $unit = $unit->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }

        return $unit;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['unit-view']);

        $unit = (new Unit)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $unit->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $unit->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $unit->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $unit = $unit->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $unit = $unit->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }
        
        foreach($unit['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $unit['data'][$k] = $v;
            }
            catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 401);
            }
        }

        return $unit;
    }

    public function store(Request $request)
    {
    	Auth::user()->cekRoleModules(['unit-create']);

    	$this->validate($request, [
            'name' => 'required|max:20',
            'description' => 'nullable',
    	]);

        //update if add data already exsist but soft deleted
    	$unit = Unit::withTrashed()->where('name', $request->name)->first();
        
        if ($unit) {
            if($unit->deleted_at){
                $unit->restore();
                $unit->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_by' => Auth::user()->id
                ]);

                $save = $unit;
            } else {
                return response()->json([
                    'message' => 'Data invalid',
                    'errors' => [
                        'name' => ['name already exists']
                    ]
                ],422);
            }
        } else {
            $unit = [
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ];

            $save = Unit::create($unit);            
        }

        return $save;
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['unit-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $unit = Unit::findOrFail($id);

        return $unit;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['unit-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $unit = Unit::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|max:20|unique:units,name,'. $id .'',
            'description' => 'nullable',
        ]);

        $save = $unit->update([
            'name' => $request->name,
            'description' => $request->description,
            'updated_by' => Auth::user()->id,
        ]);

        if ($save) {
            return $unit;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function destroy($id)
    {
        Auth::user()->cekRoleModules(['unit-delete']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $unit = Unit::findOrFail($id);

        $delete = $unit->delete();

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
        Auth::user()->cekRoleModules(['unit-delete']);

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
                $delete = Unit::findOrFail($ids)->delete();
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
