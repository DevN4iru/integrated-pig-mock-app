<?php

namespace App\Http\Controllers;

use App\Services\FarmSummaryReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FarmSummaryReportController extends Controller
{
    public function csv(FarmSummaryReportService $reportService): StreamedResponse
    {
        $summary = $reportService->summary();
        $filename = 'farm-summary-'.$summary['generated_at']->toDateString().'.csv';

        return response()->streamDownload(function () use ($summary): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['metric', 'value']);

            foreach ($summary['rows'] as $row) {
                fputcsv($handle, [
                    $row['metric'],
                    $row['value'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
