<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Exports\TransactionsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Inisialisasi Query Dasar
        $query = Transaction::query();

        // 2. Terapkan Filter Bulan (Jika ada)
        if ($request->filled('filter_month')) {
            $date = Carbon::parse($request->filter_month);
            $query->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month);
        }

        // 3. Ambil data transaksi (Gunakan clone agar filter tidak merusak query agregat di bawah)
        $transactions = (clone $query)->orderBy('created_at', 'desc')->get();
        
        // 4. Hitung Total (Gunakan clone untuk menjaga integritas filter bulan)
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // 5. Top Expense (Biasanya top expense tetap mengikuti filter bulan yang dipilih)
        $topExpense = (clone $query)->where('type', 'expense')
            ->selectRaw('description, SUM(amount) as total')
            ->groupBy('description')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        return view('dashboard', compact('transactions', 'totalIncome', 'totalExpense', 'balance', 'topExpense'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1|max:1000000000',
            'type' => 'required|in:income,expense'
        ]);

        Transaction::create($validated);

        return redirect('/')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1|max:1000000000',
            'type' => 'required|in:income,expense'
        ]);

        $transaction = Transaction::findOrFail($id);
        $transaction->update($validated);

        return redirect('/')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        
        return redirect('/')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $month = $request->get('month');
        
        $fileName = 'Laporan_Keuangan_UP_SMK_Taruna_Bangsa';
        
        if ($month) {
            try {
                // Gunakan Carbon agar lebih aman saat parsing format Y-m
                $fileName .= '_' . Carbon::parse($month)->format('F_Y');
            } catch (\Exception $e) {
                // Fallback jika format tanggal tidak valid
            }
        }
        
        $fileName .= '.xlsx';

        return Excel::download(new TransactionsExport($month), $fileName);
    }
}