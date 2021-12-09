<?php

namespace Modules\Mttl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Mttl\Models\ExchangeCode;

class ExchangeCodeController extends Controller
{
    public function index()
    {
        return view('mttl::exchange_code.index');
    }

    public function create()
    {

        return view('mttl::exchange_code.create');
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        $data = ExchangeCode::query()->find($id);
        return view('mttl::exchange_code.edit', [
            'data' => $data,
            'data_id' => $id
        ]);
    }
}
