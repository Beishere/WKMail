<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::with('induk')->withCount('pengguna')
            ->when($request->q, fn ($q, $kw) => $q->where('nama_unit', 'like', "%{$kw}%"))
            ->when($request->jenis, fn ($q, $v) => $q->where('jenis_unit', $v))
            ->orderBy('jenis_unit')->orderBy('nama_unit')
            ->paginate(20)->withQueryString();

        return view('master.unit.index', compact('units'));
    }

    public function create(): View
    {
        return $this->form(new Unit());
    }

    public function store(Request $request): RedirectResponse
    {
        Unit::create($this->validated($request));

        return redirect()->route('master.unit.index')->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return $this->form($unit);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $unit->update($this->validated($request, $unit));

        return redirect()->route('master.unit.index')->with('success', 'Unit diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->pengguna()->exists() || \App\Models\Disposisi::where('id_unit_tujuan', $unit->id_unit)->exists()) {
            return back()->with('error', 'Unit masih memiliki pengguna atau riwayat disposisi sehingga tidak dapat dihapus.');
        }

        $unit->delete();

        return back()->with('success', 'Unit dihapus.');
    }

    protected function form(Unit $unit): View
    {
        return view('master.unit.form', [
            'unit' => $unit,
            'indukOptions' => Unit::when($unit->exists, fn ($q) => $q->where('id_unit', '!=', $unit->id_unit))
                ->orderBy('nama_unit')->get(),
        ]);
    }

    protected function validated(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'nama_unit' => ['required', 'string', 'max:150'],
            'jenis_unit' => ['required', Rule::in(array_keys(Unit::JENIS))],
            'id_induk' => ['nullable', 'exists:unit,id_unit', Rule::notIn([$unit?->id_unit])],
        ]);
    }
}
