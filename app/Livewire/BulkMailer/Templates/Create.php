<?php

namespace App\Livewire\BulkMailer\Templates;

use App\Models\BulkMailerTemplate;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $subject = '';
    public string $html_content = '';
    public string $text_content = '';
    public bool $is_active = true;

    public string $editorTab = 'details';
    public string $device = 'desktop';
    public string $previewTab = 'preview';

    protected array $sampleData = [
        '{{name}}' => 'Siatex BD LTD',
        '{{email}}' => 'info@siatex.com',
        '{{first_name}}' => 'Siatex',
        '{{last_name}}' => 'BD LTD',
        '{{subject}}' => 'Reliable OEM Clothing Manufacturing from Bangladesh Since 1987',
        '{{unsubscribe_url}}' => '#',
    ];

    public function setEditorTab(string $tab): void
    {
        if (! in_array($tab, ['details', 'html', 'text', 'preview'], true)) {
            return;
        }

        $this->editorTab = $tab;
    }

    public function setDevice(string $device): void
    {
        if (! in_array($device, ['desktop', 'tablet', 'mobile'], true)) {
            return;
        }

        $this->device = $device;
    }

    public function setPreviewTab(string $tab): void
    {
        if (! in_array($tab, ['preview', 'html', 'text'], true)) {
            return;
        }

        $this->previewTab = $tab;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'html_content' => ['nullable', 'string'],
            'text_content' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Template name is required.',
            'subject.required' => 'Email subject is required.',
        ]);

        $template = BulkMailerTemplate::create([
            'name' => trim($validated['name']),
            'subject' => trim($validated['subject']),
            'html_content' => filled($validated['html_content']) ? $validated['html_content'] : null,
            'text_content' => filled($validated['text_content']) ? $validated['text_content'] : null,
            'is_active' => (bool) $validated['is_active'],
        ]);

        session()->flash('success', 'Template created successfully.');

        return redirect()->route('bulk-mailer.templates.edit', $template);
    }

    public function getRenderedSubjectProperty(): string
    {
        return $this->replaceVariables($this->subject ?: '{{subject}}');
    }

    public function getRenderedHtmlProperty(): string
    {
        if (filled($this->html_content)) {
            return $this->replaceVariables($this->html_content);
        }

        if (filled($this->text_content)) {
            return nl2br(e($this->replaceVariables($this->text_content)));
        }

        return '<div style="font-size:14px; color:#71717a;">No preview content yet.</div>';
    }

    public function getRenderedTextProperty(): string
    {
        return $this->replaceVariables($this->text_content);
    }

    public function getPreviewWidthClassProperty(): string
    {
        return match ($this->device) {
            'mobile' => 'max-w-sm',
            'tablet' => 'max-w-3xl',
            default => 'max-w-full',
        };
    }

    protected function replaceVariables(?string $content): string
    {
        return strtr($content ?? '', $this->sampleData);
    }

    public function render()
    {
        return view('livewire.bulk-mailer.templates.create')
            ->layout('layouts.app')
            ->title('Create Template');
    }
}