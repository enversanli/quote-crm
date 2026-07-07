<?php

use App\Models\Quote;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/quotes/{quote}/preview', function (Quote $quote) {
    $quote->load(['customer', 'business', 'quoteLines.orderItem.itemGroup']);

    return view('pdf.quote', [
        'quote'      => $quote,
        'showPrices' => request()->boolean('show_prices', true),
        'language'   => request()->string('language', 'en')->toString(),
        'preview'    => true,
    ]);
})->middleware('auth')->name('quotes.preview');
