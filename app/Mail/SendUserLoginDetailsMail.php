<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendUserLoginDetailsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $randomPassword;
    /**
     * Create a new message instance.
     */

    public function __construct($user, $randomPassword)
    {
        $this->user = $user;
        $this->randomPassword = $randomPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'User Login Details',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content //Content
    {
        $data = $this->buildData();
        return new Content(
            view: 'emails.account-created',
            with: [
                $data,
            ],
        );
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

    public function buildData()
    {
        return [
           'email' =>  $this->user->email,
           'password' => $this->randomPassword,
        ];
    }
}
