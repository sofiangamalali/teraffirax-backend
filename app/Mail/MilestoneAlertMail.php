<?php

namespace App\Mail;

use App\Models\Creator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MilestoneAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Creator $creator,
        public float $minimum_withdraw_amount,
        public float $current_available
    ) {}

    public function build()
    {
        $creator_first_name = explode(' ', $this->creator->name)[0];

        $subject = "🎉 مبروك يا {$creator_first_name} - وصلت للحد الأدنى للسحب (" . number_format($this->minimum_withdraw_amount, 2) . " ج.م)";

        return $this->subject($subject)
            ->view('emails.milestone-alert')
            ->with([
                'creator_first_name' => $creator_first_name,
                'minimum_withdraw_amount' => $this->minimum_withdraw_amount,
                'current_available' => $this->current_available,
            ])
            ->withSymfonyMessage(function ($message) {
                $message->getHeaders()->addTextHeader('X-Creator-ID', (string) $this->creator->id);
                $message->getHeaders()->addTextHeader('X-Email-Type', 'milestone_alert');
            });
    }
}
