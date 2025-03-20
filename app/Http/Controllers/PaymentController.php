<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class PaymentController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $payments = Auth::user()->payments()->latest()->paginate(10);
        return View::make('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment) {
        $this->authorize('view', $payment);
        return View::make('payments.show', compact('payment'));
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

    public function process(Invoice $invoice, Request $request) {
        $this->authorize('update', $invoice);

        $request->validate([
            'payment_method' => 'required|in:card,bank_transfer',
            'amount' => 'required|numeric|min:0|max:' . $invoice->amount,
        ]);

        // Здесь будет интеграция с платежной системой
        $payment = $invoice->payments()->create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        // Здесь будет обработка платежа через платежную систему
        $payment->update([
            'status' => 'completed',
            'transaction_id' => 'TRANS-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
        ]);

        if ($payment->isSuccessful()) {
            $invoice->markAsPaid();
        }

        return Redirect::route('payments.show', $payment)
            ->with('success', 'Платеж успешно обработан');
    }

    public function receipt(Payment $payment) {
        $this->authorize('view', $payment);

        // Здесь будет генерация PDF
        return Response::download(
            storage_path('app/receipts/receipt-' . $payment->transaction_id . '.pdf')
        );
    }
}
