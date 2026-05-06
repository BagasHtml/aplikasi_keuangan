Saya akan buatkan dokumentasi lengkap dan sekaligus **memperbaiki masalah** yang Anda alami! Dari gambar yang Anda kirim, masalah utamanya adalah:

1. **Header "JUMLAH (RP)"** membuat rumus SUM tidak jalan (perlu di-cut dulu)
2. **Database menggunakan SQLite**
3. **Perlu effort lebih dalam instalasi**

## 📚 DOKUMENTASI LENGKAP + SOLUSI

# Panduan Instalasi & Konfigurasi Export Excel dengan Auto SUM
## Untuk Aplikasi Laravel dengan Database SQLite

---

## 📋 Daftar Isi
1. [Persiapan Environment](#1-persiapan-environment)
2. [Instalasi Laravel & Package](#2-instalasi-laravel--package)
3. [Konfigurasi Database SQLite](#3-konfigurasi-database-sqlite)
4. [Membuat Model & Migration](#4-membuat-model--migration)
5. [Membuat Export Class (SOLUSI)](#5-membuat-export-class-solusi)
6. [Membuat Controller & Routes](#6-membuat-controller--routes)
7. [Testing & Debugging](#7-testing--debugging)
8. [Troubleshooting Lengkap](#8-troubleshooting-lengkap)

---

## 1. Persiapan Environment

### Requirements
```bash
PHP >= 8.0
Composer >= 2.0
SQLite3 >= 3.35
Laravel >= 9.0
```

### Cek Versi PHP & Composer
```bash
php -v
composer -v
sqlite3 --version
```

---

## 2. Instalasi Laravel & Package

### Install Laravel Baru (Opsional)
```bash
composer create-project laravel/laravel aplikasi-keuangan
cd aplikasi-keuangan
```

### Install Package Excel
```bash
composer require maatwebsite/excel
```

### Publish Config (Opsional)
```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

---

## 3. Konfigurasi Database SQLite

### Langkah 1: Buat File Database SQLite
```bash
# Di terminal
touch database/database.sqlite

# Atau di Windows
type nul > database/database.sqlite
```

### Langkah 2: Setting .env
```env
# Buka file .env dan ubah:

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# Ganti dengan ini:
DB_CONNECTION=sqlite
DB_DATABASE=/full/path/to/your/project/database/database.sqlite
```

**Contoh path absolut:**
```env
# Windows
DB_DATABASE=C:\xampp\htdocs\aplikasi-keuangan\database\database.sqlite

# Linux/Mac
DB_DATABASE=/opt/lampp/htdocs/aplikasi-keuangan/database/database.sqlite
```

### Langkah 3: Verifikasi Koneksi
```bash
php artisan migrate:status
```

Jika muncul error permission:
```bash
# Linux/Mac
chmod 777 database/database.sqlite
chmod 777 database

# Windows (run as administrator)
icacls database /grant "Everyone:(OI)(CI)F"
```

---

## 4. Membuat Model & Migration

### Membuat Migration
```bash
php artisan make:migration create_transactions_table
```

### Isi Migration
```php
<?php
// database/migrations/2026_01_01_000000_create_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->enum('type', ['income', 'expense']); // income = Masuk, expense = Keluar
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
```

### Jalankan Migration
```bash
php artisan migrate
```

### Buat Model
```bash
php artisan make:model Transaction
```

### Isi Model
```php
<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'type',
        'amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];
}
```

### Seeder untuk Data Contoh
```bash
php artisan make:seeder TransactionSeeder
```

```php
<?php
// database/seeders/TransactionSeeder.php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $transactions = [
            [
                'description' => 'beli kopi',
                'type' => 'income',
                'amount' => 25000,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'beli kopi',
                'type' => 'expense',
                'amount' => 12000,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'beli baju',
                'type' => 'expense',
                'amount' => 105000,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'beli kopi',
                'type' => 'income',
                'amount' => 25000,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'beli kopi',
                'type' => 'income',
                'amount' => 300,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'ada',
                'type' => 'expense',
                'amount' => 300,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'beli kopi',
                'type' => 'income',
                'amount' => 300000,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'beli baju',
                'type' => 'expense',
                'amount' => 300000,
                'created_at' => Carbon::parse('2026-05-02')
            ],
            [
                'description' => 'pembelian alat bangkar laptop',
                'type' => 'expense',
                'amount' => 108000,
                'created_at' => Carbon::parse('2026-05-06')
            ],
        ];

        foreach ($transactions as $transaction) {
            Transaction::create($transaction);
        }
    }
}
```

### Jalankan Seeder
```bash
php artisan db:seed --class=TransactionSeeder
```

---

## 5. Membuat Export Class (SOLUSI)

### 🔥 INI SOLUSI UNTUK MASALAH HEADER "JUMLAH (RP)" 🔥

```bash
php artisan make:export TransactionsExport
```

### **Kode LENGKAP yang SUDAH DIPERBAIKI**
```php
<?php
// app/Exports/TransactionsExport.php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class TransactionsExport implements 
    FromQuery, 
    WithMapping, 
    WithHeadings, 
    WithEvents, 
    WithDrawings, 
    WithTitle, 
    WithColumnFormatting,
    WithCustomStartCell
{
    protected ?string $month;
    protected $rowCount;

    public function __construct(?string $month = null)
    {
        $this->month = $month;
        $this->rowCount = $this->getRowCount();
    }

    public function title(): string 
    { 
        return 'Laporan Keuangan'; 
    }
    
    public function startCell(): string 
    { 
        return 'B9'; 
    }

    public function query()
    {
        $query = Transaction::query();
        
        if ($this->month) {
            $date = Carbon::parse($this->month);
            $query->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month);
        }
        
        return $query->orderBy('created_at', 'asc');
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->created_at->format('d/m/Y'),
            $transaction->description,
            $transaction->type == 'income' ? 'Masuk' : 'Keluar',
            $transaction->amount,
        ];
    }

    /**
     * 🔥 KRUSIAL: Gunakan 'JUMLAH' bukan 'JUMLAH (RP)' 🔥
     * Ini SOLUSI untuk masalah SUM tidak otomatis!
     */
    public function headings(): array
    {
        return [
            'ID', 
            'TANGGAL', 
            'KETERANGAN', 
            'JENIS', 
            'JUMLAH'  // <-- TANPA TANDA KURUNG!
        ];
    }

    public function columnFormats(): array 
    { 
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        ]; 
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $logoPath = storage_path('app/public/logo_up.png');
        
        if (file_exists($logoPath)) {
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('B2');
            return $drawing;
        }
        
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Setup semua style
                $this->setupHeader($event, $sheet);
                
                $startRow = 9;
                $endRow = $startRow + $this->rowCount - 1;
                
                if ($this->rowCount > 0) {
                    $this->setupTableStyle($event, $sheet, $startRow, $endRow);
                    $this->setupFooter($event, $sheet, $startRow, $endRow);
                } else {
                    $this->setupEmptyData($event, $sheet);
                }
            },
        ];
    }

    private function getRowCount(): int
    {
        $query = Transaction::query();
        
        if ($this->month) {
            $date = Carbon::parse($this->month);
            $query->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month);
        }
        
        return $query->count();
    }

    private function setupHeader(AfterSheet $event, Worksheet $sheet): void
    {
        // Set lebar kolom
        $sheet->getColumnDimension('B')->setWidth(8); 
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(24); // Lebar cukup untuk angka

        // Judul Laporan
        $sheet->mergeCells('D2:F2');
        $sheet->setCellValue('D2', 'LAPORAN KEUANGAN UNIT PRODUKSI');
        $sheet->setCellValue('D3', 'SMK Taruna Bangsa');
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D3')->getFont()->setSize(11);

        // Periode
        if ($this->month) {
            $date = Carbon::parse($this->month);
            $sheet->setCellValue('D4', 'Periode: ' . $date->format('F Y'));
            $sheet->getStyle('D4')->getFont()->setItalic(true);
        }

        // Style Header Tabel (baris 8)
        $event->sheet->getStyle('B8:F8')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF3498DB']
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN],
                'top' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
    }

    private function setupTableStyle(AfterSheet $event, Worksheet $sheet, int $startRow, int $endRow): void
    {
        // Border untuk semua cell data
        $event->sheet->getStyle("B{$startRow}:F{$endRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Rata tengah untuk ID, Tanggal, Jenis
        $event->sheet->getStyle("B{$startRow}:B{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle("C{$startRow}:C{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $event->sheet->getStyle("E{$startRow}:E{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Rata kanan untuk kolom Jumlah
        $event->sheet->getStyle("F{$startRow}:F{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Rata kiri untuk Keterangan
        $event->sheet->getStyle("D{$startRow}:D{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Warna Zebra (baris genap)
        for ($row = $startRow; $row <= $endRow; $row++) {
            if ($row % 2 == 0) {
                $event->sheet->getStyle("B{$row}:F{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF9F9F9']
                    ]
                ]);
            }
        }
    }

    /**
     * 🔥 RUMUS SUM OTOMATIS 🔥
     * Sekarang akan menghasilkan =SUM(F9:F17) bukan =SUM(F9,F10,F11,...)
     */
    private function setupFooter(AfterSheet $event, Worksheet $sheet, int $startRow, int $endRow): void
    {
        $footerRow = $endRow + 1;
        
        // Gabungkan kolom B sampai E untuk label
        $sheet->mergeCells("B{$footerRow}:E{$footerRow}");
        $sheet->setCellValue("B{$footerRow}", 'TOTAL SALDO AKHIR');
        
        // 🔥 RUMUS SUM RANGE (BUKAN PER CELL) 🔥
        $sheet->setCellValue("F{$footerRow}", "=SUM(F{$startRow}:F{$endRow})");
        
        // Style Footer
        $event->sheet->getStyle("B{$footerRow}:F{$footerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1C40F']
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THICK],
                'bottom' => ['borderStyle' => Border::BORDER_THICK],
                'left' => ['borderStyle' => Border::BORDER_THIN],
                'right' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Format Rupiah untuk total
        $event->sheet->getStyle("F{$footerRow}")
              ->getNumberFormat()
              ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    }

    private function setupEmptyData(AfterSheet $event, Worksheet $sheet): void
    {
        $sheet->mergeCells('B10:F10');
        $sheet->setCellValue('B10', 'Tidak ada data transaksi untuk periode ini');
        $sheet->getStyle('B10')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('B10')->getFont()->getColor()->setARGB('FFE74C3C');
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('B11:E11');
        $sheet->setCellValue('B11', 'TOTAL SALDO AKHIR');
        $sheet->setCellValue('F11', 0);
        
        $event->sheet->getStyle("B11:F11")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1C40F']
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THICK],
                'bottom' => ['borderStyle' => Border::BORDER_THICK]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        $event->sheet->getStyle("F11")
              ->getNumberFormat()
              ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    }
}
```

---

## 6. Membuat Controller & Routes

### Controller
```bash
php artisan make:controller TransactionsController
```

```php
<?php
// app/Http/Controllers/TransactionsController.php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Tampilkan semua transaksi
     */
    public function index()
    {
        $transactions = Transaction::orderBy('created_at', 'desc')->paginate(10);
        return view('transactions.index', compact('transactions'));
    }

    /**
     * Export semua data ke Excel (TANPA FILTER)
     */
    public function exportAll()
    {
        $fileName = 'laporan_keuangan_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new TransactionsExport(), $fileName);
    }
    
    /**
     * Export dengan filter bulan
     */
    public function exportFiltered(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m'
        ]);
        
        $month = $request->input('month');
        $fileName = 'laporan_keuangan';
        
        if ($month) {
            $date = \Carbon\Carbon::parse($month);
            $fileName .= '_' . $date->format('Y_m');
        }
        
        $fileName .= '_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new TransactionsExport($month), $fileName);
    }
    
    /**
     * Form export excel
     */
    public function exportForm()
    {
        return view('transactions.export');
    }
}
```

### Routes
```php
<?php
// routes/web.php

use App\Http\Controllers\TransactionsController;

Route::get('/', [TransactionsController::class, 'index']);
Route::get('/transactions', [TransactionsController::class, 'index']);

// Export routes
Route::get('/export/excel', [TransactionsController::class, 'exportAll'])->name('export.all');
Route::post('/export/excel/filtered', [TransactionsController::class, 'exportFiltered'])->name('export.filtered');
Route::get('/export/form', [TransactionsController::class, 'exportForm'])->name('export.form');
```

### Views

Buat folder views:

```bash
mkdir -p resources/views/transactions
```

**resources/views/transactions/index.blade.php**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Laporan Keuangan Unit Produksi</h1>
        <h3 class="mb-4">SMK Taruna Bangsa</h3>
        
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('export.filtered') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-auto">
                        <label>Filter Bulan:</label>
                    </div>
                    <div class="col-auto">
                        <input type="month" name="month" class="form-control">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Export Excel</button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('export.all') }}" class="btn btn-success">Export Semua</a>
                    </div>
                </form>
            </div>
        </div>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Jenis</th>
                    <th>Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td>{{ $transaction->type == 'income' ? 'Masuk' : 'Keluar' }}</td>
                    <td class="text-end">{{ number_format($transaction->amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        {{ $transactions->links() }}
    </div>
</body>
</html>
```

**resources/views/transactions/export.blade.php**
```html
<!DOCTYPE html>
<html>
<head>
    <title>Export Laporan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Export Laporan Keuangan</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('export.filtered') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="month" class="form-label">Pilih Periode Bulan</label>
                        <input type="month" name="month" id="month" class="form-control">
                        <small class="text-muted">Kosongkan untuk export semua data</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download"></i> Download Excel
                    </button>
                    <a href="{{ route('export.all') }}" class="btn btn-success">
                        Download Semua Data
                    </a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
```

---

## 7. Testing & Debugging

### Testing Export di Browser
```bash
php artisan serve
```

Buka: `http://localhost:8000/export/form`

### Debug Query SQLite
```php
// Di controller, tambahkan untuk debug:
\DB::connection()->enableQueryLog();
$transactions = Transaction::all();
dd(\DB::getQueryLog());
```

### Cek Data di SQLite
```bash
sqlite3 database/database.sqlite
.tables
SELECT * FROM transactions;
.quit
```

---

## 8. Troubleshooting Lengkap

### Masalah 1: ERROR - Header "JUMLAH (RP)" Membuat SUM Tidak Jalan

**Penyebab:** Karakter `(` dan `)` dalam heading mengganggu parsing Excel

**Solusi SUDAH DIBAHAS di atas:**
```php
// Gunakan ini:
public function headings(): array
{
    return ['ID', 'TANGGAL', 'KETERANGAN', 'JENIS', 'JUMLAH']; // TANPA KURUNG
}
```

### Masalah 2: SQLite Error - "General error: 8 attempt to write a readonly database"

**Solusi:**
```bash
# Linux/Mac
chmod 777 database
chmod 777 database/database.sqlite

# Windows (Run as Administrator)
takeown /f database
icacls database /grant "Everyone:(OI)(CI)F"
```

### Masalah 3: SQLite Error - "No such table: transactions"

**Solusi:**
```bash
# Jalankan migration ulang
php artisan migrate:fresh --seed
```

### Masalah 4: Memory Exhausted saat Export Data Besar

**Solusi - Gunakan chunking:**
```php
// Di TransactionsExport.php
public function query()
{
    return Transaction::query()->chunk(1000);
}
```

### Masalah 5: File Excel Corrupt

**Solusi:**
```bash
# Clear cache
php artisan optimize:clear
composer dump-autoload

# Hapus file lama
rm -rf storage/framework/laravel-excel/
```

### Masalah 6: Format Rupiah Tidak Muncul

**Solusi:** Gunakan formatting manual
```php
// Di setupFooter
$event->sheet->getStyle("F{$footerRow}")
      ->getNumberFormat()
      ->setFormatCode('"Rp" #,##0.00');
```

### Masalah 7: Logo Tidak Muncul

**Solusi:** Simpan logo dengan ukuran yang tepat
```bash
# Buat folder jika belum ada
mkdir -p storage/app/public

# Copy logo ke sana
cp your-logo.png storage/app/public/logo_up.png

# Link storage
php artisan storage:link
```

### Masalah 8: SUM Menjadi Teks Bukan Rumus

**Solusi:** Pastikan setCellValue tidak menggunakan tanda kutip
```php
// ✅ BENAR
$sheet->setCellValue("F{$footerRow}", "=SUM(F{$startRow}:F{$endRow})");

// ❌ SALAH (akan jadi teks)
$sheet->setCellValue("F{$footerRow}", '=SUM(F9:F17)');
```

### Masalah 9: Data Tidak Muncul di Excel

**Solusi:** Debug dengan dd()
```php
// Di map() method
public function map($transaction): array
{
    dd($transaction); // Cek apakah data ada
    return [ ... ];
}
```

---

## 📊 Hasil Akhir

Setelah semua terinstall dengan benar, ketika user men-download Excel:

1. **Header bersih**: `ID | TANGGAL | KETERANGAN | JENIS | JUMLAH`
2. **Rumus SUM otomatis**: `=SUM(F9:F17)` langsung terpasang
3. **Format Rupiah**: Semua angka terformat dengan benar
4. **Tidak perlu cut header** - Langsung jalan!

---

## 🎯 Kesimpulan

| Masalah | Solusi |
|---------|--------|
| Header "JUMLAH (RP)" | Ganti dengan "JUMLAH" |
| SQLite koneksi | Setting .env dengan path absolut |
| SUM tidak jalan | Pastikan heading tanpa karakter khusus |
| Permission error | chmod 777 pada folder database |

**Dokumentasi ini sudah mencakup semua aspek dari instalasi hingga troubleshooting.** Jika masih ada kendala, silakan cek log di `storage/logs/laravel.log` atau tanyakan lebih detail!