<?php

namespace App\Mail;

use App\Models\Creator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DailyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Creator $creator,
        public string $variant,
        public float $matured_sum,
        public float $new_commissions_sum,
        public float $current_available,
        public float $current_pending
    ) {}

    public function build()
    {
        $creator_first_name = explode(' ', $this->creator->name)[0];

        $subject = match ($this->variant) {
            'A' => "فلوسك جاهزة يا {$creator_first_name} 💰 " . number_format($this->matured_sum, 2) . " ج.م بقت متاحة للسحب",
            'B' => "في عمولة جديدة اتسجلتلك يا {$creator_first_name}",
            default => "يوم كويس يا {$creator_first_name} - عمولة جديدة وفلوس بقت جاهزة",
        };

        return $this->subject($subject)
            ->view('emails.daily-digest')
            ->with([
                'creator_first_name' => $creator_first_name,
                'variant' => $this->variant,
                'matured_sum' => $this->matured_sum,
                'new_commissions_sum' => $this->new_commissions_sum,
                'current_available' => $this->current_available,
                'current_pending' => $this->current_pending,
            ])
            ->withSymfonyMessage(function ($message) {
                $message->getHeaders()->addTextHeader('X-Creator-ID', (string) $this->creator->id);
                $message->getHeaders()->addTextHeader('X-Email-Type', 'daily_digest');
            });
    }
}
