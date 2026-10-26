<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateSuratController extends Controller
{
    public function index(): View
    {
        return view('master.template.index', [
            'templates' => TemplateSurat::withCount('surat')->orderBy('nama_template')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('master.template.form', ['template' => new TemplateSurat()]);
    }

    public function store(Request $request): RedirectResponse
    {
        TemplateSurat::create($this->validated($request));

        return redirect()->route('master.template.index')->with('success', 'Template surat ditambahkan.');
    }

    public function edit(TemplateSurat $template): View
    {
        return view('master.template.form', compact('template'));
    }

    public function update(Request $request, TemplateSurat $template): RedirectResponse
    {
        $template->update($this->validated($request));

        return redirect()->route('master.template.index')->with('success', 'Template surat diperbarui.');
    }

    public function destroy(TemplateSurat $template): RedirectResponse
    {
        $template->delete(); // relasi surat -> nullOnDelete

        return back()->with('success', 'Template dihapus.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'nama_template' => ['required', 'string', 'max:150'],
            'perihal_default' => ['required', 'string', 'max:255'],
            'isi_template' => ['required', 'string'],
        ]);
    }
}
