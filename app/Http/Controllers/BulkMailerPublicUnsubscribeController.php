<?php

namespace App\Http\Controllers;

use App\Models\BulkMailerCampaign;
use App\Models\BulkMailerContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BulkMailerPublicUnsubscribeController extends Controller
{
    public function show(Request $request, BulkMailerCampaign $campaign, BulkMailerContact $contact): View
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('bulk-mailer.public.unsubscribe', [
            'campaign' => $campaign,
            'contact' => $contact,
        ]);
    }

    public function store(Request $request, BulkMailerCampaign $campaign, BulkMailerContact $contact): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $contact->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
            'suppression_reason' => 'Public unsubscribe link',
        ]);

        return redirect()
            ->route('bulk-mailer.public.unsubscribe.show', [
                'campaign' => $campaign,
                'contact' => $contact,
                'signature' => $request->query('signature'),
                'expires' => $request->query('expires'),
            ])
            ->with('success', 'You have been unsubscribed successfully.');
    }
}