<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Meter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Client::withCount('meters')->orderBy('fio');

        if ($request->filled('fio')) {
            $query->where('fio', 'like', '%' . $request->input('fio') . '%');
        }

        if ($request->filled('address')) {
            $query->where('address', 'like', '%' . $request->input('address') . '%');
        }

        return Inertia::render('Clients', [
            'clients' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['fio', 'address']),
        ]);
    }

    public function metersWithCerts(Client $client): JsonResponse
    {
        $meters = $client->meters()->orderBy('id')->get();

        $data = $meters->map(function ($meter) {
            $certs = $meter->certs()->orderByDesc('id')->get(['id', 'cert_number', 'check_date']);
            return [
                'id'           => $meter->id,
                'zavod_number' => $meter->zavod_number,
                'type_model'   => $meter->type_model ?? '',
                'certs'        => $certs,
            ];
        });

        return response()->json([
            'id'     => $client->id,
            'fio'    => $client->fio,
            'meters' => $data,
        ]);
    }

    public function excerpt(Client $client): JsonResponse
    {
        $meters = $client->meters()->orderBy('id')->get();

        $metersData = $meters->map(function ($meter) {
            $lastCert = $meter->certs()->orderByDesc('id')->first();
            return [
                'type_model'   => $meter->type_model   ?? '',
                'make_year'    => $meter->make_year    ?? '',
                'zavod_number' => $meter->zavod_number,
                'plomb_number' => $lastCert?->plomb_number ?? '',
            ];
        });

        return response()->json([
            'fio'     => $client->fio,
            'address' => $client->address,
            'phone'   => $client->phone ?? '',
            'meters'  => $metersData,
        ]);
    }

    public function meterDetails(Meter $meter): JsonResponse
    {
        $lastCert = $meter->certs()->with('readings')->orderByDesc('id')->first();

        return response()->json([
            'id'           => $meter->id,
            'zavod_number' => $meter->zavod_number,
            'type_model'   => $meter->type_model   ?? '',
            'manufacturer' => $meter->manufacturer ?? '',
            'make_year'    => $meter->make_year    ?? '',
            'class'        => $meter->class        ?? '',
            'last_cert'    => $lastCert ? [
                'cert_number'         => $lastCert->cert_number,
                'verification_method' => $lastCert->verification_method ?? '',
                'plomb_number'        => $lastCert->plomb_number,
                'water_data'          => $lastCert->water_data,
                'check_date'          => $lastCert->check_date,
                'final_date'          => $lastCert->final_date,
                'readings'            => $lastCert->readings->toArray(),
            ] : null,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        $clients = Client::query()
            ->when($q, fn ($query) => $query
                ->where('fio',       'like', "%{$q}%")
                ->orWhere('phone',   'like', "%{$q}%")
                ->orWhere('address', 'like', "%{$q}%")
            )
            ->with('meters')
            ->orderBy('fio')
            ->limit(15)
            ->get();

        return response()->json($clients);
    }
}
