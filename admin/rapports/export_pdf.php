<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/MiniPDF.php';
requireRole('admin');

$pdo = getPDO();

$allowed = ['joueurs', 'matchs', 'statistiques', 'performances'];
$type    = in_array($_GET['type'] ?? '', $allowed, true) ? $_GET['type'] : 'joueurs';

$labels = [
    'joueurs'      => 'Rapport Joueurs',
    'matchs'       => 'Rapport Matchs',
    'statistiques' => 'Rapport Statistiques',
    'performances' => 'Rapport Performances',
];

$data = [];
switch ($type) {
    case 'joueurs':
        $data = $pdo->query("
            SELECT j.*,
                COALESCE(SUM(s.buts),0) AS total_buts,
                COALESCE(SUM(s.passes_decisives),0) AS total_passes,
                COALESCE(SUM(s.minutes_jouees),0) AS total_minutes,
                COUNT(DISTINCT s.match_id) AS matchs_joues
            FROM joueurs j
            LEFT JOIN statistiques s ON s.joueur_id=j.id
            GROUP BY j.id ORDER BY j.poste, j.nom
        ")->fetchAll();
        break;
    case 'matchs':
        $data = $pdo->query("
            SELECT m.*, c.nom AS competition
            FROM matchs m
            LEFT JOIN competitions c ON c.id=m.competition_id
            ORDER BY m.date_match DESC
        ")->fetchAll();
        break;
    case 'statistiques':
        $data = $pdo->query("
            SELECT j.nom, j.prenom, j.poste, j.numero_maillot,
                COALESCE(SUM(s.buts),0)             AS buts,
                COALESCE(SUM(s.passes_decisives),0) AS passes,
                COALESCE(SUM(s.cartons_jaunes),0)   AS cj,
                COALESCE(SUM(s.cartons_rouges),0)   AS cr,
                COALESCE(SUM(s.minutes_jouees),0)   AS minutes,
                COUNT(DISTINCT s.match_id)           AS matchs,
                ROUND(AVG(s.note_match),1)          AS note_moy
            FROM joueurs j
            LEFT JOIN statistiques s ON s.joueur_id=j.id
            GROUP BY j.id ORDER BY buts DESC, passes DESC
        ")->fetchAll();
        break;
    case 'performances':
        $data = $pdo->query("
            SELECT m.date_match, m.adversaire, m.score_equipe, m.score_adverse,
                c.nom AS competition,
                COUNT(cv.id) AS nb_convoques,
                COALESCE(SUM(s.buts),0) AS buts_match
            FROM matchs m
            LEFT JOIN competitions c ON c.id=m.competition_id
            LEFT JOIN convocations cv ON cv.match_id=m.id
            LEFT JOIN statistiques s ON s.match_id=m.id
            WHERE m.statut='Terminé'
            GROUP BY m.id ORDER BY m.date_match DESC
        ")->fetchAll();
        break;
}

// ── Définition des colonnes par rapport ─────────────────────────────────────
// [width_pt, label_entête, align (0=gauche,1=centre,2=droite)]
$cols = match($type) {
    'joueurs' => [
        [185, 'Joueur',     0],
        [60,  'Poste',      0],
        [30,  'N°',         1],
        [55,  'Statut',     0],
        [45,  'Matchs',     1],
        [40,  'Buts',       1],
        [45,  'Passes D.',  1],
        [65,  'Minutes',    1],
    ],
    'matchs' => [
        [65,  'Date',        1],
        [145, 'Adversaire',  0],
        [65,  'Lieu',        0],
        [90,  'Compétition', 0],
        [50,  'Score',       1],
        [60,  'Résultat',    0],
        [50,  'Statut',      0],
    ],
    'statistiques' => [
        [195, 'Joueur',   0],
        [55,  'Poste',    0],
        [38,  'Matchs',   1],
        [38,  'Buts',     1],
        [40,  'Passes D.',1],
        [35,  'CJ',       1],
        [35,  'CR',       1],
        [45,  'Min.',     1],
        [44,  'Note moy.',1],
    ],
    'performances' => [
        [65,  'Date',         1],
        [145, 'Adversaire',   0],
        [95,  'Compétition',  0],
        [50,  'Score',        1],
        [65,  'Résultat',     0],
        [55,  'Buts',         1],
        [50,  'Convoqués',    1],
    ],
};

// ── Helpers de rendu ─────────────────────────────────────────────────────────
$ROW_H   = 14.0;  // hauteur ligne de données
$HEAD_H  = 15.0;  // hauteur ligne d'en-tête de tableau
$pageNum = 0;

function pdfPageHeader(MiniPDF $pdf, string $label): void {
    $x = MiniPDF::ML;
    $w = $pdf->usableWidth();

    $pdf->setFillColor(27, 67, 50);
    $pdf->fillRect($x, MiniPDF::MT, $w, 26);

    $pdf->font(12, true);
    $pdf->setTextColor(255, 255, 255);
    $pdf->text($x + 6, MiniPDF::MT + 5, 'AS Etoile de Dakar');

    $pdf->font(9, false);
    $pdf->text($x + 6, MiniPDF::MT + 16, $label . ' - Genere le ' . date('d/m/Y') . ' a ' . date('H:i'));

    $pdf->setTextColor(0, 0, 0);
    $pdf->setY(MiniPDF::MT + 26 + 6);
}

function pdfPageFooter(MiniPDF $pdf, int $pageNum): void {
    $x  = MiniPDF::ML;
    $w  = $pdf->usableWidth();
    $fy = 842.0 - MiniPDF::MB + 6;

    $pdf->hline($x, $fy - 4, $w, 0.5);
    $pdf->font(7, false);
    $pdf->setTextColor(120, 120, 120);
    $pdf->text($x, $fy + 3, 'AS Etoile de Dakar - Rapport confidentiel');
    $pdf->text($x + $w - 45, $fy + 3, 'Page ' . $pageNum);
    $pdf->setTextColor(0, 0, 0);
}

function pdfTableHeader(MiniPDF $pdf, array $cols, float $rowH): void {
    $x = MiniPDF::ML;
    $y = $pdf->getY();
    $pdf->setFillColor(45, 106, 79);
    $pdf->setTextColor(255, 255, 255);
    $pdf->font(8, true);
    foreach ($cols as [$w, $label]) {
        $pdf->cell($x, $y, $w, $rowH, $label, true, 1);
        $x += $w;
    }
    $pdf->setTextColor(0, 0, 0);
    $pdf->setY($y + $rowH);
}

function pdfDataRow(MiniPDF $pdf, array $cols, array $values, bool $even, float $rowH): void {
    $x = MiniPDF::ML;
    $y = $pdf->getY();

    // Fond alterné
    if (!$even) {
        $pdf->setFillColor(245, 247, 245);
        $tw = array_sum(array_column($cols, 0));
        $pdf->fillRect(MiniPDF::ML, $y, $tw, $rowH);
    }

    $pdf->font(8, false);
    $pdf->setTextColor(30, 30, 30);
    foreach ($cols as $i => [$w, , $align]) {
        $pdf->cell($x, $y, $w, $rowH, (string)($values[$i] ?? ''), false, $align);
        $x += $w;
    }
    $pdf->hline(MiniPDF::ML, $y + $rowH, array_sum(array_column($cols, 0)));
    $pdf->setTextColor(0, 0, 0);
    $pdf->setY($y + $rowH);
}

// ── Formatage des données ────────────────────────────────────────────────────
function buildRows(string $type, array $data): array {
    $rows = [];
    foreach ($data as $r) {
        $row = match($type) {
            'joueurs' => [
                ($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? ''),
                $r['poste'] ?? '',
                $r['numero_maillot'] ? '#' . $r['numero_maillot'] : '',
                $r['statut'] ?? '',
                (string)($r['matchs_joues'] ?? 0),
                (string)($r['total_buts'] ?? 0),
                (string)($r['total_passes'] ?? 0),
                ($r['total_minutes'] ?? 0) . "'",
            ],
            'matchs' => [
                $r['date_match'] ? date('d/m/Y', strtotime($r['date_match'])) : '',
                'vs ' . ($r['adversaire'] ?? ''),
                $r['domicile_exterieur'] ?? '',
                $r['competition'] ?? '',
                $r['statut'] === 'Termine' || $r['statut'] === 'Terminé'
                    ? ($r['score_equipe'] . '-' . $r['score_adverse']) : '',
                $r['statut'] === 'Termine' || $r['statut'] === 'Terminé'
                    ? ($r['score_equipe'] > $r['score_adverse'] ? 'Victoire'
                     : ($r['score_equipe'] < $r['score_adverse'] ? 'Defaite' : 'Nul')) : '',
                $r['statut'] ?? '',
            ],
            'statistiques' => [
                ($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? ''),
                $r['poste'] ?? '',
                (string)($r['matchs'] ?? 0),
                (string)($r['buts'] ?? 0),
                (string)($r['passes'] ?? 0),
                (string)($r['cj'] ?? 0),
                (string)($r['cr'] ?? 0),
                ($r['minutes'] ?? 0) . "'",
                $r['note_moy'] ?? '',
            ],
            'performances' => [
                $r['date_match'] ? date('d/m/Y', strtotime($r['date_match'])) : '',
                'vs ' . ($r['adversaire'] ?? ''),
                $r['competition'] ?? '',
                ($r['score_equipe'] ?? '') . '-' . ($r['score_adverse'] ?? ''),
                $r['score_equipe'] > $r['score_adverse'] ? 'Victoire'
                    : ($r['score_equipe'] < $r['score_adverse'] ? 'Defaite' : 'Nul'),
                (string)($r['buts_match'] ?? 0),
                (string)($r['nb_convoques'] ?? 0),
            ],
        };
        $rows[] = $row;
    }
    return $rows;
}

// ── Génération du PDF ────────────────────────────────────────────────────────
$pdf     = new MiniPDF();
$rows    = buildRows($type, $data);
$label   = $labels[$type];
$pageNum = 0;

$pdf->addPage();
$pageNum++;
pdfPageHeader($pdf, $label);
pdfTableHeader($pdf, $cols, $HEAD_H);

$even = true;
foreach ($rows as $row) {
    if ($pdf->needsNewPage($ROW_H)) {
        pdfPageFooter($pdf, $pageNum);
        $pdf->addPage();
        $pageNum++;
        pdfPageHeader($pdf, $label);
        pdfTableHeader($pdf, $cols, $HEAD_H);
        $even = true;
    }
    pdfDataRow($pdf, $cols, $row, $even, $ROW_H);
    $even = !$even;
}

if (empty($rows)) {
    $pdf->font(9, false);
    $pdf->setTextColor(120, 120, 120);
    $tw = array_sum(array_column($cols, 0));
    $pdf->cell(MiniPDF::ML, $pdf->getY(), $tw, $ROW_H, 'Aucune donnée disponible.', false, 1);
}

pdfPageFooter($pdf, $pageNum);

// ── Envoi au navigateur ───────────────────────────────────────────────────────
$filename = 'rapport_' . $type . '_' . date('Ymd_Hi') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
echo $pdf->output();
