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

use App\Models\ClassificationType;

class ClassificationTypeController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['classification-type-view']);

        $classificationType = (new ClassificationType)->newQuery();

        $classificationType->where('deleted', false)->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $classificationType->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(name)"), 'LIKE', "%".$q."%");
            });
        }

        if(request()->has('name')) {
            $classificationType->where(DB::raw("LOWER(name)"), 'LIKE', "%".strtolower(request()->input('name'))."%");
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $classificationType->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $classificationType->orderBy('name', 'asc');
        }

        if (request()->has('per_page')) {
            return $classificationType->paginate(request()->input('per_page'))->appends(Input::except('page'));
        } else {
            return $classificationType->paginate(appsetting('PAGINATION_DEFAULT'))->appends(Input::except('page'));
        }
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['classification-type-create']);

        $this->validate(request(), [
            'name' => 'required|unique:classification_types,name,NULL,NULL,deleted,false',
        ]);

        //update if add data already exsist but soft deleted
        $clasificationType = ClassificationType::where('name', $request->name)->first();
        if ($clasificationType) {
            $save = $clasificationType->update([
                'deleted'       => 0,
                'updated_by'    => Auth::user()->id
            ]);

            return $clasificationType;
        } else {
            $save = ClassificationType::create([
                'name' => $request->name,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['classification-type-view']);

        return ClassificationType::with(['createdBy', 'updatedBy'])->findOrFail($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['classification-type-update']);

        $clasificationType = ClassificationType::findOrFail($id);

        $this->validate(request(), [
            'name' => 'required|unique:classification_types,name,'. $id .''
        ]);

        $save = $clasificationType->update([
            'name' => $request->name,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $clasificationType;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 401);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['classification-type-update']);

        $delete = ClassificationType::findOrFail($id)->update([
            'deleted' => true, 'updated_by' => Auth::user()->id
        ]);

        if ($delete) {
            return response()->json($delete);
        } else {
            return response()->json([
                'message' => 'Failed Delete Data',
            ], 401);
        }
    }

    public function multipleDelete()
    {
        Auth::user()->cekRoleModules(['classification-type-update']);

        $this->validate(request(), [
            'id'          => 'required|array',
            'id.*'        => 'required|exists:classification_types,id',
        ]);

        foreach (request()->id as $id) {
            $delete = ClassificationType::findOrFail($id)->update([
                'deleted' => true, 'updated_by' => Auth::user()->id
            ]);
        }

        if ($delete) {
            return response()->json([
                'message' => 'Success Delete Data',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Failed Delete Data',
            ], 401);
        }
    }
}
