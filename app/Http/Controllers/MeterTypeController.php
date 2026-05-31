<?php

namespace App\Http\Controllers;

use App\Models\MeterType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MeterTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = MeterType::withCount('meters')
            ->when($request->filled('q'), fn ($q) =>
                $q->where('type_name', 'like', '%' . $request->input('q') . '%')
                  ->orWhere('manufacturer', 'like', '%' . $request->input('q') . '%')
            )
            ->orderBy('type_name');

        return Inertia::render('MeterTypes', [
            'types'   => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type_name'            => ['required', 'string', 'max:255'],
            'manufacturer'         => ['required', 'string', 'max:255'],
            'verification_method'  => ['required', 'string', 'max:255'],
            'verify_interval_years'=> ['required', 'integer', 'min:1', 'max:20'],
        ]);

        MeterType::create($data);

        return back()->with('success', 'Тип счётчика добавлен');
    }

    public function update(Request $request, MeterType $meterType): RedirectResponse
    {
        $data = $request->validate([
            'type_name'            => ['required', 'string', 'max:255'],
            'manufacturer'         => ['required', 'string', 'max:255'],
            'verification_method'  => ['required', 'string', 'max:255'],
            'verify_interval_years'=> ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $meterType->update($data);

        return back()->with('success', 'Тип счётчика обновлён');
    }

    public function destroy(MeterType $meterType): RedirectResponse
    {
        if ($meterType->meters()->exists()) {
            return back()->with('error', 'Нельзя удалить: тип используется ' . $meterType->meters()->count() . ' счётчиком(и)');
        }

        $meterType->delete();

        return back()->with('success', 'Тип счётчика удалён');
    }
}
