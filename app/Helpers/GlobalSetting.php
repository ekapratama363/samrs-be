<?php
if (!function_exists('appsetting')) {
    function appsetting($key=NULL, $default_value_if_not_exists=NULL) {
        if(empty($key)) return NULL;

        $setting = \App\Models\Setting::find($key);
        if(empty($setting)) return $default_value_if_not_exists;
        return $setting->value;
    }
}