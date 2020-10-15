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

use App\Models\UserGroup;
use App\Models\ActivityLog;
use App\Helpers\HashId;

class UserGroupController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['user-group-view']);

        $userGroup = (new UserGroup)->newQuery();

        $userGroup->where('deleted', false)->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $userGroup->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $userGroup->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $userGroup->orderBy('code', 'asc');
        }

        $userGroup = $userGroup->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $userGroup;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['user-group-view']);

        $userGroup = (new UserGroup)->newQuery();

        $userGroup->where('deleted', false)->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $userGroup->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $userGroup->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $userGroup->orderBy('code', 'asc');
        }

        $userGroup = $userGroup->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($userGroup['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $userGroup['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $userGroup;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['user-group-create']);

        $this->validate(request(), [
            'code'          => 'required|max:2',
            'description'   => 'required|max:60'
        ]);

        $userGroup = UserGroup::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($userGroup) {

            if($userGroup->deleted){
                $save = $userGroup->update([
                    'code'          => $request->code,
                    'description'   => $request->description,
                    'deleted'       => 0,
                    'updated_by'    => Auth::user()->id
                ]);

                return $userGroup;
            }

            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = UserGroup::create([
                'code'          => $request->code,
                'description'   => $request->description,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['user-group-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return UserGroup::with(['createdBy', 'updatedBy'])->findOrFail($id);
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['user-group-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $log = (new ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'UserGroup')
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
            ->appends(request()->except('page'));

        $log->transform(function ($data) {
            $data->properties = json_decode($data->properties);

            return $data;
        });

        return $log;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['user-group-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $userGroup = UserGroup::findOrFail($id);

        $this->validate(request(), [
            'code'          => 'required|max:2|unique:user_groups,code,'. $id .'',
            'description'   => 'required|max:60'
        ]);

        $save = $userGroup->update([
            'code'          => $request->code,
            'description'   => $request->description,
            'updated_by'    => Auth::user()->id
        ]);

        if ($save) {
            return $userGroup;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 401);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['user-group-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $userGroup = UserGroup::findOrFail($id);

        $delete = $userGroup->update([
            'deleted'   => 1,
            'updated_by'=> Auth::user()->id
        ]);

        if ($delete) {
            return response()->json($delete);
        } else {
            return response()->json([
                'message' => 'Failed Delete Data',
            ], 401);
        }
    }

    public function multipleDelete()
    {
        Auth::user()->cekRoleModules(['user-group-update']);

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
            'id.*'        => 'required|exists:user_groups,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = UserGroup::findOrFail($ids)->update([
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
