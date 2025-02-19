<?php

namespace App\Http\Responses;

use App\Services\DataTableAdapter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataTableResponse
{
    public static function load(Request $request, Builder $builder)
    {
        $dataLoad = DataTableAdapter::load($builder, $request);
        return response()->json($dataLoad);
    }
}
