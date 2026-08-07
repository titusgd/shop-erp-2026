<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\View\View;

class CityController extends Controller
{
    public function index(): View
    {
        return view('cities.index');
    }

    public function create(): View
    {
        return view('cities.create');
    }

    public function edit(City $city): View
    {
        return view('cities.edit', [
            'city' => $city,
        ]);
    }
}
