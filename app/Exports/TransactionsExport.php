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
            'F' => '"Rp "#,##0',
        ];
    }

    public function headings(): array
    {
        return [
            ['ID', 'TANGGAL', 'KETERANGAN', 'JENIS', 'JUMLAH (RP)'],
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $logoPath = storage_path('app/public/logo_up.png');

        if (!file_exists($logoPath)) {
            $logoUrl = 'https://thi-web6.github.io/resume/images/tarunabangsaicon.png';
            $content = @file_get_contents($logoUrl);
            if ($content) file_put_contents($logoPath, $content);
        }

        if (file_exists($logoPath)) {
            $drawing->setName('Logo');
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
                $highestRow = $sheet->getHighestRow();

                $this->setupHeader($sheet);
                $this->setupTableStyle($sheet, $highestRow);
                $this->setupFooter($sheet, $highestRow);
            },
        ];
    }

    private function setupHeader(Worksheet $sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(2);
        $sheet->getColumnDimension('B')->setWidth(8); 
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(20);

        $sheet->mergeCells('D2:F2');
        $sheet->mergeCells('D3:F3');
        $sheet->setCellValue('D2', 'LAPORAN KEUANGAN UNIT PRODUKSI');
        $sheet->setCellValue('D3', 'SMK TARUNA BANGSA');

        $sheet->getStyle('D2:D3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
        ]);

        $sheet->getStyle('B8:F8')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);
    }

    private function setupTableStyle(Worksheet $sheet, int $highestRow): void
    {
        if ($highestRow < 9) return;

        $sheet->getStyle("B9:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E9:E{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D9:D{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle("B9:F{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);
    }

    private function setupFooter(Worksheet $sheet, int $highestRow): void
    {
        $footerRow = $highestRow > 8 ? $highestRow + 1 : 10;
        $lastDataRow = $highestRow > 8 ? $highestRow : 9;
        
        $sheet->mergeCells("B{$footerRow}:E{$footerRow}");
        $sheet->setCellValue("B{$footerRow}", 'TOTAL SALDO AKHIR');
        
        // Rumus SUM langsung menjumlahkan kolom F
        $formula = sprintf('=SUM(F9:F%d)', $lastDataRow);
        $sheet->setCellValue("F{$footerRow}", $formula);

        $sheet->getStyle("B{$footerRow}:F{$footerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'F1C40F']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        $sheet->getStyle("F{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}