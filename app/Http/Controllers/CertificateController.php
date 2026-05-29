<?php

namespace App\Http\Controllers;

use App\Models\Cert;
use App\Models\CertReading;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificateController extends Controller
{
    private string $templatePath;
    private string $garantTemplatePath;
    private string $tempDir;

    public function __construct()
    {
        $this->templatePath       = storage_path('app/templates/cert_template.docx');
        $this->garantTemplatePath = storage_path('app/templates/garant.docx');
        $this->tempDir            = storage_path('app/temp');
    }

    public function index(): Response
    {
        return Inertia::render('Home', [
            'certs' => Cert::latest()->take(5)->get(),
        ]);
    }

    public function history(): Response
    {
        return Inertia::render('History', [
            'certs' => Cert::latest()->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Certificate', ['cert' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $cert = Cert::create($data);
        $this->syncReadings($cert, $request->input('readings', []));

        return redirect()->route('certificate.edit', $cert->id)
            ->with('success', 'Данные о поверке сохранены');
    }

    public function edit(Cert $cert): Response
    {
        return Inertia::render('Certificate', ['cert' => $cert->load('readings')]);
    }

    public function update(Request $request, Cert $cert): RedirectResponse
    {
        $data = $this->validated($request);
        $cert->update($data);
        $this->syncReadings($cert, $request->input('readings', []));

        return redirect()->route('certificate.edit', $cert->id)
            ->with('success', 'Данные обновлены');
    }

    private function syncReadings(Cert $cert, array $readings): void
    {
        $cert->readings()->delete();
        foreach ($readings as $i => $row) {
            $cert->readings()->create([
                'sort_order' => $i,
                'n'          => $row['n']          ?? null,
                'dn'         => $row['dn']         ?? null,
                'qmin_s'     => $row['qmin_s']     ?? null,
                'qmin_e'     => $row['qmin_e']     ?? null,
                'qmin_p'     => $row['qmin_p']     ?? null,
                'qn_s'       => $row['qn_s']       ?? null,
                'qn_e'       => $row['qn_e']       ?? null,
                'qn_p'       => $row['qn_p']       ?? null,
                'qmax_s'     => $row['qmax_s']     ?? null,
                'qmax_e'     => $row['qmax_e']     ?? null,
                'qmax_p'     => $row['qmax_p']     ?? null,
                'before_val' => $row['before_val'] ?? null,
                'after_val'  => $row['after_val']  ?? null,
            ]);
        }
    }

    public function downloadWord(Cert $cert): BinaryFileResponse
    {
        $data     = $cert->toArray();
        $docxPath = $this->buildDocx($data);
        $filename = sprintf('Сертификат о поверке %s %s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadPdf(Cert $cert)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return response()->json([
                'error' => 'Конвертация в PDF недоступна на Windows.',
            ], 501);
        }

        $data     = $cert->toArray();
        $docxPath = $this->buildDocx($data);
        $pdfDir   = dirname($docxPath);

        $command = sprintf(
            'HOME=/tmp libreoffice --headless --norestore --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($pdfDir),
            escapeshellarg($docxPath)
        );

        shell_exec($command);
        @unlink($docxPath);

        $pdfPath = substr($docxPath, 0, -5) . '.pdf';

        if (!file_exists($pdfPath)) {
            return response()->json(['error' => 'Не удалось сконвертировать в PDF'], 500);
        }

        $filename = sprintf('Сертификат о поверке %s %s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadProtocolWord(Cert $cert): BinaryFileResponse
    {
        $data     = $cert->load('readings')->toArray();
        $docxPath = $this->buildProtocolDocx($data);
        $filename = sprintf('Протокол поверки %s %s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadProtocolPdf(Cert $cert)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return response()->json([
                'error' => 'Конвертация в PDF недоступна на Windows.',
            ], 501);
        }

        $data     = $cert->load('readings')->toArray();
        $docxPath = $this->buildProtocolDocx($data);
        $pdfDir   = dirname($docxPath);

        $command = sprintf(
            'HOME=/tmp libreoffice --headless --norestore --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($pdfDir),
            escapeshellarg($docxPath)
        );

        shell_exec($command);
        @unlink($docxPath);

        $pdfPath = substr($docxPath, 0, -5) . '.pdf';

        if (!file_exists($pdfPath)) {
            return response()->json(['error' => 'Не удалось сконвертировать в PDF'], 500);
        }

        $filename = sprintf('Протокол поверки %s %s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadGarantWord(Cert $cert): BinaryFileResponse
    {
        $data     = $cert->toArray();
        $docxPath = $this->buildGarantDocx($data);
        $filename = sprintf('Гарантийное соглашение %s %s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadGarantPdf(Cert $cert)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return response()->json([
                'error' => 'Конвертация в PDF недоступна на Windows.',
            ], 501);
        }

        $data     = $cert->toArray();
        $docxPath = $this->buildGarantDocx($data);
        $pdfDir   = dirname($docxPath);

        $command = sprintf(
            'HOME=/tmp libreoffice --headless --norestore --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($pdfDir),
            escapeshellarg($docxPath)
        );

        shell_exec($command);
        @unlink($docxPath);

        $pdfPath = substr($docxPath, 0, -5) . '.pdf';

        if (!file_exists($pdfPath)) {
            return response()->json(['error' => 'Не удалось сконвертировать в PDF'], 500);
        }

        $filename = sprintf('Гарантийное соглашение %s %s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'cert_number'  => ['required'],
            'zavod_number' => ['required'],
            'type_model'   => ['nullable'],
            'manufacturer'        => ['nullable'],
            'verification_method' => ['nullable'],
            'verifier'            => ['nullable'],
            'make_year'           => ['required'],
            'fio'          => ['required'],
            'address'      => ['required'],
            'phone'        => ['nullable'],
            'water_data'   => ['required', 'numeric'],
            'class'        => ['required'],
            'check_date'   => ['required', 'date_format:d.m.Y'],
            'plomb_number' => ['required'],
        ], [
            'check_date.date_format' => 'Дата поверки: формат ДД.ММ.ГГГГ',
            'water_data.numeric'     => 'Показания счётчика должны быть числом',
        ]);

        $data['final_date'] = Carbon::createFromFormat('d.m.Y', $data['check_date'])
            ->addYears(5)
            ->format('d.m.Y');

        return $data;
    }

    private function buildGarantDocx(array $data): string
    {
        abort_unless(file_exists($this->garantTemplatePath), 500, 'Шаблон не найден: app/templates/garant.docx');

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $processor = new TemplateProcessor($this->garantTemplatePath);
        $processor->setValue('fio',          $data['fio'] ?? '');
        $processor->setValue('address',      $data['address'] ?? '');
        $processor->setValue('phone',        $data['phone'] ?? '');
        $processor->setValue('type',         $data['type_model'] ?? '');
        $processor->setValue('zavod_number', $data['zavod_number']);
        $processor->setValue('check_date',   $data['check_date']);

        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'garant_' . Str::uuid() . '.docx';
        $processor->saveAs($path);

        return $path;
    }

    private function buildDocx(array $data): string
    {
        abort_unless(file_exists($this->templatePath), 500, 'Шаблон не найден: app/templates/cert_template.docx');

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $processor = new TemplateProcessor($this->templatePath);
        $processor->setValue('type',  $data['type_model']);
        $processor->setValue('manufacturer',  $data['manufacturer']);
        $processor->setValue('verification_method',  $data['verification_method']);
        $processor->setValue('verifier',  $data['verifier']);
        $processor->setValue('cert_number',  $data['cert_number']);
        $processor->setValue('zavod_number', $data['zavod_number']);
        $processor->setValue('make_year',    $data['make_year']);
        $processor->setValue('fio_address',  trim(($data['fio'] ?? '') . ' ' . ($data['address'] ?? '')));
        $processor->setValue('water_data',   $data['water_data']);
        $processor->setValue('verifier',   $data['verifier']);
        $processor->setValue('class',        $data['class']);
        $processor->setValue('check_date',   $data['check_date']);
        $processor->setValue('final_date',   $data['final_date']);
        $processor->setValue('plomb_number', $data['plomb_number']);

        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'cert_' . Str::uuid() . '.docx';
        $processor->saveAs($path);

        return $path;
    }

    private function buildProtocolDocx(array $data): string
    {
        $protocolTemplatePath = storage_path('app/templates/protocol.docx');
        abort_unless(file_exists($protocolTemplatePath), 500, 'Шаблон не найден: app/templates/protocol.docx');

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $processor = new TemplateProcessor($protocolTemplatePath);
        $processor->setValue('certID',              '000' . $data['id']);
        $processor->setValue('type',                $data['type_model'] ?? '');
        $processor->setValue('zavod_number',        $data['zavod_number']);
        $processor->setValue('class',               $data['class']);
        $processor->setValue('make_year',           $data['make_year']);
        $processor->setValue('manufacturer',        $data['manufacturer'] ?? '');
        $processor->setValue('verification_method', $data['verification_method'] ?? '');
        $processor->setValue('verifier',            $data['verifier'] ?? '');
        $processor->setValue('check_date',          $data['check_date']);

        $readings = $data['readings'] ?? [];
        $count    = max(1, count($readings));
        $processor->cloneRow('row_n', $count);

        foreach ($readings as $i => $row) {
            $idx = $i + 1;
            $processor->setValue("row_n#{$idx}",      $row['sort_order']+1          ?? '');
            $processor->setValue("row_dn#{$idx}",     $row['dn']         ?? '');
            $processor->setValue("row_qmin_s#{$idx}", $row['qmin_s']     ?? '');
            $processor->setValue("row_qmin_e#{$idx}", $row['qmin_e']     ?? '');
            $processor->setValue("row_qmin_p#{$idx}", $row['qmin_p']     ?? '');
            $processor->setValue("row_qn_s#{$idx}",   $row['qn_s']       ?? '');
            $processor->setValue("row_qn_e#{$idx}",   $row['qn_e']       ?? '');
            $processor->setValue("row_qn_p#{$idx}",   $row['qn_p']       ?? '');
            $processor->setValue("row_qmax_s#{$idx}", $row['qmax_s']     ?? '');
            $processor->setValue("row_qmax_e#{$idx}", $row['qmax_e']     ?? '');
            $processor->setValue("row_qmax_p#{$idx}", $row['qmax_p']     ?? '');
            $processor->setValue("row_before#{$idx}", $row['before_val'] ?? '');
            $processor->setValue("row_after#{$idx}",  $row['after_val']  ?? '');
        }

        if (empty($readings)) {
            foreach (['row_n','row_dn','row_qmin_s','row_qmin_e','row_qmin_p','row_qn_s','row_qn_e','row_qn_p','row_qmax_s','row_qmax_e','row_qmax_p','row_before','row_after'] as $key) {
                $processor->setValue("{$key}#1", '');
            }
        }

        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'protocol_' . Str::uuid() . '.docx';
        $processor->saveAs($path);

        return $path;
    }
}
