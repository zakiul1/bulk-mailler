<?php

namespace App\Mail;

use App\Models\BulkMailerSmtpAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkMailerSmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BulkMailerSmtpAccount $smtp)
    {
    }

    public function build(): self
    {
        $mail = $this->subject('Bulk Mailer SMTP Test: '.$this->smtp->name)
            ->view('emails.bulk-mailer.smtp-test', [
                'smtp' => $this->smtp,
            ])
            ->from($this->smtp->from_email, $this->smtp->from_name);

        if (filled($this->smtp->reply_to_email)) {
            $mail->replyTo($this->smtp->reply_to_email);
        }

        return $mail;
    }
}