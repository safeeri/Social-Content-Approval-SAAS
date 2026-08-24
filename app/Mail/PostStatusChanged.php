<?php

namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Post $post,
        public string $headline,
        public string $message,
        public string $ctaLabel,
        public string $ctaUrl,
    ) {
        $this->post->loadMissing(['client.company', 'platform']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->headline.' — '.$this->post->client->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.post-status',
        );
    }
}
