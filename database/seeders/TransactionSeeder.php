<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama biar bersih pas ngetes
        Transaction::truncate();

        $descriptions = [
            'income' => ['Penjualan Produk Roti', 'Sewa Alat Produksi', 'Jasa Catering', 'Penjualan Snack Box', 'Donasi Unit Produksi'],
            'expense' => ['Beli Bahan Baku', 'Bah
            
            yar Listrik UP', 'Beli Kemasan Plastik', 'Transport Pengantaran', 'Servis Oven', 'Uang Kebersihan']
        ];

        for ($i = 1; $i <= 90; $i++) {
            $type = rand(0, 1) ? 'income' : 'expense';
            
            Transaction::create([
                'description' => $descriptions[$type][array_rand($descriptions[$type])],
                'amount'      => rand(5, 50) * 10000, // Random antara 50rb - 500rb
                'type'        => $type,
                // Kita bikin tanggalnya acak mundur sampai 90 hari ke belakang
                'created_at'  => Carbon::now()->subDays(rand(0, 90)),
                'updated_at'  => Carbon::now()
            ]);
        }
    }
}