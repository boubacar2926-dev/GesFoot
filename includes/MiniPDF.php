<?php
/**
 * MiniPDF — générateur PDF minimaliste sans dépendance externe.
 * Police Helvetica Type1 (embarquée dans tout lecteur PDF standard).
 * Coordonnées en points (pt), Y depuis le haut de la page.
 */
class MiniPDF {
    private float $pw = 595.0;  // A4 largeur en pt
    private float $ph = 842.0;  // A4 hauteur en pt

    private array  $pages = [];  // streams des pages finalisées
    private string $cur   = '';  // stream page courante
    private float  $y     = 0.0; // Y courant (depuis le haut, en pt)
    private float  $fsize = 10.0;
    private bool   $fbold = false;

    // Couleurs courantes (0-255)
    private int $tr = 0,   $tg = 0,   $tb = 0;
    private int $fr = 255, $fg = 255, $fb = 255;

    public const ML = 35.0;
    public const MR = 35.0;
    public const MT = 40.0;
    public const MB = 35.0;

    public function addPage(): void {
        if ($this->cur !== '') {
            $this->pages[] = $this->cur;
        }
        $this->cur = '';
        $this->y   = self::MT;
    }

    public function font(float $size, bool $bold = false): void {
        $this->fsize = $size;
        $this->fbold = $bold;
    }

    public function setTextColor(int $r, int $g, int $b): void {
        $this->tr = $r; $this->tg = $g; $this->tb = $b;
    }

    public function setFillColor(int $r, int $g, int $b): void {
        $this->fr = $r; $this->fg = $g; $this->fb = $b;
    }

    public function getY(): float { return $this->y; }
    public function setY(float $y): void { $this->y = $y; }
    public function usableWidth(): float { return $this->pw - self::ML - self::MR; }
    public function ln(float $h = 12.0): void { $this->y += $h; }

    public function needsNewPage(float $h): bool {
        return ($this->y + $h) > ($this->ph - self::MB);
    }

    // UTF-8 → CP1252 (WinAnsiEncoding) + échappement PDF
    private function enc(string $s): string {
        $s = mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    // Y dans le référentiel PDF (origine en bas)
    private function py(float $y): float {
        return $this->ph - $y;
    }

    public function text(float $x, float $y, string $txt): void {
        if ($txt === '') return;
        $fn       = $this->fbold ? 'F2' : 'F1';
        $tr       = round($this->tr / 255, 4);
        $tg       = round($this->tg / 255, 4);
        $tb       = round($this->tb / 255, 4);
        $baseline = $this->py($y + $this->fsize * 0.75);
        $this->cur .= sprintf(
            "%.4f %.4f %.4f rg BT /%s %.1f Tf %.2f %.2f Td (%s) Tj ET 0 0 0 rg\n",
            $tr, $tg, $tb, $fn, $this->fsize, $x, $baseline, $this->enc($txt)
        );
    }

    public function fillRect(float $x, float $y, float $w, float $h): void {
        $this->cur .= sprintf(
            "%.4f %.4f %.4f rg %.2f %.2f %.2f %.2f re f 0 0 0 rg\n",
            $this->fr / 255, $this->fg / 255, $this->fb / 255,
            $x, $this->py($y + $h), $w, $h
        );
    }

    public function hline(float $x, float $y, float $w, float $lw = 0.3): void {
        $py = $this->py($y);
        $this->cur .= sprintf(
            "q 0.75 0.75 0.75 RG %.2f w %.2f %.2f m %.2f %.2f l S Q\n",
            $lw, $x, $py, $x + $w, $py
        );
    }

    // Cellule : fond + texte tronqué + alignement (0=gauche, 1=centre, 2=droite)
    public function cell(
        float $x, float $y, float $w, float $h,
        string $txt,
        bool $fill = false,
        int $align = 0
    ): void {
        if ($fill) {
            $this->fillRect($x, $y, $w, $h);
        }
        if ($txt === '') return;

        $pad   = 4.0;
        $charW = $this->fsize * 0.55;
        $maxC  = max(1, (int)(($w - 2 * $pad) / $charW));
        $label = mb_strlen($txt) > $maxC ? mb_substr($txt, 0, $maxC - 1) . '.' : $txt;

        $textW = mb_strlen($label) * $charW;
        $tx = match($align) {
            1 => $x + ($w - $textW) / 2,
            2 => $x + $w - $pad - $textW,
            default => $x + $pad,
        };
        $ty = $y + ($h - $this->fsize) / 2;
        $this->text($tx, $ty, $label);
    }

    public function output(): string {
        if ($this->cur !== '') {
            $this->pages[] = $this->cur;
            $this->cur = '';
        }
        $n = count($this->pages);
        if ($n === 0) return '';

        $out  = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offs = [];

        $offs[1] = strlen($out);
        $out .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        $kids = [];
        for ($i = 0; $i < $n; $i++) $kids[] = (5 + $i * 2) . ' 0 R';
        $offs[2] = strlen($out);
        $out .= "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count $n >>\nendobj\n";

        $offs[3] = strlen($out);
        $out .= "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        $offs[4] = strlen($out);
        $out .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n";

        for ($i = 0; $i < $n; $i++) {
            $di = 5 + $i * 2;
            $si = 6 + $i * 2;
            $content = $this->pages[$i];

            $offs[$di] = strlen($out);
            $out .= "$di 0 obj\n<< /Type /Page /Parent 2 0 R "
                  . "/MediaBox [0 0 {$this->pw} {$this->ph}] "
                  . "/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> "
                  . "/Contents $si 0 R >>\nendobj\n";

            $offs[$si] = strlen($out);
            $len = strlen($content);
            $out .= "$si 0 obj\n<< /Length $len >>\nstream\n{$content}endstream\nendobj\n";
        }

        // XRef — chaque entrée = exactement 20 octets
        $xref  = strlen($out);
        $total = 4 + $n * 2;
        $out .= "xref\n0 " . ($total + 1) . "\n";
        $out .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $total; $i++) {
            $out .= str_pad((string)($offs[$i] ?? 0), 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $out .= "trailer\n<< /Size " . ($total + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";

        return $out;
    }
}
