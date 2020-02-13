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

use App\Models\LocationType;
use App\Helpers\HashId;

class LocationTypeController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['location-type-view']);

        $locType = (new LocationType)->newQuery();

        $locType->where('deleted', false)->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $locType->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $locType->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $locType->orderBy('code', 'asc');
        }

        $locType = $locType->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $locType;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['location-type-view']);

        $locType = (new LocationType)->newQuery();

        $locType->where('deleted', false)->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $locType->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $locType->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $locType->orderBy('code', 'asc');
        }

        $locType = $locType->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'))
            ->toArray();

        foreach($locType['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $locType['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $locType;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['location-type-create']);

        $this->validate(request(),
            [
                // 'code' => 'required|unique:location_types,code',
                // 'icon' => 'required',
                'code' => 'required|max:4|regex:/^\S*$/',//|unique:location_types,code,NULL,NULL,deleted,false',
                // custom unique validation insensitive case App\Providers\AppServiceProvider
                // 'name' => 'required|regex:/^\S*$/|iunique:location_types,name,NULL,NULL,deleted,false',
                'description' => 'required|max:30',
                'icon' => 'required|image',
                'zoom_level' => 'required|numeric|min:1|max:20',
                'zoom_level_end' => 'required|numeric|min:1|max:20|gte:zoom_level'
            ],
            // custom message
            [
                // 'iunique'   => 'The :attribute has already been taken.',
                'regex'     => 'The :attribute must not contains whitespace.',
            ]
        );

        $image_data = request()->file('icon');
        $image_ext  = request()->file('icon')->getClientOriginalExtension();
        $image_name = md5(time()). "." .$image_ext;
        $image_path = 'images/locationtype';

        $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);

        if ($uploaded) {
            $locationType = LocationType::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

            if ($locationType) {

                if($locationType->deleted){
                    $save = $locationType->update([
                        'code' => $request->code,
                        'icon' => $uploaded,
                        'description' => $request->description,
                        'zoom_level' => $request->zoom_level,
                        'zoom_level_end' => $request->zoom_level_end,
                        'deleted'       => 0,
                        'updated_by'    => Auth::user()->id
                    ]);

                    return $locationType;
                }

                return response()->json([
                    'message' => 'Data invalid',
                    'errors' => [
                        'code' => ['Code already taken']
                    ]
                ],422);
            } else {
                $save = LocationType::create([
                    'name' => $request->name,
                    'code' => $request->code,
                    'description' => $request->description,
                    'icon' => $uploaded,
                    'zoom_level' => $request->zoom_level,
                    'zoom_level_end' => $request->zoom_level_end,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);

                return $save;
            }
        } else {
            return response()->json([
                'message' => 'Unable to upload icon'
            ], 400);
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['location-type-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return LocationType::with(['createdBy', 'updatedBy'])->findOrFail($id);
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['location-type-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $log = (new \App\Models\ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'LocationType')
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
        Auth::user()->cekRoleModules(['location-type-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $locationType = LocationType::findOrFail($id);

        $this->validate(request(),
            [
                'code' => 'required|max:4|regex:/^\S*$/|unique:location_types,code,'. $id .'',
                'description' => 'required|max:30',
                'icon' => 'nullable|image',
                'zoom_level' => 'required|numeric|min:1|max:20',
                'zoom_level_end' => 'required|numeric|min:1|max:20|gte:zoom_level'
            ],
            [
                'regex'     => 'The :attribute must not contains whitespace.',
            ]
        );

        if ($request->hasFile('icon')) {
            $image_data = request()->file('icon');
            $image_ext  = request()->file('icon')->getClientOriginalExtension();
            $image_name = md5(time()). "." .$image_ext;
            $image_path = 'images/locationtype';

            $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);

            if ($uploaded) {
                if (Storage::disk('public')->exists($locationType->icon)) {
                    Storage::delete($locationType->icon);
                }
                $save = $locationType->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'code' => $request->code,
                    'icon' => $uploaded,
                    'zoom_level' => $request->zoom_level,
                    'zoom_level_end' => $request->zoom_level_end,
                    'updated_by' => Auth::user()->id
                ]);

                if ($save) {
                    return $locationType;
                } else {
                    return response()->json([
                        'message' => 'Failed Update data',
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Unable to upload icon'
                ], 400);
            }
        } else {
            $save = $locationType->update([
                'name' => $request->name,
                // 'icon' => $uploaded,
                'description' => $request->description,
                'code' => $request->code,
                'zoom_level' => $request->zoom_level,
                'zoom_level_end' => $request->zoom_level_end,
                'updated_by' => Auth::user()->id
            ]);

            if ($save) {
                return $locationType;
            } else {
                return response()->json([
                    'message' => 'Failed Update data',
                ], 400);
            }
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['location-type-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = LocationType::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['location-type-update']);

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
            'id.*'        => 'required|exists:location_types,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = LocationType::findOrFail($ids)->update([
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

