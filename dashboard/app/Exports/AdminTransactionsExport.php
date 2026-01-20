<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AdminTransactionsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    use RegistersEventListeners;

    protected $transactions;
    protected $startDate;
    protected $endDate;
    protected $totalAmount;
    protected $totalTransactionsCount;

    public function __construct($transactions, $startDate = null, $endDate = null, $totalAmount = 0)
    {
        $this->transactions = collect($transactions)->values()->map(function ($item, $key) {
            $item->no = $key + 1;
            return $item;
        });

        $this->startDate = $startDate ? Carbon::parse($startDate) : null;
        $this->endDate = $endDate ? Carbon::parse($endDate) : null;
        $this->totalAmount = $totalAmount;
        $this->totalTransactionsCount = $transactions->count();
    }

    public function collection(): Collection
    {
        return $this->transactions;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Order ID',
            'Brand',
            'Outlet',
            'Kode Device',
            'Jumlah (Rp)',
            'Tipe Pembayaran',
            'Tipe Layanan',
            'Status',
            'Tanggal Transaksi',
            'Waktu Transaksi',
            'Zona Waktu',
        ];
    }

    public function map($transaction): array
    {

        $transactionDateTime = optional($transaction->created_at)
            ? Carbon::parse($transaction->created_at)->setTimezone($transaction->timezone)
            : null;

        $amount = (float)optional($transaction)->amount ?? 0;
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');

        return [
            (string)optional($transaction)->no ?? 'N/A',
            (string)optional($transaction)->order_id ?? 'N/A',
            (string)optional(optional($transaction)->owner)->brand_name ?? '-',
            (string)optional(optional($transaction)->outlet)->outlet_name ?? 'N/A',
            (string)optional($transaction)->device_code ?? 'N/A',
            $formattedAmount,
            (string)(optional($transaction)->type === 'manual' ? 'Kasir' : ucfirst(optional($transaction)->type ?? '')) ?? 'N/A',
            (string)ucfirst(optional($transaction)->service_type ?? '') ?? 'N/A',
            (string)ucfirst(optional($transaction)->status ?? '') ?? 'N/A',
            (string)optional($transactionDateTime)->format('Y-m-d') ?? 'N/A',
            (string)optional($transactionDateTime)->format('H:i:s') ?? 'N/A',
            (string)strtoupper(optional($transaction)->timezone ?? '') ?? 'N/A',
        ];
    }

    public function title(): string
    {
        return 'Data Transaksi';
    }

    public static function afterSheet(AfterSheet $event)
    {
        $sheet = $event->sheet->getDelegate();
        $export = $event->getConcernable();

        // --- Bagian untuk Tanggal di Baris 1 ---
        $startDateFormatted = $export->startDate ? $export->startDate->isoFormat('D MMMM YYYY') : 'N/A';
        $endDateFormatted = $export->endDate ? $export->endDate->isoFormat('D MMMM YYYY') : 'N/A';
        $dateRangeText = "Tanggal : {$startDateFormatted} - {$endDateFormatted}";

        // Insert 2 new rows before the original row 1 (for date and empty row)
        $sheet->insertNewRowBefore(1, 2);

        // Write date text in J1 and merge
        $sheet->setCellValue('J1', $dateRangeText);
        $sheet->mergeCells('J1:L1');
        $sheet->getStyle('J1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['argb' => 'FF333333'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        // --- End of Date Section ---

        // --- Bagian untuk Total Amount dan Total Transaksi di Bawah Data ---
        // highestDataRow will give the last row containing transaction data.
        // The summary row will be one row after the last data row.
        $summaryRow = $sheet->getHighestDataRow() + 1;

        // Format total amount as string, consistent with individual amounts
        $totalAmountFormatted = 'Rp ' . number_format((float)$export->totalAmount, 0, ',', '.');

        // Write 'Total Transaksi' label and its count in column A and B
        $sheet->setCellValue('A' . $summaryRow, 'Total Transaksi');
        $sheet->setCellValue('B' . $summaryRow, $export->totalTransactionsCount);

        // Write 'Total Jumlah (Rp)' label and the formatted total amount in column E and F
        $sheet->setCellValue('E' . $summaryRow, 'Total Jumlah (Rp)');
        $sheet->setCellValue('F' . $summaryRow, $totalAmountFormatted);

        // Merge cells for the 'Total Transaksi' label if desired (A to A is not a merge)
        // No merging needed for Total Transaksi as it is in A and value in B

        // Apply styling to the summary row
        $sheet->getStyle('A' . $summaryRow . ':L' . $summaryRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM, // Medium border on top
                    'color' => ['argb' => 'FF000000'],
                ],
                'bottom' => [ // Optional: Add a bottom border to close the footer section
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FFEEEEEE'], // Light gray background
            ],
        ]);

        // Align the labels and values in the summary row
        $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Transaksi' label right
        $sheet->getStyle('B' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Center 'Total Transaksi' count
        $sheet->getStyle('E' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Jumlah (Rp)' label right
        $sheet->getStyle('F' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Jumlah (Rp)' value right

        // --- End of Total Amount and Total Transaksi Section ---
    }

    public function styles(Worksheet $sheet)
    {
        // Because 2 rows are inserted at the top:
        // - Header is now on ROW 3
        // - Data starts from ROW 4
        // $highestDataRow will correctly give the last row of actual data,
        // before the total rows are added by afterSheet event.
        $highestDataRow = $sheet->getHighestDataRow();

        // Style header row (now on row 3)
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([ // Corrected to A3
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'FF4CAF50'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Style data rows (now from row 4 onwards, up to the last data row)
        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $highestDataRow)->applyFromArray([ // Corrected to A2
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFDDDDDD'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        // Center align specific columns (now from row 4 onwards)
        $sheet->getStyle('A2:A' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Corrected to A2
        $sheet->getStyle('J2:L' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Corrected to J2

        // Right align 'Jumlah (Rp)' column (now from row 4 onwards)
        $sheet->getStyle('F2:F' . $highestDataRow)->applyFromArray([ // Corrected to F2
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Alternating row colors (now from row 4 onwards)
        for ($row = 4; $row <= $highestDataRow; $row++) {
            if ($row % 2 === 0) { // Even rows (since 4 is the first)
                $sheet->getStyle('A' . $row . ':' . $sheet->getHighestColumn() . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFE8F5E9'],
                    ],
                ]);
            }
        }

        return [];
    }
}
