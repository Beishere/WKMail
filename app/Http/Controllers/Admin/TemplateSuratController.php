<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $data = $this->validated($request);
        $this->handleUploads($request, $data);

        $tpl = TemplateSurat::create($data);

        if ($request->boolean('terapkan_semua_kop') && $tpl->path_kop) {
            TemplateSurat::query()->update(['path_kop' => $tpl->path_kop]);
        }

        return redirect()->route('master.template.index')->with('success', 'Template surat ditambahkan.');
    }

    public function edit(TemplateSurat $template): View
    {
        return view('master.template.form', compact('template'));
    }

    public function update(Request $request, TemplateSurat $template): RedirectResponse
    {
        $data = $this->validated($request);
        $this->handleUploads($request, $data, $template);

        $template->update($data);

        if ($request->boolean('terapkan_semua_kop') && $template->path_kop) {
            TemplateSurat::query()->update(['path_kop' => $template->path_kop]);
        }

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
            'file_kop' => ['nullable', 'file', 'max:5120', 'mimes:png,jpg,jpeg,webp'],
            'file_template' => ['nullable', 'file', 'max:10240', 'mimes:doc,docx,pdf'],
        ]);
    }

    protected function handleUploads(Request $request, array &$data, ?TemplateSurat $existing = null): void
    {
        unset($data['file_kop'], $data['file_template']);

        if ($request->boolean('hapus_kop') && $existing?->path_kop) {
            $data['path_kop'] = null;
        }

        if ($request->hasFile('file_kop')) {
            $data['path_kop'] = $request->file('file_kop')->store('template/kop', 'local');
        }

        if ($request->boolean('hapus_file_template') && $existing?->path_file_template) {
            Storage::disk('local')->delete($existing->path_file_template);
            $data['path_file_template'] = null;
            $data['nama_file_template'] = null;
        }

        if ($request->hasFile('file_template')) {
            $file = $request->file('file_template');
            $data['path_file_template'] = $file->store('template/dokumen', 'local');
            $data['nama_file_template'] = $file->getClientOriginalName();
        }
    }
}
