<?php

use Illuminate\Support\Facades\Route;
use App\Models\Domain;
use App\Models\Query;
use App\Models\Page;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('domains/{domain}/snapshots/{date}', function (Domain $domain, $date) {
        return view('pages.snapshots', ['domain' => $domain, 'date' => $date]);
    })->name('snapshots.show');

    Route::get('domains/{domain}/keywords', function (Domain $domain) {
        return view('pages.keywords', ['domain' => $domain]);
    })->name('keywords.index');

    Route::get('keywords/{query}', function (Query $query) {
        return view('pages.keyword_detail', ['query' => $query->load('domain')]);
    })->name('keywords.show');

    Route::get('domains/{domain}/pages', function (Domain $domain) {
        return view('pages.pages', ['domain' => $domain]);
    })->name('pages.index');

    Route::get('pages/{page}', function (Page $page) {
        return view('pages.page_detail', ['page' => $page->load('domain')]);
    })->name('pages.show');

    Route::get('domains/{domain}/opportunities', function (Domain $domain) {
        return view('pages.opportunities', ['domain' => $domain]);
    })->name('opportunities.index');

    Route::get('domains/{domain}/changes', function (Domain $domain) {
        return view('pages.changes', ['domain' => $domain]);
    })->name('changes.index');

    Route::get('domains/{domain}/trends', function (Domain $domain) {
        return view('pages.trends', ['domain' => $domain]);
    })->name('trends.index');

    Route::get('actions', function () {
        return view('pages.actions');
    })->name('actions.index');

    Route::get('sync-logs', function () {
        return view('pages.sync_logs');
    })->name('sync_logs.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
