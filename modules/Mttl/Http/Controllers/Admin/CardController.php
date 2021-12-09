<?php

namespace Modules\Mttl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Mttl\Models\EnergyCard;

class CardController extends Controller
{
    public function index()
    {
        $types = EnergyCard::$typeMap;
        foreach ($types as $key => $type) {
            $types[$key] = trans($type);
        }
        return view('mttl::card.index', [
            'types' => $types
        ]);
    }

    public function create()
    {
        $types = EnergyCard::$typeMap;
        foreach ($types as $key => $type) {
            $types[$key] = trans($type);
        }
        return view('mttl::card.create', [
            'types' => $types
        ]);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $data = EnergyCard::query()->find($id);
        $types = EnergyCard::$typeMap;
        foreach ($types as $key => $type) {
            $types[$key] = trans($type);
        }
        return view('mttl::card.edit', [
            'data' => $data,
            'types' => $types,
            'data_id' => $id
        ]);
    }
}
