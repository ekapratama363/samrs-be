<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use App\Models\AssetCategory;
use App\Helpers\HashId;

class AssetCategoryController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['asset-category-view']);

        $asset_category = (new AssetCategory)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $asset_category->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $asset_category->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $asset_category->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $asset_category = $asset_category->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $asset_category = $asset_category->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }

        return $asset_category;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['asset-category-view']);

        $asset_category = (new AssetCategory)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $asset_category->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $asset_category->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $asset_category->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            $asset_category = $asset_category->paginate(request()->input('per_page'))->appends(request()->input('page'))
            ->withPath('')->toArray();
        } else {
            $asset_category = $asset_category->paginate(20)->appends(request()->input('page'))
            ->withPath('')->toArray();
        }
        
        foreach($asset_category['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $asset_category['data'][$k] = $v;
            }
            catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 401);
            }
        }

        return $asset_category;
    }

    public function store(Request $request)
    {
    	Auth::user()->cekRoleModules(['asset-category-create']);

    	$this->validate($request, [
            'name' => 'required|max:100',
            'description' => 'nullable',
    	]);

        //update if add data already exsist but soft deleted
    	$asset_category = AssetCategory::withTrashed()->where('name', $request->name)->first();
        
        if ($asset_category) {
            if($asset_category->deleted_at){
                $asset_category->restore();
                $asset_category->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'updated_by' => Auth::user()->id
                ]);

                $save = $asset_category;
            } else {
                return response()->json([
                    'message' => 'Data invalid',
                    'errors' => [
                        'name' => ['name already exists']
                    ]
                ],422);
            }
        } else {
            $asset_category = [
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ];

            $save = AssetCategory::create($asset_category);            
        }

        return $save;
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['asset-category-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $asset_category = AssetCategory::findOrFail($id);

        return $asset_category;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['asset-category-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $asset_category = AssetCategory::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|max:100|unique:asset_categories,name,'. $id .'',
            'description' => 'nullable',
        ]);

        $save = $asset_category->update([
            'name' => $request->name,
            'description' => $request->description,
            'updated_by' => Auth::user()->id,
        ]);

        if ($save) {
            return $asset_category;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function destroy($id)
    {
        Auth::user()->cekRoleModules(['asset-category-delete']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $asset_category = AssetCategory::findOrFail($id);

        $delete = $asset_category->delete();

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
        Auth::user()->cekRoleModules(['asset-category-delete']);

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
            'id.*'        => 'required|exists:asset_categories,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = AssetCategory::findOrFail($ids)->delete();
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
