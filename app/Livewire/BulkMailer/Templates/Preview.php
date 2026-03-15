<?php

namespace App\Livewire\BulkMailer\Templates;

use App\Models\BulkMailerTemplate;
use Livewire\Component;

class Preview extends Component
{
    public BulkMailerTemplate $template;

    public string $device = 'desktop';
    public string $tab = 'rendered';

    public array $sampleData = [
        '{{name}}' => 'Md Jakiul Islam',
        '{{email}}' => 'islamzakiul1@gmail.com',
        '{{first_name}}' => 'Jakiul',
        '{{last_name}}' => 'Islam',
    ];

    public function mount(BulkMailerTemplate $template): void
    {
        $this->template = $template;
    }

    public function setDevice(string $device): void
    {
        if (! in_array($device, ['desktop', 'tablet', 'mobile'], true)) {
            return;
        }

        $this->device = $device;
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['rendered', 'html', 'text'], true)) {
            return;
        }

        $this->tab = $tab;
    }

    public function getRenderedSubjectProperty(): string
    {
        return $this->replaceVariables($this->template->subject ?? '');
    }

    public function getRenderedHtmlProperty(): string
    {
        $html = $this->template->html_content;

        if (blank($html) && filled($this->template->text_content)) {
            return nl2br(e($this->replaceVariables($this->template->text_content)));
        }

        return $this->replaceVariables($html ?? '');
    }

    public function getRenderedTextProperty(): string
    {
        return $this->replaceVariables($this->template->text_content ?? '');
    }

    public function getPreviewWidthClassProperty(): string
    {
        return match ($this->device) {
            'mobile' => 'max-w-sm',
            'tablet' => 'max-w-3xl',
            default => 'max-w-full',
        };
    }

    protected function replaceVariables(string $content): string
    {
        return strtr($content, $this->sampleData);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.templates.preview')
            ->layout('layouts.app')
            ->title('Template Preview');
    }
}