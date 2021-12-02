<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use App\Models\Fund;
use App\Helpers\HashId;

class FundController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['fund-view']);

        $fund = (new Fund)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $fund->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $fund->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $fund->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $fund = $fund->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $fund = $fund->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }

        return $fund;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['fund-view']);

        $fund = (new Fund)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $fund->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $fund->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $fund->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $fund = $fund->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $fund = $fund->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }
        
        foreach($fund['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $fund['data'][$k] = $v;
            }
            catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 401);
            }
        }

        return $fund;
    }

    public function store(Request $request)
    {
    	Auth::user()->cekRoleModules(['fund-create']);

    	$this->validate($request, [
            'name' => 'required|max:20',
            'description' => 'nullable',
    	]);

        //update if add data already exsist but soft deleted
    	$fund = Fund::withTrashed()->where('name', $request->name)->first();
        
        if ($fund) {
            if($fund->deleted_at){
                $fund->restore();
                $fund->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_by' => Auth::user()->id
                ]);

                $save = $fund;
            } else {
                return response()->json([
                    'message' => 'Data invalid',
                    'errors' => [
                        'name' => ['name already exists']
                    ]
                ],422);
            }
        } else {
            $fund = [
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ];

            $save = Fund::create($fund);            
        }

        return $save;
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['fund-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $fund = Fund::findOrFail($id);

        return $fund;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['fund-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $fund = Fund::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|max:20|unique:funds,name,'. $id .'',
            'description' => 'nullable',
        ]);

        $save = $fund->update([
            'name' => $request->name,
            'description' => $request->description,
            'updated_by' => Auth::user()->id,
        ]);

        if ($save) {
            return $fund;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function destroy($id)
    {
        Auth::user()->cekRoleModules(['fund-delete']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $fund = Fund::findOrFail($id);

        $delete = $fund->delete();

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
        Auth::user()->cekRoleModules(['fund-delete']);

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
            'id.*'        => 'required|exists:funds,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = Fund::findOrFail($ids)->delete();
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
