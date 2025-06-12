<?php

namespace App\QueryBuilders\User;

use App\Models\User;
use Illuminate\Http\Request;

class UserQueryBuilder
{
    public static function filterList(Request $request)
    {
        $builder = User::query();

        if (!empty($key = $request->search)) {
            $builder = $builder->search($key);
        }

        return $builder;
    }
}
