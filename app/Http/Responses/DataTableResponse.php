<?php

namespace App\Http\Responses;

use App\Services\DataTableAdapter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataTableResponse
{
    public static function load(Builder $builder)
    {
        try {
            $request = request();
            $dataLoad = DataTableAdapter::load($builder, $request);
            return response()->json($dataLoad, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
