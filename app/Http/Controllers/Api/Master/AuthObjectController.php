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

use App\Models\Modules;

class AuthObjectController extends Controller
{
    public function allData()
    {
        Auth::user()->cekRoleModules(['role-view']);

        $modules = (new Modules)->newQuery();

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $modules->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(object)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        return $modules->orderBy('object', 'asc')->get();
    }

    public function index()
    {
        Auth::user()->cekRoleModules(['role-view']);

        $modules = (new Modules)->newQuery();

        $modules->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $modules->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(object)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $modules->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $modules->orderBy('object', 'asc');
        }

        if (request()->has('per_page')) {
            return $modules->paginate(request()->input('per_page'))->appends(request()->except('page'));
        } else {
            return $modules->paginate(appsetting('PAGINATION_DEFAULT'))->appends(request()->except('page'));
        }
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        $this->validate(request(), [
            'name' => 'required|unique:modules,name',
            'link' => 'required|unique:modules,link',
        ]);

        $save = Modules::create([
            'name' => $request->name,
            'link' => $request->link,
            'created_by' => Auth::user()->id,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $save;
        } else {
            return response()->json([
                'message' => 'Failed Insert data',
            ], 401);
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['role-view']);

        return Modules::findOrFail($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['role-update']);

        $modules = Modules::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|unique:modules,name,'. $id .'',
            'link' => 'required|unique:modules,link,'. $id .''
        ]);

        $save = $modules->update([
            'name' => $request->name,
            'link' => $request->link,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $modules;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 401);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['role-update']);

        $delete = Modules::findOrFail($id)->delete();

        if ($delete) {
            return response()->json($delete);
        } else {
            return response()->json([
                'message' => 'Failed Delete Data',
            ], 401);
        }
    }
    
}
