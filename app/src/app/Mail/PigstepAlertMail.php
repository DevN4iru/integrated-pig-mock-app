<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PigstepAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, string>  $lines
     */
    public function __construct(
        public string $subjectLine,
        public string $headline,
        public array $lines = [],
        public ?string $actionText = null,
        public ?string $actionUrl = null,
    ) {
    }

    public function build(): static
    {
        return $this
            ->subject($this->subjectLine)
            ->view('emails.pigstep-alert')
            ->with([
                'subjectLine' => $this->subjectLine,
                'headline' => $this->headline,
                'lines' => $this->lines,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
            ]);
    }
}
