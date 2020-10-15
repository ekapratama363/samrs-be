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

use App\Models\Plant;
use App\Helpers\HashId;

class PlantController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['plant-view']);

        $plant = (new Plant)->newQuery();

        $plant->where('deleted', false)->with(['location', 'createdBy', 'updatedBy']);

        $plant->with('location.location_type');

        $plant->with('company');

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $plant->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $plant->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $plant->orderBy('code', 'asc');
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);

        if (count($plant_id) > 0) {
            $plant->whereIn('id', $plant_id);
        }

        $plant = $plant->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $plant;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['plant-view']);

        $plant = (new Plant)->newQuery();

        $plant->where('plants.deleted', false)->with(['location', 'createdBy', 'updatedBy']);
        $plant->with('location.location_type');
        $plant->with('company');

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $plant->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });

            // search location
            $plant->where('deleted', false)->orWhereHas('location', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(address)"), 'LIKE', "%".$q."%");
            })
            ->with(['location' => function ($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(address)"), 'LIKE', "%".$q."%");
            }]);

            // search company
            $plant->where('deleted', false)->orWhereHas('company', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })
            ->with(['company' => function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            }]);
        }


        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $sort_field = request()->input('sort_field');
            switch ($sort_field) {
                case 'location':
                    $plant->join('locations', 'locations.id', '=', 'plants.location_id');
                    $plant->select('plants.code as code', 'plants.*');
                    $plant->orderBy('locations.name', $sort_order);
                break;

                case 'company':
                    $plant->leftJoin('companies', 'companies.id', '=', 'plants.company_id');
                    $plant->select('plants.code as code', 'plants.*');
                    $plant->orderBy('companies.code', $sort_order);
                break;

                default:
                    $plant->orderBy($sort_field, $sort_order);
            }
        } else {
            $plant->orderBy('code', 'asc');
        }

        // if have organization parameter
        $plant_id = Auth::user()->roleOrgParam(['plant']);

        if (count($plant_id) > 0) {
            $plant->whereIn('id', $plant_id);
        }

        $plant = $plant->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($plant['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $plant['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $plant;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['plant-create']);

        $this->validate(request(), [
            'code' => 'required|max:4',
            'description' => 'required|max:30',
            'location_id' => 'nullable|exists:locations,id',
            'company_id' => 'nullable|exists:companies,id',
            // 'latitude' => 'required',
            // 'longitude' => 'required'
        ]);

        $plant = Plant::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($plant) {

            if($plant->deleted){
                $save = $plant->update([
                    'code' => $request->code,
                    'description' => $request->description,
                    'location_id' => $request->location_id,
                    'company_id' => $request->company_id,
                    // 'latitude' => $request->latitude,
                    // 'longitude' => $request->longitude,
                    'deleted'       => 0,
                    'updated_by'    => Auth::user()->id
                ]);

                return $plant;
            }

            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = Plant::create([
                'code' => $request->code,
                'description' => $request->description,
                'location_id' => $request->location_id,
                'company_id' => $request->company_id,
                // 'latitude' => $request->latitude,
                // 'longitude' => $request->longitude,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['plant-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return Plant::with(['location', 'createdBy', 'updatedBy'])
        	->with('location.location_type')
            ->with('company')
        	->find($id);
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['plant-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        
        $log = (new \App\Models\ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'Plant')
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
        Auth::user()->cekRoleModules(['plant-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $plant = Plant::findOrFail($id);

        $this->validate(request(), [
            'code' => 'required|max:4|unique:plants,code,'. $id .'',
            'description' => 'required|max:30',
            'location_id' => 'nullable|exists:locations,id',
            'company_id' => 'nullable|exists:companies,id',
            // 'latitude' => 'required',
            // 'longitude' => 'required'
        ]);

        $save = $plant->update([
            'code' => $request->code,
            'description' => $request->description,
            'location_id' => $request->location_id,
            'company_id' => $request->company_id,
            // 'latitude' => $request->latitude,
            // 'longitude' => $request->longitude,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $plant;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['plant-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = Plant::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['plant-update']);

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
            'id.*'        => 'required|exists:plants,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = Plant::findOrFail($ids)->update([
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
