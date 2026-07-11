<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use App\Repositories\Interfaces\NotificationLogRepositoryInterface;

class LogSentMailNotification
{
    public function __construct(
        protected NotificationLogRepositoryInterface $notificationLogRepository
    ) {}

    public function handle(MessageSending $event): void
    {
        $headers = $event->message->getHeaders();

        if ($headers->has('X-Creator-ID') && $headers->has('X-Email-Type')) {
            $creatorIdHeader = $headers->get('X-Creator-ID');
            $emailTypeHeader = $headers->get('X-Email-Type');

            if ($creatorIdHeader && $emailTypeHeader) {
                $creatorId = (int) $creatorIdHeader->getBody();
                $emailType = (string) $emailTypeHeader->getBody();

                // Log the send to notification_logs table
                $this->notificationLogRepository->create([
                    'creator_id' => $creatorId,
                    'email_type' => $emailType,
                    'sent_at' => now(),
                ]);

                // Strip the headers before Symfony Mailer sends it out to the recipient
                $headers->remove('X-Creator-ID');
                $headers->remove('X-Email-Type');
            }
        }
    }
}
