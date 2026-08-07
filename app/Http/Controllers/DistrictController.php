<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\View\View;

class DistrictController extends Controller
{
    public function index(): View
    {
        return view('districts.index');
    }

    public function create(): View
    {
        return view('districts.create');
    }

    public function edit(District $district): View
    {
        $district->load('city');

        return view('districts.edit', [
            'district' => $district,
        ]);
    }
}
