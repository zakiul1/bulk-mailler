<?php

namespace App\Http\Controllers;

use App\Models\BulkMailerContact;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkMailerContactExportController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $fileName = 'bulk-mailer-contacts-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $contacts = BulkMailerContact::query()
            ->with('category')
            ->orderBy('email')
            ->get();

        return response()->streamDownload(function () use ($contacts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'email',
                'category',
                'created_at',
            ]);

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->email,
                    $contact->category?->name,
                    optional($contact->created_at)?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}