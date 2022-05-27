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

use App\Models\Vendor;
use App\Helpers\HashId;

class VendorController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['vendor-view']);

        $vendor = (new Vendor)->newQuery();

        $vendor->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $vendor->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(contact)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $vendor->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $vendor->orderBy('code', 'asc');
        }

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);

        if (count($vendor_id) > 0) {
            $vendor->whereIn('id', $vendor_id);
        }

        $vendor = $vendor->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'));

        return $vendor;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['vendor-view']);

        $vendor = (new Vendor)->newQuery();

        $vendor->with(['createdBy', 'updatedBy']);

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $vendor->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(contact)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(email)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $vendor->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $vendor->orderBy('code', 'asc');
        }

        // if have organization parameter
        $vendor_id = Auth::user()->roleOrgParam(['vendor']);

        if (count($vendor_id) > 0) {
            $vendor->whereIn('id', $vendor_id);
        }

        $vendor = $vendor->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(request()->except('page'))
            ->toArray();

        foreach($vendor['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $vendor['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $vendor;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['vendor-create']);

        $this->validate(request(), [
            'code' => 'required|max:15',
            'name' => 'required|max:30',
            'contact' => 'required',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'address' => 'required|max:200',
        ]);

        $vendor = Vendor::withTrashed()->whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($vendor) {
            if($vendor->deleted_at){
                $vendor->restore();
                $save = $vendor->update([
                    'code' => $request->code,
                    'name' => $request->name,
                    'contact' => $request->contact,
                    'email' => $request->email,
                    'website' => $request->website,
                    'address' => $request->address,
                    'updated_by'    => Auth::user()->id
                ]);

                return $vendor;
            }

            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = Vendor::create([
                'code' => $request->code,
                'name' => $request->name,
                'contact' => $request->contact,
                'email' => $request->email,
                'website' => $request->website,
                'address' => $request->address,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['vendor-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return Vendor::with(['createdBy', 'updatedBy'])->find($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['vendor-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $vendor = Vendor::findOrFail($id);

        $this->validate(request(), [
            'code' => 'required|max:15|unique:vendors,code,'. $id .'',
            'name' => 'required|max:30',
            'contact' => 'required',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'address' => 'required|max:200',
        ]);

        $save = $vendor->update([
            'code' => $request->code,
            'name' => $request->name,
            'contact' => $request->contact,
            'email' => $request->email,
            'website' => $request->website,
            'address' => $request->address,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $vendor;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['vendor-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $vendor = Vendor::findOrFail($id);

        $delete = $vendor->delete();

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
        Auth::user()->cekRoleModules(['vendor-update']);

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
            'id.*'        => 'required|exists:vendors,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                $delete = Vendor::findOrFail($ids)->delete();
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
