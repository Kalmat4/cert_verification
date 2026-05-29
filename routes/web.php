<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;


Route::redirect('/', '/certificate');

Route::get('/certificate',               [CertificateController::class, 'index'])->name('certificate.index');
Route::post('/certificate/download-word', [CertificateController::class, 'downloadWord'])->name('certificate.word');
Route::post('/certificate/download-pdf',  [CertificateController::class, 'downloadPdf'])->name('certificate.pdf');
