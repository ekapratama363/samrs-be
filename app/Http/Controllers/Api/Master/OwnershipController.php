<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use App\Models\Ownership;
use App\Helpers\HashId;

class OwnershipController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['ownership-view']);

        $ownership = (new Ownership)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $ownership->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $ownership->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $ownership->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $ownership = $ownership->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $ownership = $ownership->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }

        return $ownership;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['ownership-view']);

        $ownership = (new Ownership)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $ownership->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $ownership->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $ownership->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $ownership = $ownership->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $ownership = $ownership->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }
        
        foreach($ownership['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $ownership['data'][$k] = $v;
            }
            catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 401);
            }
        }

        return $ownership;
    }

    public function store(Request $request)
    {
    	Auth::user()->cekRoleModules(['ownership-create']);

    	$this->validate($request, [
            'name' => 'required|max:20',
            'description' => 'nullable',
    	]);

        //update if add data already exsist but soft deleted
    	$ownership = Ownership::withTrashed()->where('name', $request->name)->first();
        
        if ($ownership) {
            if($ownership->deleted_at){
                $ownership->restore();
                $ownership->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_by' => Auth::user()->id
                ]);

                $save = $ownership;
            } else {
                return response()->json([
                    'message' => 'Data invalid',
                    'errors' => [
                        'name' => ['name already exists']
                    ]
                ],422);
            }
        } else {
            $ownership = [
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ];

            $save = Ownership::create($ownership);            
        }

        return $save;
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['ownership-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $ownership = Ownership::findOrFail($id);

        return $ownership;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['ownership-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $ownership = Ownership::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|max:20|unique:ownerships,name,'. $id .'',
            'description' => 'nullable',
        ]);

        $save = $ownership->update([
            'name' => $request->name,
            'description' => $request->description,
            'updated_by' => Auth::user()->id,
        ]);

        if ($save) {
            return $ownership;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function destroy($id)
    {
        Auth::user()->cekRoleModules(['ownership-delete']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $ownership = Ownership::findOrFail($id);

        $delete = $ownership->delete();

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
        Auth::user()->cekRoleModules(['ownership-delete']);

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
            'id.*'        => 'required|exists:ownerships,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = Ownership::findOrFail($ids)->delete();
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
