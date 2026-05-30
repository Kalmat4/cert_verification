<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/history')->name('home');
Route::get('/history', [CertificateController::class, 'history'])->name('history');

Route::get('/certificate/create', [CertificateController::class, 'create'])->name('certificate.create');
Route::post('/certificate', [CertificateController::class, 'store'])->name('certificate.store');
Route::get('/certificate/{cert}/edit', [CertificateController::class, 'edit'])->name('certificate.edit');
Route::put('/certificate/{cert}', [CertificateController::class, 'update'])->name('certificate.update');
Route::delete('/certificate/{cert}', [CertificateController::class, 'destroy'])->name('certificate.destroy');
Route::get('/certificate/{cert}/word', [CertificateController::class, 'downloadWord'])->name('certificate.word');
Route::get('/certificate/{cert}/pdf', [CertificateController::class, 'downloadPdf'])->name('certificate.pdf');
Route::get('/certificate/{cert}/protocol/word', [CertificateController::class, 'downloadProtocolWord'])->name('certificate.protocol.word');
Route::get('/certificate/{cert}/protocol/pdf', [CertificateController::class, 'downloadProtocolPdf'])->name('certificate.protocol.pdf');
Route::get('/certificate/{cert}/garant/word', [CertificateController::class, 'downloadGarantWord'])->name('certificate.garant.word');
Route::get('/certificate/{cert}/garant/pdf', [CertificateController::class, 'downloadGarantPdf'])->name('certificate.garant.pdf');

Route::post('/certificates/download-zip', [CertificateController::class, 'downloadZip'])->name('certificates.zip');

Route::get('/clients', [\App\Http\Controllers\ClientController::class, 'index'])->name('clients');
Route::get('/clients/{client}/excerpt', [\App\Http\Controllers\ClientController::class, 'excerpt'])->name('clients.excerpt');
Route::get('/clients/{client}/meters-certs', [\App\Http\Controllers\ClientController::class, 'metersWithCerts'])->name('clients.meters-certs');

Route::get('/api/clients', [\App\Http\Controllers\ClientController::class, 'search'])->name('api.clients');
Route::get('/api/meters/{meter}', [\App\Http\Controllers\ClientController::class, 'meterDetails'])->name('api.meter');
