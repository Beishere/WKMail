<?php

namespace App\Services;

use App\Models\Surat;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

class WordSuratService
{
    /**
     * Hasilkan berkas .docx siap-edit di Microsoft Word lengkap dengan Kop Surat UKWK.
     *
     * @param  array{
     *   nomor_surat?: ?string,
     *   tanggal_surat?: ?string,
     *   sifat_surat?: ?string,
     *   perihal?: ?string,
     *   isi_surat?: ?string,
     *   template_path?: ?string
     * }  $data
     * @return string Path ke berkas sementara (.docx)
     */
    public function generateDocx(array $data): string
    {
        $sourceDocx = $data['template_path'] ?? null;
        if (! $sourceDocx || ! is_file($sourceDocx)) {
            $sourceDocx = public_path('kop-ukwk.docx');
        }

        if (! is_file($sourceDocx)) {
            throw new RuntimeException('Berkas template dasar kop-ukwk.docx tidak ditemukan.');
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'wkmail_word_') . '.docx';
        if (! copy($sourceDocx, $tempPath)) {
            throw new RuntimeException('Gagal menyiapkan berkas sementara Word.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tempPath) !== true) {
            @unlink($tempPath);
            throw new RuntimeException('Gagal membuka arsip OpenXML (.docx).');
        }

        $docXml = $zip->getFromName('word/document.xml');
        if ($docXml === false) {
            $zip->close();
            @unlink($tempPath);
            throw new RuntimeException('Struktur word/document.xml tidak ditemukan di dalam template.');
        }

        $nomor = trim((string) ($data['nomor_surat'] ?? '')) ?: '[Otomatis Saat Dikirim]';
        $tanggal = trim((string) ($data['tanggal_surat'] ?? '')) ?: now()->translatedFormat('d F Y');
        $sifat = trim((string) ($data['sifat_surat'] ?? 'Biasa')) ?: 'Biasa';
        $perihal = trim((string) ($data['perihal'] ?? '')) ?: '-';
        $isi = str_replace(["\r\n", "\r"], "\n", trim((string) ($data['isi_surat'] ?? '')));

        $paragraphsXml = '';

        // Blok atribut surat di bagian atas dokumen
        $metaLines = [
            "Nomor\t: {$nomor}",
            "Tanggal\t: {$tanggal}",
            "Sifat\t: {$sifat}",
            "Perihal\t: {$perihal}",
        ];

        foreach ($metaLines as $metaLine) {
            $paragraphsXml .= $this->buildParagraphXml($metaLine, false);
        }

        // Baris kosong pemisah antara atribut & isi surat
        $paragraphsXml .= $this->buildParagraphXml('', false);

        // Isi surat per baris
        $lines = $isi !== '' ? explode("\n", $isi) : ['[Tuliskan isi surat di sini...]'];
        foreach ($lines as $line) {
            $paragraphsXml .= $this->buildParagraphXml($line, false);
        }

        // Ganti paragraf kosong bawaan sebelum <w:sectPr dengan paragraf isi surat
        if (str_contains($docXml, '<w:sectPr')) {
            $docXml = preg_replace(
                '/<w:body>.*?(<w:sectPr\b.*<\/w:sectPr>)<\/w:body>/s',
                '<w:body>' . $paragraphsXml . '$1</w:body>',
                $docXml,
                1
            );
        } else {
            $docXml = str_replace('</w:body>', $paragraphsXml . '</w:body>', $docXml);
        }

        $zip->addFromString('word/document.xml', $docXml);
        $zip->close();

        return $tempPath;
    }

    /**
     * Buat dokumen .docx langsung dari model Surat.
     */
    public function generateFromSurat(Surat $surat): string
    {
        $surat->loadMissing('template');

        $templatePath = null;
        if ($surat->template?->path_file_template) {
            if (str_starts_with($surat->template->path_file_template, 'public/')) {
                $candidate = public_path(substr($surat->template->path_file_template, 7));
                if (is_file($candidate)) {
                    $templatePath = $candidate;
                }
            }
        }

        return $this->generateDocx([
            'nomor_surat' => $surat->nomor_surat,
            'tanggal_surat' => $surat->tanggal_surat?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y'),
            'sifat_surat' => $surat->sifat_surat,
            'perihal' => $surat->perihal,
            'isi_surat' => $surat->isi_surat,
            'template_path' => $templatePath,
        ]);
    }

    /**
     * Ekstrak teks dan atribut surat dari berkas .docx yang diunggah pengguna.
     *
     * @return array{
     *   nomor_surat: ?string,
     *   sifat_surat: ?string,
     *   perihal: ?string,
     *   isi_surat: string
     * }
     */
    public function extractFromDocx(UploadedFile|string $file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        if (! $path || ! is_file($path)) {
            throw new RuntimeException('Berkas Word (.docx) tidak ditemukan.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Berkas tidak dapat dibuka sebagai dokumen Microsoft Word (.docx).');
        }

        $docXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($docXml === false) {
            throw new RuntimeException('Berkas .docx tidak memiliki dokumen teks utama (word/document.xml).');
        }

        $dom = new DOMDocument();
        @$dom->loadXML($docXml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphs = [];
        foreach ($xpath->query('//w:body/w:p') as $pNode) {
            $pText = '';
            foreach ($xpath->query('.//w:t | .//w:tab | .//w:br', $pNode) as $child) {
                if ($child->localName === 't') {
                    $pText .= $child->textContent;
                } elseif ($child->localName === 'tab') {
                    $pText .= "\t";
                } elseif ($child->localName === 'br') {
                    $pText .= "\n";
                }
            }
            $paragraphs[] = rtrim($pText, " \t\r\n");
        }

        $nomor = null;
        $sifat = null;
        $perihal = null;
        $bodyLines = [];
        $inHeaderBlock = true;

        foreach ($paragraphs as $idx => $line) {
            $trimmed = trim($line);

            if ($inHeaderBlock && $idx <= 6) {
                if (preg_match('/^Nomor\s*:\s*(.+)$/iu', $trimmed, $m)) {
                    $val = trim($m[1]);
                    if ($val !== '' && ! str_contains($val, '[Otomatis')) {
                        $nomor = $val;
                    }
                    continue;
                }
                if (preg_match('/^Tanggal\s*:\s*(.+)$/iu', $trimmed)) {
                    continue;
                }
                if (preg_match('/^Sifat\s*:\s*(Biasa|Urgent)$/iu', $trimmed, $m)) {
                    $sifat = ucfirst(strtolower(trim($m[1])));
                    continue;
                }
                if (preg_match('/^Perihal\s*:\s*(.+)$/iu', $trimmed, $m)) {
                    $val = trim($m[1]);
                    if ($val !== '' && $val !== '-') {
                        $perihal = $val;
                    }
                    continue;
                }
                if ($trimmed === '' && ($nomor !== null || $sifat !== null || $perihal !== null)) {
                    $inHeaderBlock = false;
                    continue;
                }
            }

            $inHeaderBlock = false;
            $bodyLines[] = $line;
        }

        $isiSurat = trim(implode("\n", $bodyLines));

        return [
            'nomor_surat' => $nomor,
            'sifat_surat' => $sifat,
            'perihal' => $perihal,
            'isi_surat' => $isiSurat,
        ];
    }

    /**
     * Bangun elemen XML <w:p> dengan font Times New Roman 12pt.
     */
    protected function buildParagraphXml(string $line, bool $bold = false): string
    {
        $parts = explode("\t", $line);
        $runsXml = '';

        foreach ($parts as $index => $part) {
            if ($index > 0) {
                $runsXml .= '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:tab/></w:r>';
            }
            if ($part !== '') {
                $escaped = htmlspecialchars($part, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $boldTag = $bold ? '<w:b/>' : '';
                $runsXml .= '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>' . $boldTag . '<w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">' . $escaped . '</w:t></w:r>';
            }
        }

        return '<w:p><w:pPr><w:spacing w:after="80" w:line="276" w:lineRule="auto"/></w:pPr>' . $runsXml . '</w:p>';
    }
}
