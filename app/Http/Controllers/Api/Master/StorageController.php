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

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Master\StorageTemplateExport;
use App\Exports\Master\StorageInvalidExport;
use App\Imports\Master\StorageImport;

use App\Models\Storage as MasterStorage;
use App\Helpers\HashId;
use App\Models\StorageFailedUpload;

class StorageController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['storage-view']);
  
        $storage = (new MasterStorage)->newQuery();
        $storage->where('deleted', false)->with(['location', 'plant', 'user', 'createdBy', 'updatedBy']);
        $storage->with('location.location_type');
        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $storage->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('location')) {
            $storage->whereIn('location_id', request()->input('location'));
        }

        if (request()->has('plant')) {
            $storage->whereIn('plant_id', request()->input('plant'));
        }

        if (request()->has('type')) {
            $storage->whereIn('type', request()->input('type'));
        }

        if (request()->has('user')) {
            $storage->whereIn('user_id', request()->input('user'));
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $storage->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $storage->orderBy('code', 'asc');
        }

        // if have organization parameter
        $storage_id = Auth::user()->roleOrgParam(['storage']);

        if (count($storage_id) > 0) {
            $storage->whereIn('id', $storage_id);
        }

        $storage = $storage->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $storage;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['storage-view']);
  
        $storage = (new MasterStorage)->newQuery();
        
        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $storage->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });

            // search location
            $storage->where('deleted', false)->orWhereHas('location', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(address)"), 'LIKE', "%".$q."%");
            })
            ->with(['location' => function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(address)"), 'LIKE', "%".$q."%");
            }]);

            // search plant
            $storage->where('deleted', false)->orWhereHas('plant', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })
            ->with(['plant' => function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            }]);

            // search responsible
            $storage->where('deleted', false)->orWhereHas('user', function ($query) use ($q) {
                $query->where(DB::raw("LOWER( CONCAT_WS(' ', firstname, lastname) )"), 'LIKE', "%".$q."%");
            })
            ->with(['user' => function ($query) use ($q) {
                $query->where(DB::raw("LOWER( CONCAT_WS(' ', firstname, lastname) )"), 'LIKE', "%".$q."%");
            }]);
        }

        $storage->where('storages.deleted', false);
        $storage->with(['location', 'plant', 'user', 'createdBy', 'updatedBy']);
        $storage->with('location.location_type');

        if (request()->has('location')) {
            $storage->whereIn('location_id', request()->input('location'));
        }

        if (request()->has('plant')) {
            $storage->whereIn('plant_id', request()->input('plant'));
        }

        if (request()->has('type')) {
            $storage->whereIn('type', request()->input('type'));
        }

        if (request()->has('user')) {
            $storage->whereIn('user_id', request()->input('user'));
        }

        if (request()->has('sort_field')) {
            $sort_field = request()->input('sort_field');
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            switch ($sort_field) {
                case 'plant':
                    $storage->join('plants', 'plants.id', '=', 'storages.plant_id');
                    $storage->select('storages.code as code', 'storages.*');
                    $storage->orderBy('plants.code', $sort_order);
                break;

                case 'location':
                    $storage->join('locations', 'locations.id', '=', 'storages.location_id');
                    $storage->select('storages.code as code', 'storages.*');
                    $storage->orderBy('locations.name', $sort_order);
                break;

                case 'user':
                    $storage->join('users', 'users.id', '=', 'storages.user_id');
                    $storage->select('storages.code as code', 'storages.*');
                    $storage->orderBy('users.firstname', $sort_order);
                break;

                default:
                    $storage->orderBy($sort_field, $sort_order);
            }
        } else {
            $storage->orderBy('code', 'asc');
        }

        // if have organization parameter
        $storage_id = Auth::user()->roleOrgParam(['storage']);

        if (count($storage_id) > 0) {
            $storage->whereIn('id', $storage_id);
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
        Auth::user()->cekRoleModules(['storage-create']);

        $this->validate(request(), [
            'plant_id'          => 'required|exists:plants,id',
            'location_id'       => 'required|exists:locations,id',
            // 'type'              => 'required',
            // 'name'              => 'required|max:21|unique:storages,name',
            'code'              => 'required|max:4',
            'description'       => 'required|max:30',
            'user_id'           => 'nullable|exists:users,id'
        ]);

        $storage = MasterStorage::where('plant_id', $request->plant_id)
                 ->whereRaw('LOWER(code) = ?', strtolower($request->code))
                 ->first();

        if ($storage) {

            if($storage->deleted){
                $save = $storage->update([
                    'location_id'   => $request->location_id,
                    'plant_id'      => $request->plant_id,
                    // 'type'          => $request->type,
                    // 'name'          => $request->name,
                    'code'          => $request->code,
                    'description'   => $request->description,
                    'user_id'       => $request->user_id,
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
            $save = MasterStorage::create([
                'location_id'   => $request->location_id,
                'plant_id'      => $request->plant_id,
                // 'type'          => $request->type,
                // 'name'          => $request->name,
                'code'          => $request->code,
                'description'   => $request->description,
                'user_id'       => $request->user_id,
                'created_by'    => Auth::user()->id,
                'updated_by'    => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['storage-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return MasterStorage::with(['location', 'plant', 'user', 'createdBy', 'updatedBy'])
        ->with('location.location_type')->find($id);
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['storage-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $log = (new \App\Models\ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'Storage')
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
            ->appends(Input::except('page'));

        $log->transform(function ($data) {
            $data->properties = json_decode($data->properties);

            return $data;
        });

        return $log;
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['storage-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $storage = MasterStorage::findOrFail($id);

        $this->validate(request(), [
            'plant_id'          => 'required|exists:plants,id',
            'location_id'       => 'required|exists:locations,id',
            // 'type'              => 'required',
            // 'name'              => 'required|max:21|unique:storages,name,'. $id .'',
            'code'              => 'required|max:4|unique:storages,code,'. $id .'',
            'description'       => 'required|max:30',
            'user_id'           => 'nullable|exists:users,id'
        ]);

        $save = $storage->update([
            'location_id'   => $request->location_id,
            'plant_id'      => $request->plant_id,
            // 'type'          => $request->type,
            // 'name'          => $request->name,
            'code'          => $request->code,
            'description'   => $request->description,
            'user_id'       => $request->user_id,
            'updated_by'    => Auth::user()->id
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
        Auth::user()->cekRoleModules(['storage-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = MasterStorage::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['storage-update']);

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
            'id.*'        => 'required|exists:storages,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = MasterStorage::findOrFail($ids)->update([
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

    public function getStorageByPlant($id)
    {
        Auth::user()->cekRoleModules(['storage-view']);
  
        $storage = (new MasterStorage)->newQuery();
        $storage->with('user');
        $storage->with('location');
        $storage->where('deleted', false);
        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $storage->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }
            
        $storage->where('plant_id', (int)$id);
        
        $storage->orderBy('id', 'desc');

        // if have organization parameter
        $storage_id = Auth::user()->roleOrgParam(['storage']);

        if (count($storage_id) > 0) {
            $storage->whereIn('id', $storage_id);
        }

        $storage = $storage->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $storage;
    }

    public function template()
    {
        Auth::user()->cekRoleModules(['storage-create']);

        return Excel::download(new StorageTemplateExport, 'template_storage.xlsx');
    }
    
    public function upload(Request $request)
    {
        Auth::user()->cekRoleModules(['storage-create']);

        $this->validate($request, [
            'upload_file' => 'required'
        ]);

        // get file
        $file = $request->file('upload_file');

        // get file extension for validation
        $ext = $file->getClientOriginalExtension();

        if ($ext != 'xlsx') {
            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'upload_file' => ['File Type must xlsx']
                ]
            ],422);
        }

        $user = Auth::user();
        
        $import = new StorageImport($user);

        Excel::import($import, $file);

        return response()->json([
            'message' => 'upload is in progess'
        ], 200);
    }
    
    public function getInvalidUpload()
    {
        Auth::user()->cekRoleModules(['storage-create']);

        $data = StorageFailedUpload::where('uploaded_by', Auth::user()->id)
            ->orderBy('created_at', 'asc')
            ->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return response()->json([
            'message' => 'invalid data',
            'data' => $data
        ], 200);
    }
    
    public function downloadInvalidUpload()
    {
        Auth::user()->cekRoleModules(['storage-create']);

        $data = StorageFailedUpload::where('uploaded_by', Auth::user()->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data is empty',
            ],404);
        }

        $excel = Excel::download(new StorageInvalidExport($data), 'invalid_storage_upload.xlsx');

        // delete failed upload data
        StorageFailedUpload::where('uploaded_by', Auth::user()->id)
            ->orderBy('created_at', 'asc')
            ->delete();

        return $excel;
    }
    
    public function deleteInvalidUpload()
    {
        Auth::user()->cekRoleModules(['storage-create']);

        $data = StorageFailedUpload::where('uploaded_by', Auth::user()->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if (count($data) == 0) {
            return response()->json([
                'message' => 'Data is empty',
            ],404);
        }

        // delete failed upload data
        StorageFailedUpload::where('uploaded_by', Auth::user()->id)
            ->orderBy('created_at', 'asc')
            ->delete();

        return response()->json([
            'message' => 'berhasil hapus data',
        ],200);
    }
}
