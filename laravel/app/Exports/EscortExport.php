<?php

namespace App\Exports;

use App\Models\EscortModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EscortExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $filters;

    public function __construct($startDate = null, $endDate = null, $filters = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filters = $filters;
    }

    /**
     * Get collection of data for Excel export
     * 
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = EscortModel::query();

        // Apply date filter
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        // Apply additional filters
        if (!empty($this->filters['kategori'])) {
            $query->where('kategori_pengantar', $this->filters['kategori']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $this->filters['jenis_kelamin']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_pengantar', 'like', "%{$search}%")
                  ->orWhere('nama_pasien', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhere('plat_nomor', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Define the headings for the Excel file
     * 
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Kategori Pengantar',
            'Nama Pengantar', 
            'Jenis Kelamin',
            'Nomor HP',
            'Plat Nomor',
            'Nama Pasien',
            'Status',
            'Tanggal Masuk',
            'Waktu Masuk',
            'Submission ID',
            'IP Address'
        ];
    }

    /**
     * Map each row of data
     * 
     * @param mixed $escort
     * @return array
     */
    public function map($escort): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $escort->kategori_pengantar,
            $escort->nama_pengantar,
            $escort->jenis_kelamin,
            $escort->nomor_hp,
            $escort->plat_nomor,
            $escort->nama_pasien,
            $escort->getStatusDisplayName(),
            $escort->created_at->format('d/m/Y'),
            $escort->created_at->format('H:i:s'),
            $escort->submission_id ?? '-',
            $escort->submitted_from_ip ?? '-'
        ];
    }

    /**
     * Apply styles to the Excel worksheet
     * 
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Get the last row and column
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $lastColumn . $lastRow;

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            
            // All cells styling
            $range => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ],
            
            // Data rows styling (alternating colors)
            '2:' . $lastRow => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ]
            ]
        ];
    }

    /**
     * Set column widths
     * 
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 18,  // Kategori Pengantar
            'C' => 25,  // Nama Pengantar
            'D' => 15,  // Jenis Kelamin
            'E' => 18,  // Nomor HP
            'F' => 15,  // Plat Nomor
            'G' => 25,  // Nama Pasien
            'H' => 12,  // Status
            'I' => 15,  // Tanggal Masuk
            'J' => 12,  // Waktu Masuk
            'K' => 20,  // Submission ID
            'L' => 18   // IP Address
        ];
    }

    /**
     * Set the title of the worksheet
     * 
     * @return string
     */
    public function title(): string
    {
        $startFormatted = Carbon::parse($this->startDate)->format('d-m-Y');
        $endFormatted = Carbon::parse($this->endDate)->format('d-m-Y');
        return "Data IGD {$startFormatted} - {$endFormatted}";
    }
}