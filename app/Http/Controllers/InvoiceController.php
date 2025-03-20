<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;

class InvoiceController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $invoices = Auth::user()->invoices()->latest()->paginate(10);
        return View::make('invoices.index', compact('invoices'));
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
    public function show(Invoice $invoice) {
        $this->authorize('view', $invoice);
        return View::make('invoices.show', compact('invoice'));
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

    public function pay(Invoice $invoice) {
        $this->authorize('update', $invoice);

        if (!$invoice->isPaid()) {
            // Здесь будет интеграция с платежной системой
            $invoice->markAsPaid();
        }

        return Redirect::route('invoices.show', $invoice)
            ->with('success', 'Счет успешно оплачен');
    }

    public function download(Invoice $invoice) {
        $this->authorize('view', $invoice);

        // Здесь будет генерация PDF
        return response()->download(
            storage_path('app/invoices/invoice-' . $invoice->number . '.pdf')
        );
    }
}
