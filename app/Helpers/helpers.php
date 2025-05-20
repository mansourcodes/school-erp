<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

function getPaymentSourceArray()
{
    $array =  Config::get('settings.payment_sources');
    if (empty($array)) {
        return null;
    }
    $flattened = Arr::flatten(json_decode($array, true));
    foreach ($flattened as $i => $value) {
        $key = Str::upper(Str::snake(Str::lower($value)));
        unset($flattened[$i]);
        $flattened[$key] = $key;
    }

    return $flattened;
}

function getPaymentTypesArray()
{
    $array =  Config::get('settings.payment_types');
    if (empty($array)) {
        return null;
    }
    $flattened = Arr::flatten(json_decode($array, true));
    foreach ($flattened as $i => $value) {
        $key = Str::upper(Str::snake(Str::lower($value)));
        unset($flattened[$i]);
        $flattened[$key] = $key;
    }

    return $flattened;
}

function titleCase($value)
{
    return Str::title(str_replace('_', ' ', $value));
}


function getReportClassFunctions($class, $route, $id)
{

    $list = [];
    $class_methods = get_class_methods($class);
    foreach ($class_methods as $method_name) {
        $functionOriginalName = substr($method_name, 0, -1);
        if (substr($method_name, -1) === '_') {

            array_push($list, [
                'label' => trans('reports.' . Str::of($functionOriginalName)->snake()),
                'url' => backpack_url($route . '?view=' . $functionOriginalName . '&course=' . $id),
            ]);
        }
    }
    return $list;
}


function encodeArabicForJsonSqlSearch($word)
{
    $str_encoded = substr(json_encode($word), 1, -1);
    $str_encoded = str_replace('\\', '%\\', $str_encoded);

    return $str_encoded;
}


function isArabic($word) {
    // Arabic Unicode range: \u0600-\u06FF
    return preg_match('/[\x{0600}-\x{06FF}]/u', $word) === 1;
}