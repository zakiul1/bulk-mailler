<?php

namespace App\Mail;

use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerContact;
use App\Models\BulkMailerSmtpAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mime\Email;

class BulkMailerCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BulkMailerSmtpAccount $smtp,
        public BulkMailerCampaign $campaign,
        public BulkMailerContact $contact,
        public string $subjectLine,
        public string $htmlBody,
        public ?string $textBody = null,
    ) {
    }

    public function build(): self
    {
        $htmlBody = $this->normalizeHtmlBody($this->htmlBody);

        $textBody = filled($this->textBody)
            ? $this->normalizeTextBody($this->textBody)
            : $this->generateTextFromHtml($htmlBody);

        $unsubscribeUrl = $this->resolveUnsubscribeUrl();

        if ($unsubscribeUrl) {
            $htmlBody = $this->appendUnsubscribeFooter($htmlBody, $unsubscribeUrl);
            $textBody = $this->appendTextUnsubscribeFooter($textBody, $unsubscribeUrl);
        }

        $mail = $this->subject($this->subjectLine)
            ->from($this->smtp->from_email, $this->smtp->from_name)
            ->html($htmlBody)
            ->text('emails.bulk-mailer-plain', [
                'slot' => $textBody,
            ])
            ->withSymfonyMessage(function (Email $message) use ($htmlBody, $textBody, $unsubscribeUrl) {
                $message->html($htmlBody);
                $message->text($textBody);

                if ($unsubscribeUrl) {
                    $headers = $message->getHeaders();

                    if (! $headers->has('List-Unsubscribe')) {
                        $headers->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');
                    }

                    if (! $headers->has('List-Unsubscribe-Post')) {
                        $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                    }
                }
            });

        if (filled($this->smtp->reply_to_email)) {
            $mail->replyTo($this->smtp->reply_to_email);
        }

        return $mail;
    }

    protected function resolveUnsubscribeUrl(): ?string
    {
        if (! $this->campaign->exists || ! $this->contact->exists || ! $this->contact->id) {
            return null;
        }

        return URL::temporarySignedRoute(
            'bulk-mailer.public.unsubscribe.show',
            now()->addDays(30),
            [
                'campaign' => $this->campaign->id,
                'contact' => $this->contact->id,
            ]
        );
    }

    protected function appendUnsubscribeFooter(string $html, string $unsubscribeUrl): string
    {
        $footer = '
            <hr style="margin-top:32px;margin-bottom:16px;border:none;border-top:1px solid #d4d4d8;">
            <div style="font-size:12px;line-height:1.6;color:#71717a;">
                If you do not want to receive these emails, you can
                <a href="' . e($unsubscribeUrl) . '" style="color:#18181b;text-decoration:underline;">unsubscribe here</a>.
            </div>
        ';

        if (preg_match('/<\/body>/i', $html)) {
            return preg_replace('/<\/body>/i', $footer . '</body>', $html, 1) ?? ($html . $footer);
        }

        if (preg_match('/<\/html>/i', $html)) {
            return preg_replace('/<\/html>/i', $footer . '</html>', $html, 1) ?? ($html . $footer);
        }

        return rtrim($html) . $footer;
    }

    protected function appendTextUnsubscribeFooter(string $text, string $unsubscribeUrl): string
    {
        $footer = "\n\n----------------------------------------\n";
        $footer .= "If you do not want to receive these emails, unsubscribe here:\n";
        $footer .= $unsubscribeUrl . "\n";

        return rtrim($text) . $footer;
    }

    protected function generateTextFromHtml(string $html): string
    {
        $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\s*\/p\s*>/i', "\n\n", $text);
        $text = preg_replace('/<\s*\/div\s*>/i', "\n", $text);
        $text = preg_replace('/<\s*\/tr\s*>/i', "\n", $text);
        $text = preg_replace('/<\s*\/td\s*>/i', "\t", $text);
        $text = strip_tags($text ?? '');
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = preg_replace("/[ \t]+/", ' ', $text);

        return trim((string) $text);
    }

    protected function normalizeHtmlBody(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        if (stripos($html, '<html') !== false || stripos($html, '<body') !== false) {
            return $html;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;color:#222;">
    <div style="max-width:700px;margin:0 auto;padding:16px;line-height:1.6;">
        {$html}
    </div>
</body>
</html>
HTML;
    }

    protected function normalizeTextBody(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim((string) $text);
    }
}