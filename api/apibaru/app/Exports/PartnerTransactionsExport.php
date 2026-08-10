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

class PartnerTransactionsExport implements
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
    protected $totalTransactions;
    protected $brandName; // Added brandName property

    public function __construct($transactions, $startDate = null, $endDate = null, $totalAmount = 0, $totalTransactions = 0, $brandName = null) // Added $brandName parameter
    {
        $this->transactions = collect($transactions)->values()->map(function ($item, $key) {
            $item->no = $key + 1;
            return $item;
        });

        $this->startDate = $startDate ? Carbon::parse($startDate) : null;
        $this->endDate = $endDate ? Carbon::parse($endDate) : null;
        $this->totalAmount = $totalAmount;
        $this->totalTransactions = $totalTransactions;
        $this->brandName = $brandName; // Set brandName
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
            'Outlet', // "Brand" column removed
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
            (string)optional(optional($transaction)->outlet)->outlet_name ?? 'N/A', // Shifted left
            (string)optional($transaction)->device_code ?? 'N/A', // Shifted left
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

        // Insert 2 new rows before the original row 1 (for header info and empty row)
        $sheet->insertNewRowBefore(1, 2);

        // Write Brand Name in A1 and merge
        $sheet->setCellValue('A1', 'Brand: ' . ($export->brandName ?? 'N/A'));
        $sheet->mergeCells('A1:C1'); // Merge A1, B1, C1 for brand name
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['argb' => 'FF333333'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT, // Align brand name to the left
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Write date text in I1 and merge (adjusted column for date)
        $sheet->setCellValue('I1', $dateRangeText); // Changed from J1 to I1
        $sheet->mergeCells('I1:K1'); // Changed from J1:L1 to I1:K1
        $sheet->getStyle('I1:K1')->applyFromArray([
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
        // --- End of Header Info Section ---

        // --- Bagian untuk Total Amount dan Total Transaksi di Bawah Data ---
        // highestDataRow will give the last row containing transaction data.
        // The summary row will be one row after the last data row.
        $summaryRow = $sheet->getHighestDataRow() + 1;

        // Format total amount as string, consistent with individual amounts
        $totalAmountFormatted = 'Rp ' . number_format((float)$export->totalAmount, 0, ',', '.');

        // Write 'Total Transaksi' label and its count in column A and B
        $sheet->setCellValue('A' . $summaryRow, 'Total Transaksi');
        $sheet->setCellValue('B' . $summaryRow, $export->totalTransactions);

        // Write 'Total Jumlah (Rp)' label and the formatted total amount in column D and E (adjusted columns)
        $sheet->setCellValue('D' . $summaryRow, 'Total Jumlah (Rp)'); // Changed from E to D
        $sheet->setCellValue('E' . $summaryRow, $totalAmountFormatted); // Changed from F to E

        // Apply styling to the summary row
        $sheet->getStyle('A' . $summaryRow . ':' . $sheet->getHighestColumn() . $summaryRow)->applyFromArray([ // Adjusted highest column
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
        $sheet->getStyle('D' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Jumlah (Rp)' label right (changed from E)
        $sheet->getStyle('E' . $summaryRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Align 'Total Jumlah (Rp)' value right (changed from F)

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
        // Note: The data now starts from column A, and the "Brand" column is removed, so 'L' becomes 'K'
        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . $highestDataRow)->applyFromArray([ // Corrected to A2 and K
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
        // 'No.' (A) and Date/Time/Timezone (I, J, K)
        $sheet->getStyle('A2:A' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Corrected to A2
        $sheet->getStyle('I2:K' . $highestDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Changed from J2:L to I4:K

        // Right align 'Jumlah (Rp)' column (now column E, from row 4 onwards)
        $sheet->getStyle('E2:E' . $highestDataRow)->applyFromArray([ // Changed from F2:F to E4:E
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Alternating row colors (now from row 4 onwards)
        for ($row = 2; $row <= $highestDataRow; $row++) {
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
