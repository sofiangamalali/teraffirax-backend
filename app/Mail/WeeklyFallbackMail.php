<?php

namespace App\Mail;

use App\Models\Creator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyFallbackMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Creator $creator
    ) {}

    public function build()
    {
        $creator_first_name = explode(' ', $this->creator->name)[0];

        $subject = "مشتاقين لنشاطك يا {$creator_first_name}";

        return $this->subject($subject)
            ->view('emails.weekly-fallback')
            ->with([
                'creator_first_name' => $creator_first_name,
            ])
            ->withSymfonyMessage(function ($message) {
                $message->getHeaders()->addTextHeader('X-Creator-ID', (string) $this->creator->id);
                $message->getHeaders()->addTextHeader('X-Email-Type', 'weekly_fallback');
            });
    }
}
