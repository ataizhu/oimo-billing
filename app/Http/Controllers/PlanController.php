<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

class PlanController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $plans = Plan::all();
        return View::make('plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        return View::make('plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'features' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $features = $request->features ? array_filter(explode("\n", $request->features)) : null;

        Plan::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'features' => $features,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('plans.index')
            ->with('success', 'План успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan) {
        return view('plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'features' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $features = $request->features ? array_filter(explode("\n", $request->features)) : null;

        $plan->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'billing_period' => $request->billing_period,
            'features' => $features,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('plans.index')
            ->with('success', 'План успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan) {
        $plan->delete();
        return redirect()->route('plans.index')
            ->with('success', 'План успешно удален');
    }
}
