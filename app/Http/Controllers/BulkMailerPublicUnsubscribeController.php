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
            'alreadyUnsubscribed' => ! is_null($contact->unsubscribed_at),
        ]);
    }

    public function store(Request $request, BulkMailerCampaign $campaign, BulkMailerContact $contact): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        if (is_null($contact->unsubscribed_at)) {
            $contact->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
                'suppression_reason' => 'Public unsubscribe link',
            ]);
        }

        return redirect()->signedRoute(
            'bulk-mailer.public.unsubscribe.show',
            [
                'campaign' => $campaign->id,
                'contact' => $contact->id,
            ],
            now()->addMinutes(10)
        )->with('success', 'You have been unsubscribed successfully.');
    }
}