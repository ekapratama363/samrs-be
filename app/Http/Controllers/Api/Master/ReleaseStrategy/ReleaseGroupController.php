<?php

namespace App\Http\Controllers\Api\Master\ReleaseStrategy;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

use App\Models\ReleaseGroup;
use App\Helpers\HashId;

class ReleaseGroupController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['release-group-view']);

        $release = (new ReleaseGroup)->newQuery();

        $release->where('deleted', false)->with(['createdBy', 'updatedBy', 'classification', 'release_object']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $release->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $release->orderBy('code', 'asc');
        }

        $release = $release->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $release;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['release-group-view']);

        $release = (new ReleaseGroup)->newQuery();

        $release->with(['createdBy', 'updatedBy', 'classification', 'release_object']);

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('classification', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('release_object', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);
        }

        $release->where('deleted', false);

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $release->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $release->orderBy('code', 'asc');
        }

        $release = $release->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($release['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $release['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $release;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['release-group-create']);

        $this->validate(request(), [
            'code' => 'required|max:2',
            'description' => 'required|max:30',
            'release_object_id' => 'required|exists:release_objects,id',
            'classification_id' => 'required|exists:classification_materials,id',
            'active' => 'boolean'
        ]);

        $release = ReleaseGroup::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($release) {

            if($release->deleted){
                $save = $release->update([
                    'code' => $request->code,
                    'description' => $request->description,
                    'deleted' => 0,
                    'release_object_id' => $request->release_object_id,
		            'classification_id' => $request->classification_id,
		            'active' => $request->active,
                    'updated_by'    => Auth::user()->id
                ]);

                return $release;
            }

            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = ReleaseGroup::create([
                'code' => $request->code,
                'description' => $request->description,                
                'release_object_id' => $request->release_object_id,
	            'classification_id' => $request->classification_id,
	            'active' => $request->active,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['release-group-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return ReleaseGroup::with(['createdBy', 'updatedBy', 'classification', 'release_object'])->findOrFail($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-group-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $release = ReleaseGroup::findOrFail($id);

        $this->validate(request(), [
            'code' => 'required|max:2|unique:account_assignments,code,'. $id .'',
            'description' => 'required|max:30',
            'release_object_id' => 'required|exists:release_objects,id',
            'classification_id' => 'required|exists:classification_materials,id',
            'active' => 'boolean'
        ]);

        $save = $release->update([
            'code' => $request->code,
            'description' => $request->description,
            'release_object_id' => $request->release_object_id,
            'classification_id' => $request->classification_id,
            'active' => $request->active,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $release;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['release-group-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = ReleaseGroup::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['release-group-update']);

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
            'id.*'        => 'required|exists:release_groups,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = ReleaseGroup::findOrFail($ids)->update([
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
}
