<?php

namespace App\Http\Controllers;

use App\Models\Cert;
use App\Models\CertReading;
use App\Models\Client;
use App\Models\Meter;
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

    // ── Pages ─────────────────────────────────────────────────────────────────

    public function index(): Response
    {
        return Inertia::render('Home', [
            'certs' => Cert::with('meter.client')->latest()->take(5)->get(),
        ]);
    }

    public function history(Request $request): Response
    {
        $query = Cert::with([
                'meter',
                'meter.client' => fn ($q) => $q->withCount('meters'),
            ])
            ->latest();

        if ($request->filled('fio')) {
            $query->whereHas('meter.client', fn ($q) =>
                $q->where('fio', 'like', '%' . $request->input('fio') . '%')
            );
        }

        if ($request->filled('check_date')) {
            $query->where('check_date', 'like', '%' . $request->input('check_date') . '%');
        }

        return Inertia::render('History', [
            'certs'   => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['fio', 'check_date']),
        ]);
    }

    public function create(Request $request): Response
    {
        $copyFrom = $request->has('copy_id')
            ? Cert::with('meter.client.meters', 'readings')->find($request->integer('copy_id'))
            : null;

        return Inertia::render('Certificate', [
            'cert'     => null,
            'copyFrom' => $copyFrom,
        ]);
    }

    public function edit(Cert $cert): Response
    {
        $cert->load('meter.client.meters', 'readings');

        return Inertia::render('Certificate', [
            'cert'     => $cert,
            'copyFrom' => null,
        ]);
    }

    // ── Store / Update ────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        [$client, $meter, $certData] = $this->resolveClientMeterCert($request);

        $cert = Cert::create(array_merge($certData, ['meter_id' => $meter->id]));
        $this->syncReadings($cert, $request->input('readings', []));

        return redirect()->route('certificate.edit', $cert->id)
            ->with('success', 'Данные о поверке сохранены');
    }

    public function update(Request $request, Cert $cert): RedirectResponse
    {
        $cert->load('meter.client');

        // При редактировании: если client_id/meter_id не пришли (null),
        // подставляем существующие значения, чтобы не создавать дубликаты
        if ($request->input('client_id') === null) {
            $request->merge(['client_id' => $cert->meter->client_id]);
        }
        if ($request->input('meter_id') === null) {
            $request->merge(['meter_id' => $cert->meter_id]);
        }

        [$client, $meter, $certData] = $this->resolveClientMeterCert($request);

        $cert->update(array_merge($certData, ['meter_id' => $meter->id]));
        $this->syncReadings($cert, $request->input('readings', []));

        return redirect()->route('certificate.edit', $cert->id)
            ->with('success', 'Данные обновлены');
    }

    public function destroy(Cert $cert): RedirectResponse
    {
        $cert->delete();

        return redirect()->route('history')->with('success', 'Сертификат удалён');
    }

    // ── Downloads ─────────────────────────────────────────────────────────────

    public function downloadWord(Cert $cert): BinaryFileResponse
    {
        $data     = $this->certToDocxData($cert);
        $docxPath = $this->buildDocx($data);
        $filename = sprintf('Сертификат о поверке %s %s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadPdf(Cert $cert)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return response()->json(['error' => 'Конвертация в PDF недоступна на Windows.'], 501);
        }

        $data     = $this->certToDocxData($cert);
        $docxPath = $this->buildDocx($data);

        return $this->convertAndDownloadPdf(
            $docxPath,
            sprintf('Сертификат о поверке %s %s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']))
        );
    }

    public function downloadProtocolWord(Cert $cert): BinaryFileResponse
    {
        $data     = $this->certToDocxData($cert);
        $docxPath = $this->buildProtocolDocx($data);
        $filename = sprintf('Протокол поверки %s %s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadProtocolPdf(Cert $cert)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return response()->json(['error' => 'Конвертация в PDF недоступна на Windows.'], 501);
        }

        $data     = $this->certToDocxData($cert);
        $docxPath = $this->buildProtocolDocx($data);

        return $this->convertAndDownloadPdf(
            $docxPath,
            sprintf('Протокол поверки %s %s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']))
        );
    }

    public function downloadGarantWord(Cert $cert): BinaryFileResponse
    {
        $data     = $this->certToDocxData($cert);
        $docxPath = $this->buildGarantDocx($data);
        $filename = sprintf('Гарантийное соглашение %s %s.docx', $data['zavod_number'], str_replace('.', '', $data['check_date']));

        return response()->download($docxPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadGarantPdf(Cert $cert)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return response()->json(['error' => 'Конвертация в PDF недоступна на Windows.'], 501);
        }

        $data     = $this->certToDocxData($cert);
        $docxPath = $this->buildGarantDocx($data);

        return $this->convertAndDownloadPdf(
            $docxPath,
            sprintf('Гарантийное соглашение %s %s.pdf', $data['zavod_number'], str_replace('.', '', $data['check_date']))
        );
    }

    public function downloadZip(Request $request): BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer', 'exists:certs,id'],
            'type'   => ['required', 'in:cert,protocol,garant'],
            'format' => ['required', 'in:word,pdf'],
        ]);

        $type   = $request->input('type');
        $format = $request->input('format', 'word');

        if ($format === 'pdf' && PHP_OS_FAMILY === 'Windows') {
            return response()->json(['error' => 'Конвертация в PDF недоступна на Windows.'], 501);
        }

        $certs = Cert::with('meter.client', 'readings')->whereIn('id', $request->input('ids'))->get();

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $zipPath = $this->tempDir . DIRECTORY_SEPARATOR . 'archive_' . Str::uuid() . '.zip';
        $zip     = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $tempFiles = [];

        foreach ($certs as $cert) {
            $data     = $this->certToDocxData($cert);
            $safeName = preg_replace('/[\\\\\\/:*?"<>|]/', '_', "{$data['zavod_number']}_{$data['check_date']}");

            [$docxPath, $baseName] = match ($type) {
                'cert'     => [$this->buildDocx($data),         "{$cert->id}_{$safeName}_Сертификат"],
                'protocol' => [$this->buildProtocolDocx($data), "{$cert->id}_{$safeName}_Протокол"],
                'garant'   => [$this->buildGarantDocx($data),   "{$cert->id}_{$safeName}_Гарантия"],
            };

            if ($format === 'pdf') {
                $command = sprintf(
                    'HOME=/tmp libreoffice --headless --norestore --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
                    escapeshellarg(dirname($docxPath)),
                    escapeshellarg($docxPath)
                );
                shell_exec($command);
                @unlink($docxPath);
                $pdfPath = substr($docxPath, 0, -5) . '.pdf';
                if (file_exists($pdfPath)) {
                    $zip->addFile($pdfPath, $baseName . '.pdf');
                    $tempFiles[] = $pdfPath;
                }
            } else {
                $zip->addFile($docxPath, $baseName . '.docx');
                $tempFiles[] = $docxPath;
            }
        }

        $zip->close();
        foreach ($tempFiles as $f) {
            @unlink($f);
        }

        $label = match ($type) {
            'cert'     => 'Сертификаты',
            'protocol' => 'Протоколы',
            'garant'   => 'Гарантии',
        };

        return response()->download($zipPath, "{$label} " . ($format === 'pdf' ? 'PDF' : 'Word') . ".zip")
            ->deleteFileAfterSend(true);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Разрешает клиента, счётчик и данные поверки из запроса.
     */
    private function resolveClientMeterCert(Request $request, ?Cert $existing = null): array
    {
        $data = $request->validate([
            'client_id'          => ['nullable', 'exists:clients,id'],
            'fio'                => ['required'],
            'address'            => ['required'],
            'phone'              => ['nullable'],
            'meter_id'           => ['nullable', 'exists:meters,id'],
            'zavod_number'       => ['required'],
            'type_model'         => ['nullable'],
            'manufacturer'       => ['nullable'],
            'make_year'          => ['nullable'],
            'class'              => ['nullable'],
            'cert_number'        => ['required'],
            'verification_method'=> ['nullable'],
            'verifier'           => ['nullable'],
            'plomb_number'       => ['required'],
            'water_data'         => ['required', 'numeric'],
            'check_date'         => ['required', 'date_format:d.m.Y'],
        ], [
            'check_date.date_format' => 'Дата поверки: формат ДД.ММ.ГГГГ',
            'water_data.numeric'     => 'Показания счётчика должны быть числом',
        ]);

        // Client
        if ($data['client_id'] ?? null) {
            $client = Client::findOrFail($data['client_id']);
            $client->update(['fio' => $data['fio'], 'address' => $data['address'], 'phone' => $data['phone'] ?? null]);
        } else {
            $client = Client::create(['fio' => $data['fio'], 'address' => $data['address'], 'phone' => $data['phone'] ?? null]);
        }

        // Meter
        if ($data['meter_id'] ?? null) {
            $meter = Meter::findOrFail($data['meter_id']);
            $meter->update([
                'zavod_number' => $data['zavod_number'],
                'type_model'   => $data['type_model']   ?? null,
                'manufacturer' => $data['manufacturer'] ?? null,
                'make_year'    => $data['make_year']    ?? null,
                'class'        => $data['class']        ?? null,
            ]);
        } else {
            $meter = $client->meters()->create([
                'zavod_number' => $data['zavod_number'],
                'type_model'   => $data['type_model']   ?? null,
                'manufacturer' => $data['manufacturer'] ?? null,
                'make_year'    => $data['make_year']    ?? null,
                'class'        => $data['class']        ?? null,
            ]);
        }

        $certData = [
            'cert_number'         => $data['cert_number'],
            'verification_method' => $data['verification_method'] ?? null,
            'verifier'            => $data['verifier']            ?? null,
            'plomb_number'        => $data['plomb_number'],
            'water_data'          => $data['water_data'],
            'check_date'          => $data['check_date'],
            'final_date'          => Carbon::createFromFormat('d.m.Y', $data['check_date'])->addYears(5)->format('d.m.Y'),
        ];

        return [$client, $meter, $certData];
    }

    /**
     * Возвращает плоский массив данных для шаблонов docx.
     */
    private function certToDocxData(Cert $cert): array
    {
        $cert->loadMissing('meter.client', 'readings');
        $meter  = $cert->meter;
        $client = $meter?->client;

        return array_merge($cert->toArray(), [
            'fio'          => $client?->fio          ?? '',
            'address'      => $client?->address      ?? '',
            'phone'        => $client?->phone        ?? '',
            'zavod_number' => $meter?->zavod_number  ?? '',
            'type_model'   => $meter?->type_model    ?? '',
            'manufacturer' => $meter?->manufacturer  ?? '',
            'make_year'    => $meter?->make_year     ?? '',
            'class'        => $meter?->class         ?? '',
        ]);
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

    private function convertAndDownloadPdf(string $docxPath, string $filename)
    {
        $pdfDir  = dirname($docxPath);
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

        return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
    }

    private function buildGarantDocx(array $data): string
    {
        abort_unless(file_exists($this->garantTemplatePath), 500, 'Шаблон не найден: app/templates/garant.docx');

        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }

        $checkDate = Carbon::createFromFormat('d.m.Y', $data['check_date']);
        $months = [
            1 => 'января', 2 => 'февраля', 3 => 'марта',  4 => 'апреля',
            5 => 'мая',    6 => 'июня',    7 => 'июля',    8 => 'августа',
            9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
        ];

        $processor = new TemplateProcessor($this->garantTemplatePath);
        $processor->setValue('fio',              $data['fio'] ?? '');
        $processor->setValue('address',          $data['address'] ?? '');
        $processor->setValue('phone',            $data['phone'] ?? '');
        $processor->setValue('type',             $data['type_model'] ?? '');
        $processor->setValue('zavod_number',     $data['zavod_number']);
        $processor->setValue('check_date',       $data['check_date']);
        $processor->setValue('garant_number',    $data['garant_number'] ?? '');
        $processor->setValue('check_date_day',   $checkDate->format('d'));
        $processor->setValue('check_date_month', $months[(int) $checkDate->format('n')]);
        $processor->setValue('check_date_year',  substr($checkDate->format('Y'), -1));

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
        $processor->setValue('type',                $data['type_model']);
        $processor->setValue('manufacturer',        $data['manufacturer']);
        $processor->setValue('verification_method', $data['verification_method']);
        $processor->setValue('verifier',            $data['verifier']);
        $processor->setValue('cert_number',         $data['cert_number']);
        $processor->setValue('zavod_number',        $data['zavod_number']);
        $processor->setValue('make_year',           $data['make_year']);
        $processor->setValue('fio_address',         trim(($data['fio'] ?? '') . ' ' . ($data['address'] ?? '')));
        $processor->setValue('water_data',          $data['water_data']);
        $processor->setValue('class',               $data['class']);
        $processor->setValue('check_date',          $data['check_date']);
        $processor->setValue('final_date',          $data['final_date']);
        $processor->setValue('plomb_number',        $data['plomb_number']);

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
            $processor->setValue("row_n#{$idx}",      ($row['sort_order'] ?? $i) + 1);
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
