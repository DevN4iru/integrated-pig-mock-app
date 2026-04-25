<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FarmSummaryReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public array $summary,
        public string $pdfBytes,
        public string $pdfFilename,
        public string $csvBytes,
        public string $csvFilename,
    ) {
    }

    public function build(): static
    {
        $subjectDate = $this->summary['generated_at']->toDateString();

        return $this
            ->subject('[Pigstep] Farm Summary Report — '.$subjectDate)
            ->view('emails.farm-summary-report')
            ->with([
                'summary' => $this->summary,
                'metrics' => $this->summary['metrics'],
            ])
            ->attachData($this->pdfBytes, $this->pdfFilename, [
                'mime' => 'application/pdf',
            ])
            ->attachData($this->csvBytes, $this->csvFilename, [
                'mime' => 'text/csv',
            ]);
    }
}
