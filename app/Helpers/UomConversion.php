<?php
if (!function_exists('uomconversion')) {
    function uomconversion($material_id, $uom_id, $value) {
        if(empty($material_id) | empty($uom_id) | empty($value)) return NULL;

        $material = \App\Models\Material::find($material_id);
        if(!$material) return NULL;

        $convertion_row = $material->uom_conversion()->wherePivot('uom_id', $uom_id)->first();

        if (!$convertion_row) {
            return NULL;
        }

        $pivot = $convertion_row->pivot;

        // if ((float)($pivot->base_value) > (float)($pivot->value_conversion)) {
        //     $covertion_value = (float)(($value / $pivot->base_value) * $pivot->value_conversion);
        // } else {
        //     $covertion_value = (float)(($value * $pivot->value_conversion) / $pivot->base_value);
        // }

        $covertion_value = (float)(($value * $pivot->base_value) * $pivot->value_conversion);
        
        return $covertion_value;
    }
}