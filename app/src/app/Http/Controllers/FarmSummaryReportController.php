<?php

namespace App\Http\Controllers;

use App\Mail\FarmSummaryReportMail;
use App\Models\FarmSetting;
use App\Services\FarmSummaryReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class FarmSummaryReportController extends Controller
{
    public function csv(FarmSummaryReportService $reportService): StreamedResponse
    {
        $summary = $reportService->summary();
        $filename = 'farm-summary-'.$summary['generated_at']->toDateString().'.csv';

        return response()->streamDownload(function () use ($summary): void {
            echo $this->csvContent($summary);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    public function pdf(FarmSummaryReportService $reportService): Response
    {
        $summary = $reportService->summary();
        $filename = 'farm-summary-'.$summary['generated_at']->toDateString().'.pdf';

        return Pdf::loadView('reports.farm-summary-pdf', [
            'summary' => $summary,
        ])
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function email(FarmSummaryReportService $reportService): RedirectResponse
    {
        $recipient = trim((string) FarmSetting::current()->alert_recipient_email);

        if ($recipient === '') {
            return back()->with('error', 'Set an alert recipient email before sending reports.');
        }

        $summary = $reportService->summary();
        $date = $summary['generated_at']->toDateString();

        $pdfFilename = 'farm-summary-'.$date.'.pdf';
        $csvFilename = 'farm-summary-'.$date.'.csv';

        try {
            Mail::to($recipient)->send(new FarmSummaryReportMail(
                summary: $summary,
                pdfBytes: $this->pdfContent($summary),
                pdfFilename: $pdfFilename,
                csvBytes: $this->csvContent($summary),
                csvFilename: $csvFilename,
            ));
        } catch (Throwable $exception) {
            Log::error('Manual farm summary report email failed.', [
                'recipient' => $recipient,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Farm summary report could not be sent. Check mail settings and try again.');
        }

        return back()->with('success', 'Farm summary report emailed to '.$recipient.'.');
    }

    protected function pdfContent(array $summary): string
    {
        return Pdf::loadView('reports.farm-summary-pdf', [
            'summary' => $summary,
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    protected function csvContent(array $summary): string
    {
        $handle = fopen('php://temp', 'r+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ['metric', 'value']);

        foreach ($summary['rows'] as $row) {
            fputcsv($handle, [
                $row['metric'],
                $row['value'],
            ]);
        }

        rewind($handle);

        $content = stream_get_contents($handle);

        fclose($handle);

        return (string) $content;
    }
}
