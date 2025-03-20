<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Date;

class SubscriptionController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $subscriptions = Auth::user()->subscriptions;
        return View::make('subscriptions.index', compact('subscriptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        $plans = Plan::where('is_active', true)->get();
        return View::make('subscriptions.create', compact('plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'trial_days' => 'nullable|integer|min:0',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $trialEndsAt = $request->trial_days ? Date::now()->addDays($request->trial_days) : null;
        $startsAt = $trialEndsAt ? $trialEndsAt : Date::now();
        $endsAt = $plan->billing_period === 'monthly' ? $startsAt->addMonth() : $startsAt->addYear();

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'active',
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        // Создаем счет для подписки
        $subscription->invoices()->create([
            'user_id' => Auth::id(),
            'number' => 'INV-' . str_pad($subscription->id, 6, '0', STR_PAD_LEFT),
            'amount' => $plan->price,
            'status' => 'pending',
            'due_date' => $startsAt,
            'items' => [
                [
                    'description' => $plan->name . ' (' . ucfirst($plan->billing_period) . ')',
                    'amount' => $plan->price,
                ]
            ],
        ]);

        return Redirect::route('subscriptions.index')
            ->with('success', 'Подписка успешно создана');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription) {
        $this->authorize('view', $subscription);
        return View::make('subscriptions.show', compact('subscription'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }

    public function cancel(Subscription $subscription) {
        $this->authorize('update', $subscription);

        if (!$subscription->isCanceled()) {
            $subscription->update([
                'canceled_at' => Date::now(),
            ]);
        }

        return Redirect::route('subscriptions.show', $subscription)
            ->with('success', 'Подписка успешно отменена');
    }

    public function resume(Subscription $subscription) {
        $this->authorize('update', $subscription);

        if ($subscription->isCanceled() && !$subscription->hasEnded()) {
            $subscription->update([
                'canceled_at' => null,
            ]);
        }

        return Redirect::route('subscriptions.show', $subscription)
            ->with('success', 'Подписка успешно возобновлена');
    }
}
