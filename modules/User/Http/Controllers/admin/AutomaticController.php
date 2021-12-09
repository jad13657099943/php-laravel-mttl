<?php

namespace Modules\User\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Mttl\Models\EnergyCard;

class AutomaticController extends Controller
{
    public function index()
    {
        $types = EnergyCard::$typeMap;
        foreach ($types as $key => $type) {
            $types[$key] = trans($type);
        }
        return view('user::admin.automatic.index', [
            'types' => $types
        ]);
    }
}
