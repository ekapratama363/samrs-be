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

use App\Models\Room;
use App\Helpers\HashId;

class RoomController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['room-view']);

        $room = (new Room)->newQuery();

        $room->with(['plant', 'createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $room->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $room->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $room->orderBy('code', 'asc');
        }

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);

        if (count($room_id) > 0) {
            $room->whereIn('id', $room_id);
        }

        $room = $room->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $room;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['room-view']);

        $room = (new Room)->newQuery();

        $room->with(['plant', 'createdBy', 'updatedBy']);

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $room->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $room->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $room->orderBy('code', 'asc');
        }

        // if have organization parameter
        $room_id = Auth::user()->roleOrgParam(['room']);

        if (count($room_id) > 0) {
            $room->whereIn('id', $room_id);
        }

        $room = $room->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($room['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $room['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $room;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['room-create']);

        $this->validate(request(), [
            'code' => 'required|max:4',
            'name' => 'required|max:30',
            'plant_id' => 'required|exists:plants,id',
            'description' => 'required|max:200',
        ]);

        $room = Room::withTrashed()->whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($room) {
            if($room->deleted_at){
                $room->restore();
                $save = $room->update([
                    'code' => $request->code,
                    'name' => $request->name,
                    'description' => $request->description,
                    'plant_id' => $request->plant_id,
                    'updated_by'    => Auth::user()->id
                ]);

                return $room;
            }

            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = Room::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'plant_id' => $request->plant_id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['room-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return Room::with(['createdBy', 'updatedBy'])->find($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['room-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $room = Room::findOrFail($id);

        $this->validate(request(), [
            'code' => 'required|max:4|unique:rooms,code,'. $id .'',
            'name' => 'required|max:30',
            'description' => 'required|max:200',
            'plant_id' => 'required|exists:plants,id',
            // 'latitude' => 'required',
            // 'longitude' => 'required'
        ]);

        $save = $room->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'plant_id' => $request->plant_id,
            // 'latitude' => $request->latitude,
            // 'longitude' => $request->longitude,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $room;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['room-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $room = Room::findOrFail($id);

        $delete = $room->delete();

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
        Auth::user()->cekRoleModules(['room-update']);

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
            'id.*'        => 'required|exists:rooms,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = Room::findOrFail($ids)->delete();
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