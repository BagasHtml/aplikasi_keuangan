<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();
    
        if ($request->filled('filter_month')) {
            $query->where('created_at', 'like', $request->filter_month . '%');
        }
        if ($request->filled('filter_type')) {
            $query->where('type', $request->filter_type);
        }
    
        $transactions = $query->orderBy('created_at', 'desc')->get();
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $topExpense = Transaction::where('type', 'expense')
            ->select('description', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total'))
            ->groupBy('description')
            ->orderBy('total', 'desc')
            ->take(5) // Ambil 5 teratas
            ->get();
    
        return view('dashboard', compact('transactions', 'totalIncome', 'totalExpense', 'balance', 'topExpense'));
    }
   
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|max:255',
            'type' => 'required|in:income,expense',
            // Kita batesin maksimal 1.000.000.000 (1 Miliar)
            'amount' => 'required|numeric|min:1|max:1000000000',
        ], [
            'amount.max' => 'Waduh, nominalnya kegedean Maksimal 1 Miliar ya.',
            'amount.min' => 'Nominal gak boleh 0 atau minus.',
        ]);

        // Simpan ke database jika lolos validasi
        Transaction::create($request->all());

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'description' => 'required|max:255',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:1|max:1000000000',
        ]);

        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->all());

        return redirect()->back()->with('success', 'Data berhasil diperbarui!');
    }

    public function export(Request $request)
    {
        $query = Transaction::query();
        // Ambil filter dari request agar hasil excel sama dengan yang dilihat di web
        if ($request->filled('month')) {
            $query->where('created_at', 'like', $request->month . '%');
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();
        $labelMonth = $request->month ?: 'Semua_Waktu';
        $labelType  = $request->type ?: 'Semua_Tipe';
        $filename   = "Laporan_Keuangan_{$labelMonth}_{$labelType}.xls";
        // Header untuk memaksa download file Excel
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        // Membuat isi tabel Excel
        echo "Tanggal\tDeskripsi\tTipe\tNominal\n";
        foreach ($transactions as $t) {
            $date    = $t->created_at->format('d/m/Y');
            $desc    = str_replace(["\t", "\n", "\r"], " ", $t->description); // Bersihkan karakter tab
            $type    = strtoupper($t->type == 'income' ? 'Masuk' : 'Keluar');
            $amount  = $t->amount;
            echo "{$date}\t{$desc}\t{$type}\t{$amount}\n";
        }
        exit;
    }

    /**
     * Menghapus data transaksi
     */
    public function destroy(string $id)
    {
        Transaction::destroy($id);
        return back()->with('success', 'Data berhasil dihapus!');
    }
}