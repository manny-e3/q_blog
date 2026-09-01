<?php

namespace App\Mail;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArticleResolutionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public Article $article;
    public string $status;
    public string $statusText;
    public string $authoriserName;
    public ?string $reason;
    public string $actionUrl;
    public string $inputterName;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $title,
        Article $article,
        string $status,
        string $statusText,
        string $authoriserName,
        ?string $reason,
        string $actionUrl,
        string $inputterName
    ) {
        $this->title = $title;
        $this->article = $article;
        $this->status = $status;
        $this->statusText = $statusText;
        $this->authoriserName = $authoriserName;
        $this->reason = $reason;
        $this->actionUrl = $actionUrl;
        $this->inputterName = $inputterName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.article_resolution');
    }
}
