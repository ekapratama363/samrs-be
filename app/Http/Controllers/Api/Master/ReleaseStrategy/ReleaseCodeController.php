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

use App\Models\ReleaseCode;
use App\Helpers\HashId;

class ReleaseCodeController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['release-code-view']);

        $release = (new ReleaseCode)->newQuery();

        $release->where('deleted', false)->with(['createdBy', 'updatedBy', 'release_group']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('release_group')) {
            $release->where('release_group_id', request()->input('release_group'));
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $release->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $release->orderBy('code', 'asc');
        }

        $release = $release->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $release;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['release-code-view']);

        $release = (new ReleaseCode)->newQuery();

        $release->with(['createdBy', 'updatedBy', 'release_group']);

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('release_group', function ($query) use ($q) {
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
            ->appends(Input::except('page'))
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
        Auth::user()->cekRoleModules(['release-code-create']);

        $this->validate(request(), [
            'code' => 'required|max:4',
            'description' => 'required|max:30',
            'release_group_id' => 'required|exists:release_groups,id',
        ]);

        $release = ReleaseCode::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($release) {

            if($release->deleted){
                $save = $release->update([
                    'code' => $request->code,
                    'description' => $request->description,
                    'deleted' => 0,
		            'release_group_id' => $request->release_group_id,
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
            $save = ReleaseCode::create([
                'code' => $request->code,
                'description' => $request->description,                
                'release_group_id' => $request->release_group_id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['release-code-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return ReleaseCode::with(['createdBy', 'updatedBy', 'release_group'])->findOrFail($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-code-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $release = ReleaseCode::findOrFail($id);

        $this->validate(request(), [
            'code' => 'required|max:4|unique:account_assignments,code,'. $id .'',
            'description' => 'required|max:30',
            'release_group_id' => 'required|exists:release_groups,id',
        ]);

        $save = $release->update([
            'code' => $request->code,
            'description' => $request->description,
            'release_group_id' => $request->release_group_id,
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
        Auth::user()->cekRoleModules(['release-code-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = ReleaseCode::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['release-code-update']);

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
            'id.*'        => 'required|exists:release_codes,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = ReleaseCode::findOrFail($ids)->update([
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
