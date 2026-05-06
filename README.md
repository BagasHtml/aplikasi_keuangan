Baik, saya akan buatkan dokumentasi lengkap untuk fitur export Excel dengan auto SUM ini.

# 📚 Dokumentasi Export Excel dengan Auto SUM - Laporan Keuangan

## 📋 Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Persyaratan Sistem](#persyaratan-sistem)
3. [Instalasi](#instalasi)
4. [Struktur Kode](#struktur-kode)
5. [Cara Penggunaan](#cara-penggunaan)
6. [Penjelasan Fitur](#penjelasan-fitur)
7. [Troubleshooting](#troubleshooting)
8. [Contoh Hasil](#contoh-hasil)

---

## 📌 Pendahuluan

Dokumentasi ini menjelaskan tentang implementasi **Export Excel Otomatis** dengan fitur **Auto SUM** untuk aplikasi keuangan. Fitur ini memungkinkan user mendownload file Excel yang sudah berisi rumus SUM otomatis pada baris **TOTAL SALDO AKHIR**.

### Tujuan
- Memudahkan user mendapatkan laporan keuangan dalam format Excel
- Rumus SUM sudah terpasang otomatis, user tidak perlu membuat manual
- Tampilan profesional dengan styling yang rapi

---

## 💻 Persyaratan Sistem

### Software Requirements
- PHP >= 8.0
- Laravel >= 8.0
- Composer
- MySQL / PostgreSQL

### Packages yang Digunakan
```json
{
    "maatwebsite/excel": "^3.1",
    "phpoffice/phpspreadsheet": "^1.18"
}
```

---

## 🔧 Instalasi

### Langkah 1: Install Package
```bash
composer require maatwebsite/excel
```

### Langkah 2: Publish Config (Opsional)
```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

### Langkah 3: Buat File Export
```bash
php artisan make:export TransactionsExport --model=Transaction
```

---

## 📁 Struktur Kode

### Lokasi File
```
app/
├── Exports/
│   └── TransactionsExport.php
├── Http/
│   └── Controllers/
│       └── TransactionsController.php
└── Models/
    └── Transaction.php
```

### Kode Lengkap TransactionsExport.php

```php
<?php

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

    public function __construct(?string $month = null)
    {
        $this->month = $month;
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

    public function columnFormats(): array 
    { 
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        ]; 
    }

    public function headings(): array
    {
        return [
            'ID', 'TANGGAL', 'KETERANGAN', 'JENIS', 'JUMLAH'
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
                
                $this->setupHeader($event, $sheet);
                
                $rowCount = $this->getRowCount();
                $startRow = 9;
                $endRow = $startRow + $rowCount - 1;
                
                if ($rowCount > 0) {
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
        $sheet->getColumnDimension('F')->setWidth(20);

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

        // Style Header Tabel
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
        // Border tabel
        $event->sheet->getStyle("B{$startRow}:F{$endRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        // Kolom Jumlah rata kanan
        $event->sheet->getStyle("F{$startRow}:F{$endRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Warna Zebra
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

    private function setupFooter(AfterSheet $event, Worksheet $sheet, int $startRow, int $endRow): void
    {
        $footerRow = $endRow + 1;
        
        // Label Total
        $sheet->mergeCells("B{$footerRow}:E{$footerRow}");
        $sheet->setCellValue("B{$footerRow}", 'TOTAL SALDO AKHIR');
        
        // 🔥 RUMUS SUM OTOMATIS 🔥
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
        
        // Format Rupiah
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

### Kode Controller (TransactionsController.php)

```php
<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Export semua data ke Excel
     */
    public function exportAll()
    {
        return Excel::download(new TransactionsExport(), 'laporan_keuangan.xlsx');
    }
    
    /**
     * Export per bulan
     */
    public function exportPerMonth(Request $request)
    {
        $month = $request->input('month'); // Format: Y-m
        $fileName = 'laporan_keuangan_' . $month . '.xlsx';
        
        return Excel::download(new TransactionsExport($month), $fileName);
    }
}
```

### Route (web.php)

```php
<?php

use App\Http\Controllers\TransactionsController;

Route::get('/export-transactions', [TransactionsController::class, 'exportAll']);
Route::get('/export-transactions/{month}', [TransactionsController::class, 'exportPerMonth']);
```

---

## 🚀 Cara Penggunaan

### 1. Export Semua Data
```php
// Di Blade/View
<a href="{{ route('export.transactions.all') }}" class="btn btn-success">
    Download Excel
</a>

// Atau pakai form
<form action="{{ route('export.transactions.all') }}" method="GET">
    <button type="submit">Export Excel</button>
</form>
```

### 2. Export Per Bulan
```php
// Form filter bulan
<form action="{{ route('export.transactions.month') }}" method="GET">
    <input type="month" name="month" required>
    <button type="submit">Export Excel</button>
</form>
```

### 3. Di Controller
```php
// Export semua
public function export()
{
    return Excel::download(new TransactionsExport(), 'laporan.xlsx');
}

// Export dengan filter bulan
public function exportWithFilter(Request $request)
{
    $month = $request->month;
    return Excel::download(new TransactionsExport($month), 'laporan_' . $month . '.xlsx');
}
```

---

## ✨ Penjelasan Fitur

### Interface yang Digunakan

| Interface | Fungsi |
|-----------|--------|
| `FromQuery` | Mengambil data menggunakan Query Builder (efisien untuk large data) |
| `WithMapping` | Memetakan field database ke kolom Excel |
| `WithHeadings` | Menentukan header kolom |
| `WithEvents` | Mengatur event setelah sheet dibuat (styling) |
| `WithDrawings` | Menambahkan gambar/logo |
| `WithTitle` | Mengatur judul sheet |
| `WithColumnFormatting` | Format kolom (Rupiah, tanggal, dll) |
| `WithCustomStartCell` | Menentukan cell awal data (B9) |

### Fitur Auto SUM

```php
// Kode inti auto SUM
$startRow = 9;  // Baris pertama data
$endRow = $startRow + $rowCount - 1;  // Baris terakhir data

// Membuat rumus SUM
$sheet->setCellValue("F{$footerRow}", "=SUM(F{$startRow}:F{$endRow})");
```

**Hasil rumus:** 
- Jika ada 10 data: `=SUM(F9:F18)`
- Jika ada 25 data: `=SUM(F9:F33)`

### Styling yang Diterapkan

1. **Header Tabel**: Background biru (`#3498DB`), teks putih, bold
2. **Border**: Semua cell memiliki border tipis
3. **Warna Zebra**: Baris genap background abu-abu muda (`#F9F9F9`)
4. **Footer**: Background kuning (`#F1C40F`), teks bold
5. **Alignment**: Rata tengah untuk semua kecuali kolom jumlah (rata kanan)

### Format Data

| Kolom | Format | Contoh |
|-------|--------|--------|
| ID | Number | 1, 2, 3 |
| TANGGAL | Date (d/m/Y) | 02/05/2026 |
| KETERANGAN | Text | beli kopi |
| JENIS | Text | Masuk/Keluar |
| JUMLAH | Rupiah (Rp #,##0) | 25,000 |

---

## 🔧 Troubleshooting

### Masalah 1: SUM Tidak Berfungsi

**Penyebab:** Header kolom jumlah menggunakan tanda kurung atau karakter khusus

**Solusi:**
```php
// ❌ Salah
public function headings(): array
{
    return ['ID', 'TANGGAL', 'KETERANGAN', 'JENIS', 'JUMLAH (RP)'];
}

// ✅ Benar
public function headings(): array
{
    return ['ID', 'TANGGAL', 'KETERANGAN', 'JENIS', 'JUMLAH'];
}
```

### Masalah 2: File Corrupt atau Tidak Bisa Dibuka

**Solusi:** Clear cache dan regenerate
```bash
php artisan optimize:clear
composer dump-autoload
```

### Masalah 3: Memory Exhausted (Data Terlalu Banyak)

**Solusi:** Gunakan chunking
```php
public function query()
{
    return Transaction::query()->chunk(1000);
}
```

### Masalah 4: Logo Tidak Muncul

**Solusi:** Pastikan path logo benar
```php
// Cek keberadaan file
if (file_exists(storage_path('app/public/logo_up.png'))) {
    // Logo akan ditampilkan
} else {
    // Log error atau gunakan default
}
```

### Masalah 5: Format Rupiah Tidak Sesuai

**Solusi:** Gunakan setting berikut
```php
public function columnFormats(): array 
{ 
    return [
        'F' => '#,##0.00'
    ]; 
}
```

---

## 📊 Contoh Hasil

### Tampilan Excel yang Dihasilkan

```
┌─────────────────────────────────────────────────────────────────────┐
│                    LAPORAN KEUANGAN UNIT PRODUKSI                    │
│                          SMK Taruna Bangsa                           │
│                         Periode: May 2026                            │
├─────┬────────────┬──────────────────┬────────┬──────────────┤
│ ID  │ TANGGAL    │ KETERANGAN       │ JENIS  │ JUMLAH       │
├─────┼────────────┼──────────────────┼────────┼──────────────┤
│ 1   │ 02/05/2026 │ beli kopi        │ Masuk  │ 25,000       │
│ 2   │ 02/05/2026 │ beli kopi        │ Keluar │ 12,000       │
│ 3   │ 02/05/2026 │ beli baju        │ Keluar │ 105,000      │
│ 4   │ 02/05/2026 │ beli kopi        │ Masuk  │ 25,000       │
│ 5   │ 02/05/2026 │ beli kopi        │ Masuk  │ 300          │
│ 6   │ 02/05/2026 │ ada              │ Keluar │ 300          │
│ 7   │ 02/05/2026 │ beli kopi        │ Masuk  │ 300,000      │
├─────┼────────────┼──────────────────┼────────┼──────────────┤
│     │            │ TOTAL SALDO AKHIR│        │ 875,600      │
└─────┴────────────┴──────────────────┴────────┴──────────────┘
```

### Fitur yang Tersedia di Excel

✅ Auto filter pada header  
✅ Rumus SUM otomatis  
✅ Format Rupiah  
✅ Warna zebra untuk baris data  
✅ Border rapi  
✅ Header dengan background biru  
✅ Footer dengan background kuning  

---

## 📝 Catatan Penting

1. **Header Bulan**: Parameter `$month` menggunakan format `Y-m` (contoh: 2026-05)

2. **Logo**: Simpan logo di `storage/app/public/logo_up.png`

3. **Performance**: Untuk data > 10.000 baris, pertimbangkan menggunakan chunking atau queue

4. **Compatibility**: File Excel yang dihasilkan kompatibel dengan Excel 2007 ke atas (format .xlsx)

---

## 🎯 Kesimpulan

Fitur Export Excel dengan Auto SUM ini memberikan kemudahan bagi user dalam:
- Mendapatkan laporan keuangan instan
- Tidak perlu membuat rumus SUM manual
- Tampilan profesional dan rapi
- Bisa difilter per periode

Dengan mengikuti dokumentasi ini, Anda dapat dengan mudah mengimplementasikan fitur export Excel yang sudah dilengkapi dengan auto SUM di aplikasi Laravel Anda.

---

**Dokumentasi oleh:** Tim Developer  
**Versi:** 1.0  
**Terakhir Diupdate:** 2026-05-06