<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CertificateController::class, 'index'])->name('home');
Route::get('/history', [CertificateController::class, 'history'])->name('history');

Route::get('/certificate/create', [CertificateController::class, 'create'])->name('certificate.create');
Route::post('/certificate', [CertificateController::class, 'store'])->name('certificate.store');
Route::get('/certificate/{cert}/edit', [CertificateController::class, 'edit'])->name('certificate.edit');
Route::put('/certificate/{cert}', [CertificateController::class, 'update'])->name('certificate.update');
Route::get('/certificate/{cert}/word', [CertificateController::class, 'downloadWord'])->name('certificate.word');
Route::get('/certificate/{cert}/pdf', [CertificateController::class, 'downloadPdf'])->name('certificate.pdf');
Route::get('/certificate/{cert}/protocol/word', [CertificateController::class, 'downloadProtocolWord'])->name('certificate.protocol.word');
Route::get('/certificate/{cert}/protocol/pdf', [CertificateController::class, 'downloadProtocolPdf'])->name('certificate.protocol.pdf');
Route::get('/certificate/{cert}/garant/word', [CertificateController::class, 'downloadGarantWord'])->name('certificate.garant.word');
Route::get('/certificate/{cert}/garant/pdf', [CertificateController::class, 'downloadGarantPdf'])->name('certificate.garant.pdf');
