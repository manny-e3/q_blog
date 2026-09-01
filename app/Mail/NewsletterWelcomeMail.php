<?php

namespace App\Mail;

use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public NewsletterSubscription $subscription;
    public string $unsubscribeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(NewsletterSubscription $subscription, string $unsubscribeUrl)
    {
        $this->subscription = $subscription;
        $this->unsubscribeUrl = $unsubscribeUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to the Q-BLOGS Newsletter!')
                    ->view('emails.newsletter_welcome');
    }
}
