<?php

namespace Modules\Mttl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Mttl\Models\EnergyCard;

class CardBuyController extends Controller
{
    public function index()
    {
        $types = EnergyCard::$typeMap;
        foreach ($types as $key => $type) {
            $types[$key] = trans($type);
        }
        return view('mttl::card_buy.index', [
            'types' => $types
        ]);
    }
}
