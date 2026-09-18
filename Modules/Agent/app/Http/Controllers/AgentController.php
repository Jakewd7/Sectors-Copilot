<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        return view('agent::index');
    }

    public function create()
    {
        return view('agent::create');
    }

    public function store(Request $request) {}

    public function show($id)
    {
        return view('agent::show');
    }

    public function edit($id)
    {
        return view('agent::edit');
    }

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
