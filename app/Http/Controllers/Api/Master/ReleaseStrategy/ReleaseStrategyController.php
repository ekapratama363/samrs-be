<?php

namespace App\Http\Controllers\Api\Master\ReleaseStrategy;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Auth;
use Storage;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

use App\Models\ReleaseStrategy;
use App\Models\CodeStrategy;
use App\Models\ReleaseStrategyParameter;
use App\Models\ReleaseStatus;
use App\Models\ReleaseCode;
use App\Models\PurchaseHeader;
use App\Helpers\HashId;

class ReleaseStrategyController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['release-strategy-view']);

        $release = (new ReleaseStrategy)->newQuery();

        $release->where('deleted', false)->with(['createdBy', 'updatedBy', 'release_group', 'release_code']);
        $release->with('release_group.classification');
        $release->with('release_group.classification.classification_type');

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });

            $release->orWhereHas('release_group', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $release->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $release->orderBy('code', 'asc');
        }

        $release = $release->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'));

        return $release;
    }

    public function list()
    {
        Auth::user()->cekRoleModules(['release-strategy-view']);

        $release = (new ReleaseStrategy)->newQuery();

        $release->with(['createdBy', 'updatedBy', 'release_group', 'release_code', 'strategy_parameters']);
        $release->with('release_group.classification');
        $release->with('release_group.classification.parameters');
        $release->with('release_group.classification.classification_type');
        $release->with('strategy_parameters.classification_parameter');

        if (request()->has('q') && request()->input('q') != '') {
            $q = strtolower(request()->input('q'));
            $release->where(function($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);

            $release->orWhereHas('release_group', function ($query) use ($q) {
                $query->where(DB::raw("LOWER(code)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(description)"), 'LIKE', "%".$q."%");
            })->where('deleted', false);
        }

        $release->where('deleted', false);

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $release->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $release->orderBy('code', 'asc');
        }

        $release = $release->paginate(request()->has('per_page') ? request()->per_page : appsetting('PAGINATION_DEFAULT'))
            ->appends(Input::except('page'))
            ->toArray();

        foreach($release['data'] as $k => $v) {
            try {
                $v['id'] = HashId::encode($v['id']);
                $release['data'][$k] = $v;
            } catch(\Exception $ex) {
                return response()->json([
                    'message' => 'ERROR : Cannot hash ID. '.$ex->getMessage(),
                ], 400);
            }
        }

        return $release;
    }

    public function store(Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-create']);

        $this->validate(request(), [
            'code' => 'required|max:2',
            'description' => 'required|max:30',
            'release_group_id' => 'required|exists:release_groups,id',
        ]);

        $release = ReleaseStrategy::whereRaw('LOWER(code) = ?', strtolower($request->code))->first();

        if ($release) {

            if($release->deleted){
                $save = $release->update([
                    'code' => $request->code,
                    'description' => $request->description,
                    'deleted' => 0,
		            'release_group_id' => $request->release_group_id,
                    'updated_by'    => Auth::user()->id
                ]);

                return $release;
            }

            return response()->json([
                'message' => 'Data invalid',
                'errors' => [
                    'code' => ['Code already taken']
                ]
            ],422);
        } else {
            $save = ReleaseStrategy::create([
                'code' => $request->code,
                'description' => $request->description,                
                'release_group_id' => $request->release_group_id,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            return $save;
        }
    }

    public function show($id)
    {
        Auth::user()->cekRoleModules(['release-strategy-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        return ReleaseStrategy::with(['createdBy', 'updatedBy', 'release_group', 'release_code', 'strategy_parameters'])
            ->with('strategy_parameters.classification_parameter')
            ->findOrFail($id);
    }

    public function update($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $release = ReleaseStrategy::findOrFail($id);

        // check if release strategy used in po
        $po_strategy = PurchaseHeader::where('release_strategy_id', $id)->first();
        if ($po_strategy) {
            return response()->json([
                'message' => 'can\'t modify release strategy, Release strategy already used in PO',
            ], 422);
        }

        $this->validate(request(), [
            'code' => 'required|max:2|unique:account_assignments,code,'. $id .'',
            'description' => 'required|max:30',
            'release_group_id' => 'required|exists:release_groups,id',
        ]);

        $save = $release->update([
            'code' => $request->code,
            'description' => $request->description,
            'release_group_id' => $request->release_group_id,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $release;
        } else {
            return response()->json([
                'message' => 'Failed Update Data',
            ], 400);
        }
    }

    public function activate($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $release = ReleaseStrategy::findOrFail($id);

        $save = $release->update([
            'active' => 1,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $release;
        } else {
            return response()->json([
                'message' => 'Failed activate Data',
            ], 400);
        }
    }

    public function deactivate($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $release = ReleaseStrategy::findOrFail($id);

        $save = $release->update([
            'active' => 0,
            'updated_by' => Auth::user()->id
        ]);

        if ($save) {
            return $release;
        } else {
            return response()->json([
                'message' => 'Failed activate Data',
            ], 400);
        }
    }

    public function delete($id)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        // check if release strategy used in po
        $po_strategy = PurchaseHeader::where('release_strategy_id', $id)->first();
        if ($po_strategy) {
            return response()->json([
                'message' => 'can\'t delete release strategy, Release strategy already used in PO',
            ], 422);
        }

        $delete = ReleaseStrategy::findOrFail($id)->update([
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
        Auth::user()->cekRoleModules(['release-strategy-update']);

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
            'id.*'        => 'required|exists:release_strategies,id',
        ]);

        try {
            DB::beginTransaction();

            foreach (request()->id as $ids) {
                // check if release strategy used in po
                $po_strategy = PurchaseHeader::where('release_strategy_id', $ids)->first();
                if ($po_strategy) {
                    throw new \Exception('can\'t delete release strategy, Release strategy already used in PO');
                }

                $delete = ReleaseStrategy::findOrFail($ids)->update([
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

    public function assignCode($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'release_code_id'     => 'nullable|array',
            'release_code_id.*'   => 'nullable|exists:release_codes,id',
        ]);

        $strategy = ReleaseStrategy::findOrFail($id);

        // check if release strategy used in po
        $po_strategy = PurchaseHeader::where('release_strategy_id', $id)->first();
        if ($po_strategy) {
            return response()->json([
                'message' => 'can\'t modify release strategy, Release strategy already used in PO',
            ], 422);
        }

        // get assign release code from db
        $code_strategy = CodeStrategy::where('release_strategy_id', $id)->pluck('release_code_id')->toArray();

        // get release code from request
        $new = array_map('intval', $request->release_code_id);

        // get unique release code from db & request
        $unique = array_unique(array_merge($code_strategy,$new), SORT_REGULAR);

        // count unique
        $count = collect($unique)->count();

        // validate max release code in 1 release strategy is 4
        if ($count > 4) {
            return response()->json([
                'message' => 'total release code can\'t more than 4'
            ], 422);
        }

        if (in_array(null, $request->release_code_id, true) || in_array('', $request->release_code_id, true)) {
            $strategy->release_code()->detach();
        } else {
            $strategy->release_code()->sync($request->release_code_id);

            // get release code assigned to release strategy
            $code = $strategy->release_code->pluck('code');

            // get matrix by total release code assigned to release strategy
            $value = json_encode($this->statusMatrix($code));

            // update or create release status
            $status = ReleaseStatus::where('release_strategy_id', $id)->first();

            if ($status) {
                $status->update([
                    'value' => $value,
                    'updated_by' => Auth::user()->id
                ]);
            } else {
                ReleaseStatus::create([
                    'release_strategy_id' => $id,
                    'value' => $value,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);
            }
        }

        return $strategy->release_code;
    }

    public function addCode($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'release_code_id'   => 'required|exists:release_codes,id',
        ]);

        $strategy = ReleaseStrategy::findOrFail($id);

        // check if release strategy used in po
        $po_strategy = PurchaseHeader::where('release_strategy_id', $id)->first();
        if ($po_strategy) {
            return response()->json([
                'message' => 'can\'t modify release strategy, Release strategy already used in PO',
            ], 422);
        }

        // validate max release code in 1 release strategy is 4
        if (count($strategy->release_code) == 4) {
            return response()->json([
                'message' => 'total release code can\'t more than 4'
            ], 422);
        }

        if ($strategy->release_code->contains($request->release_code_id)) {
            return response()->json([
                'message' => 'release code can\'t insert again'
            ], 422);
        }

        $strategy->release_code()->attach($request->release_code_id);

        // get release code assigned to release strategy
        $code = CodeStrategy::where('release_strategy_id', $strategy->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->transform( function ($data) {
                // get release code
                $release_code = ReleaseCode::find($data->release_code_id);

                $new = [
                    'code' => $release_code ? $release_code->code : null
                ];

                return $new;
            })->pluck('code');

        // get matrix by total release code assigned to release strategy
        $value = json_encode($this->statusMatrix($code));

        // update or create release status
        $status = ReleaseStatus::where('release_strategy_id', $id)->first();

        if ($status) {
            $status->update([
                'value' => $value,
                'updated_by' => Auth::user()->id
            ]);
        } else {
            ReleaseStatus::create([
                'release_strategy_id' => $id,
                'value' => $value,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
        }

        return $strategy->release_code;
    }

    public function deleteCode($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $this->validate(request(), [
            'release_code_id'   => 'required|exists:release_codes,id',
        ]);

        $strategy = ReleaseStrategy::findOrFail($id);

        // check if release strategy used in po
        $po_strategy = PurchaseHeader::where('release_strategy_id', $id)->first();
        if ($po_strategy) {
            return response()->json([
                'message' => ' can\'t modify release strategy, Release strategy already used in PO',
            ], 422);
        }

        if (!$strategy->release_code->contains($request->release_code_id)) {
            return response()->json([
                'message' => 'release code not found'
            ], 422);
        }

        $strategy->release_code()->detach($request->release_code_id);

        // get release code assigned to release strategy
        $code = CodeStrategy::where('release_strategy_id', $strategy->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->transform( function ($data) {
                // get release code
                $release_code = ReleaseCode::find($data->release_code_id);

                $new = [
                    'code' => $release_code ? $release_code->code : null
                ];

                return $new;
            })->pluck('code');

        if (count($code) > 0) {
            // get matrix by total release code assigned to release strategy
            $value = json_encode($this->statusMatrix($code));

            // update or create release status
            $status = ReleaseStatus::where('release_strategy_id', $id)->first();

            if ($status) {
                $status->update([
                    'value' => $value,
                    'updated_by' => Auth::user()->id
                ]);
            } else {
                ReleaseStatus::create([
                    'release_strategy_id' => $id,
                    'value' => $value,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id
                ]);
            }
        } else {
            $status = ReleaseStatus::where('release_strategy_id', $id)->first();

            if ($status) {
                $status->delete();
            }
        }

        return $strategy->release_code;
    }

    public function storeClassification($id, Request $request)
    {
        Auth::user()->cekRoleModules(['release-strategy-update']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $release_strategy = ReleaseStrategy::findOrFail($id);

        // check if release strategy used in po
        $po_strategy = PurchaseHeader::where('release_strategy_id', $id)->first();
        if ($po_strategy) {
            return response()->json([
                'message' => 'can\'t modify release strategy, Release strategy already used in PO',
            ], 422);
        }

        $parameter = $request->input('parameter');

        try {
            DB::beginTransaction();

            if (is_array($parameter) || is_object($parameter))
            {
                foreach($parameter as $key => $value)
                {
                    $id_parameter   = explode("-",$key)[1];
                    $value_parameter= $value;
                    $insert_param   = ReleaseStrategyParameter::updateOrCreate(
                        [
                            'release_strategy_id'           => $release_strategy->id,
                            'classification_parameter_id'   => $id_parameter,
                        ],
                        [
                            'value'         => json_encode($value_parameter),
                            'created_by'    => Auth::user()->id,
                            'updated_by'    => Auth::user()->id
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Success save data'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'error save data',
                'detail' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);
        }
    }

    public function statusMatrix($x)
    {
        $total = count($x);
        switch ($total) {
            case 1:
                $matrix = [
                    [$x['0'] => null, 'indicator' => 0],
                    [$x['0'] => 1, 'indicator' => 1]
                ];

                break;
            case 2:
                $matrix = [
                    [$x['0'] => null, $x['1'] => null, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => 1, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => null, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => 1, 'indicator' => 1]
                ];

                break;
            case 3:
                $matrix = [
                    [$x['0'] => null, $x['1'] => null, $x['2'] => null, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => null, $x['2'] => 1, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => 1, $x['2'] => null, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => 1, $x['2'] => 1, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => null, $x['2'] => null, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => null, $x['2'] => 1, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => 1, $x['2'] => null, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => 1, $x['2'] => 1, 'indicator' => 1]
                ];

                break;
            case 4:
                $matrix = [
                    [$x['0'] => null, $x['1'] => null, $x['2'] => null, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => null, $x['2'] => null, $x['3'] => 1, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => null, $x['2'] => 1, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => null, $x['2'] => 1, $x['3'] => 1, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => 1, $x['2'] => null, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => 1, $x['2'] => null, $x['3'] => 1, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => 1, $x['2'] => 1, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => null, $x['1'] => 1, $x['2'] => 1, $x['3'] => 1, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => null, $x['2'] => null, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => null, $x['2'] => null, $x['3'] => 1, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => null, $x['2'] => 1, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => null, $x['2'] => 1, $x['3'] => 1, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => 1, $x['2'] => null, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => 1, $x['2'] => null, $x['3'] => 1, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => 1, $x['2'] => 1, $x['3'] => null, 'indicator' => 0],
                    [$x['0'] => 1, $x['1'] => 1, $x['2'] => 1, $x['3'] => 1, 'indicator' => 1],
                ];

                break;
        }

        return $matrix;
    }

    public function status($id)
    {
        Auth::user()->cekRoleModules(['release-strategy-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $status = ReleaseStatus::where('release_strategy_id', $id)->first();

        if (!$status) {
            return response()->json([
                'message' => 'release status not found'
            ], 404);
        }

        return json_decode($status->value);
    }

    public function updateStatus($id)
    {
        Auth::user()->cekRoleModules(['release-strategy-view']);

        try {
            $id = HashId::decode($id);
        } catch(\Exception $ex) {
            return response()->json([
                'message' => 'ID is not valid. ERROR:'.$ex->getMessage(),
            ], 400);
        }

        $strategy = ReleaseStrategy::find($id);

        // check if release strategy used in po
        $po_strategy = PurchaseHeader::where('release_strategy_id', $id)->first();
        if ($po_strategy) {
            return response()->json([
                'message' => 'can\'t modify release strategy, Release strategy already used in PO',
            ], 422);
        }

        if (!$strategy) {
            return response()->json([
                'message' => 'release strategy not found'
            ], 404);
        }

        $this->validate(request(), [
            'indicator'     => 'required|array',
            'indicator.*'   => 'required|boolean',
        ]);

        // validation
        $total_indicator = count(request()->indicator);

        $pangkat = count($strategy->release_code);

        // total matrix = 2 pangkat n, n = total release code assigned in release strategy
        $total_matrix = pow(2, $pangkat);

        if ($total_indicator != $total_matrix) {
            return response()->json([
                'message' => 'total indicator must equal to total matrix ('.$total_matrix.')'
            ], 422);
        }

        // get release code assigned to release strategy
        $code = $strategy->release_code->pluck('code');

        // get matrix by total release code assigned to release strategy
        $val = $this->statusMatrix($code);

        // map indicator to new request
        foreach(request()->indicator as $k => $v) {
            $val[$k]['indicator'] = (float)$v;
        }

        // encode value
        $value_json = json_encode($val);

        // update or create release status
        $status = ReleaseStatus::where('release_strategy_id', $id)->first();

        if (!$status) {
            $status = ReleaseStatus::create([
                'release_strategy_id' => $id,
                'value' => $value_json,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);
        } else {
            $status->update([
                'value' => $value_json,
                'updated_by' => Auth::user()->id
            ]);
        }

        $release_status = ReleaseStatus::where('release_strategy_id', $id)->first();

        return json_decode($release_status->value);
    }
}
