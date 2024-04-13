<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminApproveMail extends Mailable
{
    use Queueable, SerializesModels;

    // Add properties to store username and password
    public $username;
    public $password;


    /**
     * Create a new message instance.
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account has been Approved',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
{
    // Pass username and password to the email view
    $content = new Content('emails.adminapprovemail');
    $content->with([
        'username' => $this->username,
        'password' => $this->password,
    ]);
    return $content;
}
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
