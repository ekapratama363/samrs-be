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

        $plant->with(['createdBy', 'updatedBy']);

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

        $plant->with(['createdBy', 'updatedBy']);

        if (request()->has('q') && request()->input('q') != '') {
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
            'code' => 'required|max:15',
            'name' => 'required|max:30',
            'description' => 'required|max:200',
        ]);

        $plant = Plant::withTrashed()->whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($plant) {
            if($plant->deleted_at){
                $plant->restore();
                $save = $plant->update([
                    'code' => $request->code,
                    'name' => $request->name,
                    'description' => $request->description,
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
                'name' => $request->name,
                'description' => $request->description,
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

        return Plant::with(['createdBy', 'updatedBy'])->find($id);
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
            'code' => 'required|max:15|unique:plants,code,'. $id .'',
            'name' => 'required|max:30',
            'description' => 'required|max:200',
            // 'latitude' => 'required',
            // 'longitude' => 'required'
        ]);

        $save = $plant->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
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

        $plant = Plant::findOrFail($id);

        $delete = $plant->delete();

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
                $delete = Plant::findOrFail($ids)->delete();
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
