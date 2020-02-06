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

use App\Models\StorageType;
use App\Helpers\HashId;

class StorageTypeController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['storage-type-view']);

        $storage = (new StorageType)->newQuery();

        $storage->where('deleted', false)->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $storage->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $storage->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $storage->orderBy('code', 'asc');
        }

        $storage = $storage->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $storage;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['storage-type-view']);

        $storage = (new StorageType)->newQuery();

        $storage->where('deleted', false)->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $storage->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $storage->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $storage->orderBy('code', 'asc');
        }

        $storage = $storage->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'))
            ->toArray();

        foreach($storage['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $storage['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $storage;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['storage-type-create']);

        $this->validate(request(), [
            'code' => 'required|max:2',
            'description' => 'required|max:30',
        ]);

        $storage = StorageType::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($storage) {

            if($storage->deleted){
                $save = $storage->update([
                    'code' => $request->code,
                    'description' => $request->description,
                    'deleted'       => 0,
                    'updated_by'    => Auth::user()->id
                ]);

                return $storage;
            }

            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = StorageType::create([
                'code' => $request->code,
                'description' => $request->description,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['storage-type-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return StorageType::with(['createdBy', 'updatedBy'])->findOrFail($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['storage-type-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $storage = StorageType::findOrFail($id);

        $this->validate(request(), [
            'code' => 'required|max:2|unique:storage_types,code,'. $id .'',
            'description' => 'required|max:30',
        ]);

        $save = $storage->update([
            'code' => $request->code,
            'description' => $request->description,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $storage;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['storage-type-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = StorageType::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['storage-type-update']);

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
            'id.*'        => 'required|exists:storage_types,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = StorageType::findOrFail($ids)->update([
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
        }    }
}
