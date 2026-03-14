<?php

use App\Http\Controllers\BulkMailerContactExportController;
use App\Http\Controllers\BulkMailerPublicUnsubscribeController;
use App\Http\Controllers\BulkMailerWebhookController;
use App\Livewire\BulkMailer\CampaignCalendar\Index as BulkMailerCampaignCalendarIndex;
use App\Livewire\BulkMailer\Campaigns\Index as BulkMailerCampaignsIndex;
use App\Livewire\BulkMailer\Campaigns\Show as BulkMailerCampaignsShow;
use App\Livewire\BulkMailer\Contacts\Index as BulkMailerContactsIndex;
use App\Livewire\BulkMailer\Dashboard as BulkMailerDashboard;
use App\Livewire\BulkMailer\Lists\Index as BulkMailerListsIndex;
use App\Livewire\BulkMailer\Operations\QueueMonitor as BulkMailerQueueMonitor;
use App\Livewire\BulkMailer\Reports\Index as BulkMailerReportsIndex;
use App\Livewire\BulkMailer\Segments\Index as BulkMailerSegmentsIndex;
use App\Livewire\BulkMailer\SmtpAccounts\Index as BulkMailerSmtpAccountsIndex;
use App\Livewire\BulkMailer\SmtpAnalytics\Index as BulkMailerSmtpAnalyticsIndex;
use App\Livewire\BulkMailer\Templates\Builder as BulkMailerTemplatesBuilder;
use App\Livewire\BulkMailer\Templates\Index as BulkMailerTemplatesIndex;
use App\Livewire\BulkMailer\Verifications\Index as BulkMailerVerificationsIndex;
use Illuminate\Support\Facades\Route;

Route::post('/bulk-mailer/webhooks/{provider}', BulkMailerWebhookController::class)
    ->name('bulk-mailer.webhooks.handle');

Route::get('/bulk-mailer/public/unsubscribe/{campaign}/{contact}', [BulkMailerPublicUnsubscribeController::class, 'show'])
    ->name('bulk-mailer.public.unsubscribe.show')
    ->middleware('signed');

Route::post('/bulk-mailer/public/unsubscribe/{campaign}/{contact}', [BulkMailerPublicUnsubscribeController::class, 'store'])
    ->name('bulk-mailer.public.unsubscribe.store')
    ->middleware('signed');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/', 'dashboard')->name('home');
    Route::redirect('/dashboard', '/');

    Route::prefix('bulk-mailer')->name('bulk-mailer.')->group(function () {
        Route::get('/', BulkMailerDashboard::class)->name('dashboard');
        Route::get('/smtp-accounts', BulkMailerSmtpAccountsIndex::class)->name('smtp-accounts.index');
        Route::get('/smtp-analytics', BulkMailerSmtpAnalyticsIndex::class)->name('smtp-analytics.index');
        Route::get('/lists', BulkMailerListsIndex::class)->name('lists.index');
        Route::get('/segments', BulkMailerSegmentsIndex::class)->name('segments.index');
        Route::get('/contacts', BulkMailerContactsIndex::class)->name('contacts.index');
        Route::get('/contacts/export', BulkMailerContactExportController::class)->name('contacts.export');
        Route::get('/verifications', BulkMailerVerificationsIndex::class)->name('verifications.index');
        Route::get('/templates', BulkMailerTemplatesIndex::class)->name('templates.index');
        Route::get('/templates/builder', BulkMailerTemplatesBuilder::class)->name('templates.builder');
        Route::get('/campaigns', BulkMailerCampaignsIndex::class)->name('campaigns.index');
        Route::get('/campaigns/calendar', BulkMailerCampaignCalendarIndex::class)->name('campaigns.calendar');
        Route::get('/campaigns/{campaign}', BulkMailerCampaignsShow::class)->name('campaigns.show');
        Route::get('/reports', BulkMailerReportsIndex::class)->name('reports.index');
        Route::get('/operations', BulkMailerQueueMonitor::class)->name('operations.index');
    });
});

require __DIR__.'/settings.php';