<?php

use Illuminate\Support\Facades\Route;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/documents/{document}/print', function (Document $document) {
    $pdf = Pdf::loadView('documents.pdf', ['document' => $document]);
    return $pdf->stream("document_{$document->id}.pdf");
})->name('documents.print');
