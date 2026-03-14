<?php

namespace App\Livewire\BulkMailer\Templates;

use Livewire\Component;

class Builder extends Component
{
    public string $preset = 'newsletter';
    public string $generated_subject = 'Your Subject Here';
    public string $generated_html = '';

    public function mount(): void
    {
        $this->applyPreset('newsletter');
    }

    public function applyPreset(string $preset): void
    {
        $this->preset = $preset;

        [$subject, $html] = match ($preset) {
            'announcement' => [
                'Important update for {{first_name}}',
                '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
                    <h1>Important Update</h1>
                    <p>Hello {{first_name}},</p>
                    <p>We have an important announcement to share with you.</p>
                    <p>Thank you,<br>Team</p>
                </div>'
            ],
            'promotion' => [
                'Special offer for {{first_name}}',
                '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
                    <h1>Special Offer</h1>
                    <p>Hello {{first_name}},</p>
                    <p>Enjoy this exclusive offer available for a limited time.</p>
                    <p><a href="#">Claim offer</a></p>
                </div>'
            ],
            default => [
                'Newsletter for {{first_name}}',
                '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
                    <h1>Monthly Newsletter</h1>
                    <p>Hello {{first_name}},</p>
                    <p>Here are the latest updates from our team.</p>
                    <ul>
                        <li>Update one</li>
                        <li>Update two</li>
                        <li>Update three</li>
                    </ul>
                    <p>Thanks for reading.</p>
                </div>'
            ],
        };

        $this->generated_subject = $subject;
        $this->generated_html = $html;
    }

    public function render()
    {
        return view('livewire.bulk-mailer.templates.builder')
            ->layout('layouts.app')
            ->title('Template Builder');
    }
}