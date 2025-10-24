<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OptionList;

class OptionListController extends Controller
{
    public function index(Request $request)
    {
        $groups = $request->query('groups');
        $groupArr = $groups ? array_filter(array_map('trim', explode(',', $groups))) : [];

        $q = OptionList::query()->orderBy('group_key')->orderBy('sort_order');
        if (!empty($groupArr)) $q->whereIn('group_key', $groupArr);

        $rows = $q->get();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->group_key][] = ['value'=>$row->value, 'label'=>$row->label];
        }

        return response()->json(['data' => $grouped]);
    }
}
