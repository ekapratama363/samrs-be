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

use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        Auth::user()->cekRoleModules(['setting-view']);

        $settings = (new Setting)->newQuery();

        $settings->with(['createdBy', 'updatedBy']);

        if (request()->has('q')) {
            $q = strtolower(request()->input('q'));
            $settings->where(function($query) use ($q) {
                $query->orWhere(DB::raw("LOWER(key)"), 'LIKE', "%".$q."%");
                $query->orWhere(DB::raw("LOWER(value)"), 'LIKE', "%".$q."%");
            });
        }

        if (request()->has('sort_field')) {
            $sort_order = request()->input('sort_order') == 'asc' ? 'asc' : 'desc';
            $settings->orderBy(request()->input('sort_field'), $sort_order);
        } else {
            $settings->orderBy('sort', 'asc');
        }

        if (request()->has('per_page')) {
            $settings = $settings->paginate(request()->input('per_page'))->appends(Input::except('page'));
            $settings->transform(function($data){
                if ($data->key == 'COMPANY_LOGO') {
                    if ($data->value && Storage::disk('public')->exists($data->value)) {
                        $data->value = Storage::disk('public')->url($data->value);
                    } else {
                        $data->value = null;
                    }
                }
                return $data;
            });

            return $settings;
        } else {
            $settings = $settings->paginate(appsetting('PAGINATION_DEFAULT'))->appends(Input::except('page'));
            $settings->transform(function($data){
                if ($data->key == 'COMPANY_LOGO') {
                    if ($data->value && Storage::disk('public')->exists($data->value)) {
                        $data->value = Storage::disk('public')->url($data->value);
                    } else {
                        $data->value = null;
                    }
                }
                return $data;
            });

            return $settings;
        }
    }

    public function show($key)
    {
        Auth::user()->cekRoleModules(['setting-view']);

        $setting = Setting::with(['createdBy', 'updatedBy'])->find($key);

        if (!$setting) {
            return response()->json([
                'message' => 'setting not found'
            ], 404);
        }

        if ($key == 'COMPANY_LOGO') {
            if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                $setting->value = Storage::disk('public')->url($setting->value);
            } else {
                $setting->value = null;
            }
        }

        return $setting;
    }

    public function update(Request $request, $key)
    {
        Auth::user()->cekRoleModules(['setting-update']);

        $setting = Setting::find($key);

        if (!$setting) {
            return response()->json([
                'message' => 'setting not found'
            ], 404);
        }

        switch ($key) {
            case 'PAGINATION_DEFAULT':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:1',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'PASS_LENGTH_MIN':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:1',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'HISTORY_PASS_KEEP':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:1',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'PASS_CYCLE_LIMIT':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:1',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'LOGIN_FAILED_LIMIT':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:1',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'PASS_REGEX_DESCRIPTION':
                $this->validate(request(), [
                    'value' => 'required|string|max:191',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'PASS_REGEX':
                $this->validate(request(), [
                    'value' => 'required|string|max:191',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'COMPANY_NAME':
                $this->validate(request(), [
                    'value' => 'required|string|max:191',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'COMPANY_ADDRESS':
                $this->validate(request(), [
                    'value' => 'required|string|max:191',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'COMPANY_PHONE':
                $this->validate(request(), [
                    'value' => 'required|regex:/^[0-9\-\(\)\/\+\s]*$/',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'COMPANY_FAX':
                $this->validate(request(), [
                    'value' => 'required|regex:/^[0-9\-\(\)\/\+\s]*$/',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'COMPANY_LOGO':
                $this->validate(request(), [
                    'value' => 'nullable|image',
                ]);

                if ($request->hasFile('value')) {
                    // delete data
                    if (Storage::disk('public')->exists($setting->value)) {
                        Storage::delete($setting->value);
                    }

                    // upload data
                    if ($request->has('value')) {
                        $image_data = request()->file('value');
                        $image_ext  = request()->file('value')->getClientOriginalExtension();
                        $image_name = "logo." .$image_ext;
                        $image_path = 'images/setting';

                        $uploaded = Storage::disk('public')->putFileAs($image_path, $image_data, $image_name);

                        $setting->update([
                            'value' => $uploaded,
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                } else {
                    $setting->update([
                        'updated_by' => Auth::user()->id
                    ]);
                }

                break;
            case 'EXPIRY_BATCH':
                $this->validate(request(), [
                    'value' => 'required|boolean',
                ]);
                // 0 = not required
                // 1 = required

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'SORT_BATCH':
                $this->validate(request(), [
                    'value' => 'required|boolean',
                ]);
                // 0 = FIFO (by expiry)
                // 1 = FIFO (by GR)

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'PROPOSE_DELIV_DATE':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:0',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'LAST_ACTIVITY':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:1',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'MAX_GR_STO':
                $this->validate(request(), [
                    'value' => 'required|numeric|min:1',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'HALAL_CERTIFIED':
                $this->validate(request(), [
                    'value' => 'required|boolean',
                ]);
                // 0 = not show
                // 1 = show

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'UNIT_COST_WITH_TAX':
                $this->validate(request(), [
                    'value' => 'required|boolean',
                ]);
                // 0 = not tax
                // 1 = include tax

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            case 'ASSET_DEPRECIATION_DATE':
                $this->validate(request(), [
                    'value' => 'required|string',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
            default:
                $this->validate(request(), [
                    'value' => 'required',
                ]);

                $setting->update([
                    'value' => $request->value,
                    'updated_by' => Auth::user()->id
                ]);

                break;
        }

        return $setting;
    }
}
