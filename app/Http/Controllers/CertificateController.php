<?php

namespace App\Http\Controllers;

use App\Models\Cert;
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

        return redirect()->route('certificate.edit', $cert->id)
            ->with('success', 'Данные о поверке сохранены');
    }

    public function edit(Cert $cert): Response
    {
        return Inertia::render('Certificate', ['cert' => $cert]);
    }

    public function update(Request $request, Cert $cert): RedirectResponse
    {
        $data = $this->validated($request);
        $cert->update($data);

        return redirect()->route('certificate.edit', $cert->id)
            ->with('success', 'Данные обновлены');
    }

    public function downloadWord(Cert $cert): BinaryFileResponse
    {
        $data     = $cert->toArray();
        $docxPath = $this->buildDocx($data);
        $filename = sprintf('cert_%s_%s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

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

        $filename = sprintf('cert_%s_%s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadGarantWord(Cert $cert): BinaryFileResponse
    {
        $data     = $cert->toArray();
        $docxPath = $this->buildGarantDocx($data);
        $filename = sprintf('garant_%s_%s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

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

        $filename = sprintf('garant_%s_%s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'cert_number'  => ['required'],
            'zavod_number' => ['required'],
            'type_model'   => ['nullable'],
            'make_year'    => ['required'],
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
        $processor->setValue('cert_number',  $data['cert_number']);
        $processor->setValue('zavod_number', $data['zavod_number']);
        $processor->setValue('make_year',    $data['make_year']);
        $processor->setValue('fio_address',  trim(($data['fio'] ?? '') . ' ' . ($data['address'] ?? '')));
        $processor->setValue('water_data',   $data['water_data']);
        $processor->setValue('class',        $data['class']);
        $processor->setValue('check_date',   $data['check_date']);
        $processor->setValue('final_date',   $data['final_date']);
        $processor->setValue('plomb_number', $data['plomb_number']);

        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'cert_' . Str::uuid() . '.docx';
        $processor->saveAs($path);

        return $path;
    }
}
