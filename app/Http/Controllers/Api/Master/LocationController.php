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

use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Plant;
use App\Helpers\HashId;

class LocationController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['location-view']);

        $location = (new Location)->newQuery();

        $location->where('deleted', false)->with(['location_type', 'createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $location->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(address)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(province)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(city)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(building)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(unit)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(contact)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(phone)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(country)"), 'LIKE', "%".$q."%");
            });
        }

        // filter name
        if (request()->has('name')) {
            $location->where(DB::raw("LOWER(locations.name)"), 'LIKE', "%".strtolower(request()->input('name'))."%");
        }

        // filter city
        if (request()->has('city')) {
            $location->where(DB::raw("LOWER(locations.city)"), 'LIKE', "%".strtolower(request()->input('city'))."%");
        }

        // filter province
        if (request()->has('province')) {
            $location->where(DB::raw("LOWER(locations.province)"), 'LIKE', "%".strtolower(request()->input('province'))."%");
        }

        // filter location type
        if (request()->has('location_type')) {
            $location->whereIn('location_type_id', request()->input('location_type'));
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $location->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $location->orderBy('code', 'asc');
        }

        $location = $location->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $location;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['location-view']);

        $location = (new Location)->newQuery();

        $location->where('deleted', false)->with(['location_type', 'createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $location->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(address)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(province)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(city)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(building)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(unit)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(contact)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(phone)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(country)"), 'LIKE', "%".$q."%");
            });
        }

        // filter name
        if (request()->has('name')) {
            $location->where(DB::raw("LOWER(locations.name)"), 'LIKE', "%".strtolower(request()->input('name'))."%");
        }

        // filter city
        if (request()->has('city')) {
            $location->where(DB::raw("LOWER(locations.city)"), 'LIKE', "%".strtolower(request()->input('city'))."%");
        }

        // filter province
        if (request()->has('province')) {
            $location->where(DB::raw("LOWER(locations.province)"), 'LIKE', "%".strtolower(request()->input('province'))."%");
        }

        // filter location type
        if (request()->has('location_type')) {
            $location->whereIn('location_type_id', request()->input('location_type'));
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $location->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $location->orderBy('code', 'asc');
        }

        $location = $location->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($location['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $location['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $location;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['location-create']);

        $this->validate(request(), [
            // 'company_id'        => 'required|exists:suppliers,id',
            // 'plant_id'          => 'required|exists:plants,id',
            'location_type_id'  => 'required|exists:location_types,id',
            'code'              => 'required|max:10',
            'name'              => 'required|max:30',
            'address'           => 'required',
            'latitude'          => 'nullable',
            'longitude'         => 'nullable',
            'province'          => 'nullable',
            'city'              => 'nullable',
            'building'          => 'nullable',
            'unit'              => 'nullable',
            'contact'           => 'nullable',
            'phone'             => 'nullable',
            'country'           => 'nullable',
            'postal_code'       => 'nullable',
            'email'             => 'nullable|email'
        ]);

        $location = Location::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($location) {
            if($location->deleted){
                $save = $location->update([
                    // 'company_id'        => $request->company_id,
                    // 'plant_id'          => $request->plant_id,
                    'location_type_id'  => $request->location_type_id,
                    'code'              => $request->code,
                    'name'              => $request->name,
                    'address'           => $request->address,
                    'latitude'          => $request->latitude,
                    'longitude'         => $request->longitude,
                    'province'          => $request->province,
                    'city'              => $request->city,
                    'building'          => $request->building,
                    'unit'              => $request->unit,
                    'contact'           => $request->contact,
                    'phone'             => $request->phone,
                    'email'             => $request->email,
                    'country'           => $request->country,
                    'postal_code'       => $request->postal_code,
                    'deleted'           => 0,
                    'updated_by'        => Auth::user()->id
                ]);

                return $location;
            }

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = Location::create([
                // 'company_id'        => $request->company_id,
                // 'plant_id'          => $request->plant_id,
                'location_type_id'  => $request->location_type_id,
                'code'              => $request->code,
                'name'              => $request->name,
                'address'           => $request->address,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
                'province'          => $request->province,
                'city'              => $request->city,
                'building'          => $request->building,
                'unit'              => $request->unit,
                'contact'           => $request->contact,
                'phone'             => $request->phone,
                'email'             => $request->email,
                'country'           => $request->country,
                'postal_code'       => $request->postal_code,
                'created_by'        => Auth::user()->id,
                'updated_by'        => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['location-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return Location::with(['location_type', 'createdBy', 'updatedBy'])->findOrFail($id);
    }

    public function log($id)
    {
        Auth::user()->cekRoleModules(['location-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $log = (new \App\Models\ActivityLog)->newQuery();

        $log->with('user')
            ->where('log_name', 'Location')
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
        Auth::user()->cekRoleModules(['location-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $location = Location::findOrFail($id);

        $this->validate(request(), [
            // 'company_id'        => 'required|exists:suppliers,id',
            // 'plant_id'          => 'required|exists:plants,id',
            'location_type_id'  => 'required|exists:location_types,id',
            'code'              => 'required|max:10|unique:locations,code,'. $id .'',
            'name'              => 'required|max:30',
            'address'           => 'required',
            'latitude'          => 'nullable',
            'longitude'         => 'nullable',
            'province'          => 'nullable',
            'city'              => 'nullable',
            'building'          => 'nullable',
            'unit'              => 'nullable',
            'contact'           => 'nullable',
            'phone'             => 'nullable',
            'country'           => 'nullable',
            'postal_code'       => 'nullable',
            'email'             => 'nullable|email'
        ]);

        $save = $location->update([
            // 'company_id'        => $request->company_id,
            // 'plant_id'          => $request->plant_id,
            'location_type_id'  => $request->location_type_id,
            'code'              => $request->code,
            'name'              => $request->name,
            'address'           => $request->address,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
            'province'          => $request->province,
            'city'              => $request->city,
            'building'          => $request->building,
            'unit'              => $request->unit,
            'contact'           => $request->contact,
            'phone'             => $request->phone,
            'email'             => $request->email,
            'country'           => $request->country,
            'postal_code'       => $request->postal_code,
            'updated_by'        => Auth::user()->id
        ]);

        if ($save) {
            return $location;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['location-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $delete = Location::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['location-update']);

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
            'id.*'        => 'required|exists:locations,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = Location::findOrFail($ids)->update([
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

    public function getLocationByPlant($id)
    {
        $plant = Plant::findOrFail($id);

        return Location::where('plant_id', $plant->id)->where('deleted', false)->get();
    }

    public function getUserByLocation($id)
    {
        $location = Location::findOrFail($id);

        return User::where('location_id', $location->id)->where('status', 1)->get();
    }

    public function getLocationList(Request $request)
    {
        if ($request->plant) {
            $plant = Plant::findOrFail($request->plant)->id;
        } else {
            $plant = null;
        }

        if ($request->company) {
            $company = Supplier::findOrFail($request->company)->id;
        } else {
            $company = null;
        }

        return Location::where('plant_id', $plant)->where('company_id', $company)
        ->where('deleted', false)->get();
    }

    public function getCompanyByType($id)
    {
        return Supplier::where('type', $id)->where('deleted', false)->get();
    }
}
