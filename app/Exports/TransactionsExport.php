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
            $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
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
            $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
        }
        return $query->count();
    }

    private function setupHeader(AfterSheet $event, Worksheet $sheet): void
    {
        $sheet->getColumnDimension('B')->setWidth(8); 
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(20);

        $sheet->mergeCells('D2:F2');
        $sheet->setCellValue('D2', 'LAPORAN KEUANGAN UNIT PRODUKSI');
        $sheet->setCellValue('D3', 'SMK Taruna Bangsa');
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D3')->getFont()->setSize(11);

        if ($this->month) {
            $date = Carbon::parse($this->month);
            $sheet->setCellValue('D4', 'Periode: ' . $date->format('F Y'));
            $sheet->getStyle('D4')->getFont()->setItalic(true);
        }

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
        $event->sheet->getStyle("B{$startRow}:F{$endRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        $event->sheet->getStyle("F{$startRow}:F{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
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
        
        $sheet->mergeCells("B{$footerRow}:E{$footerRow}");
        $sheet->setCellValue("B{$footerRow}", 'TOTAL SALDO AKHIR');
        $sheet->setCellValue("F{$footerRow}", "=SUM(F{$startRow}:F{$endRow})");
        
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
        
        $event->sheet->getStyle("F{$footerRow}")->getNumberFormat()
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
        
        $event->sheet->getStyle("F11")->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    }
}