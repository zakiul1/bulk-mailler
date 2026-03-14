<?php

namespace App\Mail;

use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerSmtpAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class BulkMailerCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BulkMailerSmtpAccount $smtp,
        public BulkMailerCampaign $campaign,
        public BulkMailerContact $contact,
        public string $subjectLine,
        public string $htmlBody,
    ) {
    }

    public function build(): self
    {
        $body = $this->htmlBody;

        if ($this->campaign->exists && $this->contact->exists && $this->contact->id) {
            $unsubscribeUrl = URL::temporarySignedRoute(
                'bulk-mailer.public.unsubscribe.show',
                now()->addDays(30),
                [
                    'campaign' => $this->campaign->id,
                    'contact' => $this->contact->id,
                ]
            );

            $body = $this->appendUnsubscribeFooter($body, $unsubscribeUrl);
        }

        $mail = $this->subject($this->subjectLine)
            ->from($this->smtp->from_email, $this->smtp->from_name)
            ->html($body);

        if (filled($this->smtp->reply_to_email)) {
            $mail->replyTo($this->smtp->reply_to_email);
        }

        return $mail;
    }

    protected function appendUnsubscribeFooter(string $html, string $unsubscribeUrl): string
    {
        $footer = '
            <hr style="margin-top:32px;margin-bottom:16px;border:none;border-top:1px solid #d4d4d8;">
            <div style="font-size:12px;color:#71717a;">
                If you do not want to receive these emails, you can
                <a href="'.$unsubscribeUrl.'" style="color:#18181b;text-decoration:underline;">unsubscribe here</a>.
            </div>
        ';

        return $html.$footer;
    }
}