<?php

namespace App\Http\Controllers;

use App\Models\BulkMailerContact;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkMailerContactExportController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $fileName = 'bulk-mailer-contacts-'.now()->format('Y-m-d-H-i-s').'.csv';

        $contacts = BulkMailerContact::query()
            ->with(['lists', 'verification'])
            ->orderBy('email')
            ->get();

        return response()->streamDownload(function () use ($contacts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'email',
                'first_name',
                'last_name',
                'status',
                'verification_status',
                'unsubscribed_at',
                'bounced_at',
                'notes',
                'lists',
            ]);

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->email,
                    $contact->first_name,
                    $contact->last_name,
                    $contact->status?->value ?? $contact->status,
                    $contact->verification_status,
                    optional($contact->unsubscribed_at)?->format('Y-m-d H:i:s'),
                    optional($contact->bounced_at)?->format('Y-m-d H:i:s'),
                    $contact->notes,
                    $contact->lists->pluck('name')->implode('|'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}