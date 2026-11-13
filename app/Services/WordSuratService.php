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

        $tempPath = tempnam(sys_get_temp_dir(), 'wkmail_word_').'.docx';
        if (! copy($sourceDocx, $tempPath)) {
            throw new RuntimeException('Gagal menyiapkan berkas sementara Word.');
        }

        $zip = new ZipArchive;
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
        $isi = $this->bodyWithoutMetadata((string) ($data['isi_surat'] ?? ''));

        $paragraphsXml = '';

        // Blok atribut surat di bagian atas dokumen
        $metaLines = [
            ['Nomor', $nomor],
            ['Tanggal', $tanggal],
            ['Sifat', $sifat],
            ['Perihal', $perihal],
        ];

        foreach ($metaLines as [$label, $value]) {
            $paragraphsXml .= $this->buildMetadataParagraphXml($label, $value);
        }

        // Baris kosong pemisah antara atribut & isi surat
        $paragraphsXml .= $this->buildParagraphXml('', false);

        $isiTerpisah = $this->splitSignatureBlock($isi);
        $lines = $isiTerpisah['body'] !== '' ? explode("\n", $isiTerpisah['body']) : ['[Tuliskan isi surat di sini...]'];
        foreach ($lines as $line) {
            $paragraphsXml .= $this->buildParagraphXml($line, false);
        }

        foreach ($isiTerpisah['closing'] as $index => $line) {
            $paragraphsXml .= $this->buildSignatureParagraphXml($line, $index === 0 ? 480 : 0, true);
        }

        foreach ($isiTerpisah['signer'] as $index => $line) {
            $lastSignerLine = $index === array_key_last($isiTerpisah['signer']);
            $paragraphsXml .= $this->buildSignatureParagraphXml($line, $index === 0 ? 1000 : 0, ! $lastSignerLine);
        }

        // Ganti paragraf kosong bawaan sebelum <w:sectPr dengan paragraf isi surat
        if (str_contains($docXml, '<w:sectPr')) {
            $docXml = preg_replace(
                '/<w:body>.*?(<w:sectPr\b.*<\/w:sectPr>)<\/w:body>/s',
                '<w:body>'.$paragraphsXml.'$1</w:body>',
                $docXml,
                1
            );
        } else {
            $docXml = str_replace('</w:body>', $paragraphsXml.'</w:body>', $docXml);
        }

        $docXml = preg_replace(
            '/<w:pgSz\b[^>]*\/>/',
            '<w:pgSz w:w="11906" w:h="16838"/>',
            $docXml,
            1
        ) ?? $docXml;

        $zip->addFromString('word/document.xml', $docXml);
        $zip->close();

        return $tempPath;
    }

    public function bodyWithoutMetadata(string $content): string
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", trim($content)));
        $metadataFound = false;

        while ($lines !== []) {
            $line = trim($lines[0]);
            if ($metadataFound && $line === '') {
                array_shift($lines);

                continue;
            }

            if (preg_match('/^(Nomor|Tanggal|Sifat|Perihal)\s*:/iu', $line)) {
                array_shift($lines);
                $metadataFound = true;

                continue;
            }

            break;
        }

        return trim(implode("\n", $lines));
    }

    /**
     * Separate the conventional closing and signature from the letter body.
     *
     * @return array{body: string, closing: list<string>, signer: list<string>}
     */
    public function splitSignatureBlock(string $content): array
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", trim($content)));
        $closingIndex = null;

        foreach ($lines as $index => $line) {
            if (preg_match('/^(Scientia\s+ad\s+Laborem|Hormat\s+kami)\s*,?$/iu', trim($line))) {
                $closingIndex = $index;
                break;
            }
        }

        if ($closingIndex === null) {
            return ['body' => trim($content), 'closing' => [], 'signer' => []];
        }

        $closing = [];
        $index = $closingIndex;
        while (isset($lines[$index])) {
            $line = trim($lines[$index]);
            if ($line === '' && $closing !== []) {
                $index++;

                continue;
            }

            if (! preg_match('/^(Scientia\s+ad\s+Laborem|Hormat\s+kami)\s*,?$/iu', $line)) {
                break;
            }

            $closing[] = $line;
            $index++;
        }

        $signer = array_values(array_filter(
            array_map('trim', array_slice($lines, $index)),
            static fn (string $line): bool => $line !== ''
        ));

        return [
            'body' => trim(implode("\n", array_slice($lines, 0, $closingIndex))),
            'closing' => $closing,
            'signer' => $signer,
        ];
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

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Berkas tidak dapat dibuka sebagai dokumen Microsoft Word (.docx).');
        }

        $docXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($docXml === false) {
            throw new RuntimeException('Berkas .docx tidak memiliki dokumen teks utama (word/document.xml).');
        }

        $dom = new DOMDocument;
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
                $runsXml .= '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:eastAsia="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'.$boldTag.'<w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t xml:space="preserve">'.$escaped.'</w:t></w:r>';
            }
        }

        return '<w:p><w:pPr><w:spacing w:after="0" w:line="276" w:lineRule="auto"/><w:jc w:val="both"/></w:pPr>'.$runsXml.'</w:p>';
    }

    protected function buildMetadataParagraphXml(string $label, string $value): string
    {
        $label = htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $font = '<w:rFonts w:ascii="Times New Roman" w:eastAsia="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
        $size = '<w:sz w:val="24"/><w:szCs w:val="24"/>';

        return '<w:p><w:pPr><w:tabs><w:tab w:val="left" w:pos="1440"/></w:tabs><w:spacing w:after="0" w:line="276" w:lineRule="auto"/></w:pPr>'
            .'<w:r><w:rPr>'.$font.'<w:b/>'.$size.'</w:rPr><w:t>'.$label.'</w:t></w:r>'
            .'<w:r><w:rPr>'.$font.$size.'</w:rPr><w:tab/></w:r>'
            .'<w:r><w:rPr>'.$font.$size.'</w:rPr><w:t xml:space="preserve">: '.$value.'</w:t></w:r></w:p>';
    }

    protected function buildSignatureParagraphXml(string $line, int $spaceBefore = 0, bool $keepNext = false): string
    {
        $escaped = htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $font = '<w:rFonts w:ascii="Times New Roman" w:eastAsia="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
        $size = '<w:sz w:val="24"/><w:szCs w:val="24"/>';
        $before = $spaceBefore > 0 ? 'w:before="'.$spaceBefore.'" ' : '';
        $keep = $keepNext ? '<w:keepNext/>' : '';

        return '<w:p><w:pPr>'.$keep.'<w:spacing '.$before.'w:after="0" w:line="276" w:lineRule="auto"/><w:jc w:val="right"/></w:pPr>'
            .'<w:r><w:rPr>'.$font.$size.'</w:rPr><w:t xml:space="preserve">'.$escaped.'</w:t></w:r></w:p>';
    }
}
