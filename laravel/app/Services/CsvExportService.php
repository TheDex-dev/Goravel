<?php

namespace App\Services;

use App\Models\EscortModel;
use Carbon\Carbon;

class CsvExportService
{
    /**
     * Generate CSV content for escort data
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param array $filters
     * @return string
     */
    public static function generateCsv($startDate = null, $endDate = null, $filters = [])
    {
        $query = EscortModel::query();

        // Apply date filter
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        // Apply additional filters
        if (!empty($filters['kategori'])) {
            $query->where('kategori_pengantar', $filters['kategori']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $filters['jenis_kelamin']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_pengantar', 'like', "%{$search}%")
                  ->orWhere('nama_pasien', 'like', "%{$search}%")
                  ->orWhere('nomor_hp', 'like', "%{$search}%")
                  ->orWhere('plat_nomor', 'like', "%{$search}%");
            });
        }

        $escorts = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV content
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8 Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");
        
        // Add metadata header comments
        fputcsv($output, ["# Data Escort IGD - Periode: " . Carbon::parse($startDate)->format('d/m/Y') . " - " . Carbon::parse($endDate)->format('d/m/Y')], ',');
        fputcsv($output, ["# Diekspor pada: " . now()->format('d/m/Y H:i:s')], ',');
        fputcsv($output, ["# Total data: " . $escorts->count() . " record"], ',');
        fputcsv($output, [""], ','); // Empty line separator
        
        // Write header
        fputcsv($output, [
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
        ], ','); // Use comma for better CSV compatibility

        // Write data rows
        $rowNumber = 1;
        foreach ($escorts as $escort) {
            fputcsv($output, [
                $rowNumber++,
                $escort->kategori_pengantar,
                $escort->nama_pengantar,
                $escort->jenis_kelamin,
                "'" . $escort->nomor_hp, // Add quote to preserve leading zeros
                $escort->plat_nomor,
                $escort->nama_pasien,
                $escort->getStatusDisplayName(),
                $escort->created_at->format('d/m/Y'),
                $escort->created_at->format('H:i:s'),
                $escort->submission_id ?: '-',
                $escort->submitted_from_ip ?: '-'
            ], ',');
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}