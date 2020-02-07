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

use App\Models\ReleaseCodeUser;
use App\Models\ReleaseCode;
use App\Models\CodeStrategy;
use App\Models\ReleaseStrategy;
use App\Models\PurchaseHeader;
use App\Helpers\HashId;

class ReleaseCodeUserController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['release-code-view']);

        $release = (new ReleaseCodeUser)->newQuery();

        $release->where('deleted', false)->with(['createdBy', 'updatedBy', 'release_group', 'release_code', 'user']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(release_code_desc)"), 'LIKE', "%".$q."%");
            });

            $release->orWhereHas('release_group', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('release_code', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('user', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(username)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);
        }

        if (request()->has('release_group')) {
            $release->where('release_group_id', request()->input('release_group'));
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $release->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $release->orderBy('id', 'asc');
        }

        $release = $release->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $release;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['release-code-view']);

        $release = (new ReleaseCodeUser)->newQuery();

        $release->where('deleted', false)->with(['createdBy', 'updatedBy', 'release_group', 'release_code', 'user']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(release_code_desc)"), 'LIKE', "%".$q."%");
            });

            $release->orWhereHas('release_group', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('release_code', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('user', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(username)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $release->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $release->orderBy('id', 'asc');
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
            'release_code_id' => 'required|exists:release_codes,id',
            'release_group_id' => 'required|exists:release_groups,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $release = ReleaseCodeUser::where('release_code_id', $request->release_code_id)
        	->where('release_group_id', $request->release_group_id)
        	// ->where('user_id', $request->user_id)
        	->first();

        $code = ReleaseCode::find($request->release_code_id);

        if ($release) {

            if($release->deleted){
                $save = $release->update([
		            'release_group_id' => $request->release_group_id,
		            'release_code_id' => $request->release_code_id,
		            'user_id' => $request->user_id,
                    'deleted' => 0,
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
            $save = ReleaseCodeUser::create([
                'release_group_id' => $request->release_group_id,
	            'release_code_id' => $request->release_code_id,
	            'release_code_desc' => $code->description,
	            'user_id' => $request->user_id,
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

        return ReleaseCodeUser::with(['createdBy', 'updatedBy', 'release_group', 'release_code', 'user'])->findOrFail($id);
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

        $release = ReleaseCodeUser::findOrFail($id);

        // cek release code used in relase strategy
        $release_strategy_id = CodeStrategy::where('release_code_id', $release->release_code_id)->pluck('release_strategy_id');
        $release_strategy_used_id = PurchaseHeader::whereIn('release_strategy_id', $release_strategy_id)->pluck('release_strategy_id')->unique()->values();
        $release_group_id = ReleaseStrategy::whereIn('id', $release_strategy_used_id)->pluck('release_group_id')->unique()->values();
        
        if (count($release_group_id) > 0) {
            return response()->json([
                'message' => 'can\'t modify release code user, Release strategy already used in PO',
            ], 422);
        }

        $this->validate(request(), [
            'release_code_id' => 'required|exists:release_codes,id',
            'release_group_id' => 'required|exists:release_groups,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $cek_release = ReleaseCodeUser::where('release_code_id', $request->release_code_id)
        	->where('release_group_id', $request->release_group_id)
        	->where('user_id', $request->user_id)
        	->where('id', '!=', $id)
        	->first();

        $code = ReleaseCode::find($request->release_code_id);

        if ($cek_release) {
        	return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'release_code_id' => ['Data already exists']
                ]
            ],422);
        }

        $save = $release->update([
            'release_group_id' => $request->release_group_id,
            'release_code_id' => $request->release_code_id,
            'release_code_desc' => $code->description,
            'user_id' => $request->user_id,
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

        $delete = ReleaseCodeUser::findOrFail($id)->update([
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
            'id.*'        => 'required|exists:release_code_users,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = ReleaseCodeUser::findOrFail($ids)->update([
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
