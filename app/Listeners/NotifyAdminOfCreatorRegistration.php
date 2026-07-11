<?php

namespace App\Listeners;

use App\Events\CreatorRegistered;
use App\Models\Admin;
use App\Mail\AdminCreatorRegisteredNotificationMail;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOfCreatorRegistration
{
    /**
     * Handle the event.
     */
    public function handle(CreatorRegistered $event): void
    {
        $creator = $event->creator;

        $adminEmails = Admin::pluck('email')->toArray();
        if (empty($adminEmails)) {
            $adminEmails = ['admin@teraffirax.com'];
        }

        Mail::to($adminEmails)->send(new AdminCreatorRegisteredNotificationMail($creator));
    }
}
