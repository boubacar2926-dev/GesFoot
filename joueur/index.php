<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('joueur');
$pdo  = getPDO();
$user = currentUser();

// Trouver la fiche joueur liée à ce compte
$joueurStmt = $pdo->prepare("SELECT * FROM joueurs WHERE utilisateur_id=? LIMIT 1");
$joueurStmt->execute([$user['id']]);
$joueur = $joueurStmt->fetch();

// Statistiques personnelles cumulées
$stats = [];
if ($joueur) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(buts),0) AS buts, COALESCE(SUM(passes_decisives),0) AS passes,
               COALESCE(SUM(minutes_jouees),0) AS minutes, COALESCE(SUM(cartons_jaunes),0) AS cj,
               COALESCE(SUM(cartons_rouges),0) AS cr, COUNT(DISTINCT match_id) AS matchs,
               ROUND(AVG(note_match),1) AS note_moy
        FROM statistiques WHERE joueur_id=?
    ");
    $stmt->execute([$joueur['id']]);
    $stats = $stmt->fetch();
}

// Prochains matchs pour lesquels il est convoqué
$prochains = [];
if ($joueur) {
    $stmt = $pdo->prepare("
        SELECT m.*, c.nom AS competition, cv.lieu_rdv, cv.heure_rdv
        FROM convocation_joueur cj
        JOIN convocations cv ON cv.id=cj.convocation_id
        JOIN matchs m ON m.id=cv.match_id
        LEFT JOIN competitions c ON c.id=m.competition_id
        WHERE cj.joueur_id=? AND m.date_match >= CURDATE() AND m.statut='Programmé'
        ORDER BY m.date_match ASC LIMIT 5
    ");
    $stmt->execute([$joueur['id']]);
    $prochains = $stmt->fetchAll();
}

// Matchs à venir avec convocation publiée où le joueur n'est PAS dans la liste
$nonConvoques = [];
if ($joueur) {
    $stmt = $pdo->prepare("
        SELECT m.*, c.nom AS competition
        FROM matchs m
        LEFT JOIN competitions c ON c.id = m.competition_id
        JOIN convocations cv ON cv.match_id = m.id AND cv.statut = 'Publiée'
        WHERE m.date_match >= CURDATE() AND m.statut = 'Programmé'
          AND NOT EXISTS (
              SELECT 1 FROM convocation_joueur cj
              WHERE cj.convocation_id = cv.id AND cj.joueur_id = ?
          )
        ORDER BY m.date_match ASC
    ");
    $stmt->execute([$joueur['id']]);
    $nonConvoques = $stmt->fetchAll();
}

// Derniers matchs joués
$derniers = [];
if ($joueur) {
    $stmt = $pdo->prepare("
        SELECT m.date_match, m.adversaire, m.score_equipe, m.score_adverse,
               s.buts, s.passes_decisives, s.minutes_jouees, s.cartons_jaunes, s.cartons_rouges, s.note_match
        FROM statistiques s
        JOIN matchs m ON m.id=s.match_id
        WHERE s.joueur_id=? ORDER BY m.date_match DESC LIMIT 5
    ");
    $stmt->execute([$joueur['id']]);
    $derniers = $stmt->fetchAll();
}

$pageTitle = 'Mon tableau de bord';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($joueur): ?>
<!-- Profil rapide -->
<div class="card mb-4" style="background:#1b4332;color:#fff;border:none">
    <div class="card-body d-flex align-items-center gap-4 flex-wrap">
        <img src="<?= avatarUrl($joueur['photo'], $joueur['prenom'].' '.$joueur['nom']) ?>"
             style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.3)">
        <div>
            <h4 class="fw-bold mb-1 text-white"><?= e($joueur['prenom']) ?> <?= e($joueur['nom']) ?></h4>
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge bg-light text-dark"><?= e($joueur['poste']) ?></span>
                <?php if ($joueur['numero_maillot']): ?><span class="badge bg-white text-dark">#<?= $joueur['numero_maillot'] ?></span><?php endif; ?>
                <?= statusBadge($joueur['statut']) ?>
                <?php if ($joueur['nationalite']): ?><span class="badge bg-light text-dark"><i class="bi bi-flag me-1"></i><?= e($joueur['nationalite']) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="ms-auto text-end">
            <a href="<?= BASE_URL ?>/joueur/profil.php" class="btn btn-light btn-sm">
                <i class="bi bi-person me-1"></i>Mon profil
            </a>
        </div>
    </div>
</div>

<!-- Stats résumées -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['bi-calendar-check','Matchs joués',   $stats['matchs']  ?? 0, '#f1f5f9','#475569'],
        ['bi-bullseye',      'Buts',            $stats['buts']    ?? 0, '#f0fdf4','#166534'],
        ['bi-send',          'Passes D.',       $stats['passes']  ?? 0, '#f0fdf4','#166534'],
        ['bi-clock',         'Minutes jouées',  ($stats['minutes'] ?? 0)."'", '#f1f5f9','#475569'],
        ['bi-card-text',     'Cartons J.',      $stats['cj']      ?? 0, '#fef9c3','#854d0e'],
        ['bi-star-fill',     'Note moyenne',    $stats['note_moy'] ? $stats['note_moy'].'/10' : '—', '#f1f5f9','#475569'],
    ];
    foreach ($cards as [$icon,$label,$val,$bg,$color]):
    ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
            <div><div class="stat-value" style="font-size:1.4rem"><?= $val ?></div><div class="stat-label"><?= $label ?></div></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($nonConvoques): ?>
<div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-bell-fill fs-4 mt-1"></i>
    <div>
        <div class="fw-bold mb-1">Vous n'êtes pas convoqué pour <?= count($nonConvoques) ?> match(s) à venir :</div>
        <ul class="mb-0 small">
            <?php foreach ($nonConvoques as $nc): ?>
            <li>
                vs <?= e($nc['adversaire']) ?> — <?= formatDate($nc['date_match']) ?>
                <?= $nc['competition'] ? ' · ' . e($nc['competition']) : '' ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Prochains matchs -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clock text-warning me-2"></i>Mes prochains matchs</div>
            <div class="card-body p-0">
                <?php if ($prochains): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($prochains as $m): ?>
                    <li class="list-group-item px-3 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold">vs <?= e($m['adversaire']) ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i><?= formatDate($m['date_match']) ?>
                                    <?= $m['heure_match'] ? ' à '.formatTime($m['heure_match']) : '' ?>
                                    <?= $m['competition'] ? ' · '.e($m['competition']) : '' ?>
                                </small>
                                <?php if ($m['lieu_rdv'] || $m['heure_rdv']): ?>
                                <div class="mt-1 small text-info">
                                    <i class="bi bi-pin-map me-1"></i>
                                    RDV : <?= $m['lieu_rdv'] ? e($m['lieu_rdv']) : '' ?>
                                    <?= $m['heure_rdv'] ? ' à '.formatTime($m['heure_rdv']) : '' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-success ms-2">Convoqué</span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <div class="text-center py-5 text-muted"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun match à venir</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Dernières performances -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-success"></i>Mes dernières performances</div>
            <div class="card-body p-0 table-responsive">
                <?php if ($derniers): ?>
                <table class="table mb-0">
                    <thead><tr><th>Match</th><th class="text-center">Min</th><th class="text-center">Buts</th><th class="text-center">Note</th></tr></thead>
                    <tbody>
                    <?php foreach ($derniers as $d):
                        $res = '';
                        if ($d['score_equipe'] > $d['score_adverse'])      $res = 'text-success';
                        elseif ($d['score_equipe'] < $d['score_adverse'])  $res = 'text-danger';
                        else                                                $res = 'text-warning';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold small">vs <?= e($d['adversaire']) ?></div>
                            <div class="<?= $res ?> small fw-bold"><?= $d['score_equipe'] ?>—<?= $d['score_adverse'] ?></div>
                            <small class="text-muted"><?= formatDate($d['date_match']) ?></small>
                        </td>
                        <td class="text-center"><?= $d['minutes_jouees'] ?>'</td>
                        <td class="text-center fw-bold text-success"><?= $d['buts'] ?: '—' ?></td>
                        <td class="text-center">
                            <?php if ($d['note_match']): ?>
                            <span class="badge <?= $d['note_match']>=7?'bg-success':($d['note_match']>=5?'bg-warning text-dark':'bg-danger') ?>">
                                <?= $d['note_match'] ?>
                            </span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="text-center py-5 text-muted"><i class="bi bi-bar-chart fs-2 d-block mb-2"></i>Aucune statistique disponible</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="alert alert-warning d-flex align-items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
    <div>
        Votre compte n'est pas encore lié à une fiche joueur.
        Contactez l'administrateur pour associer votre profil.
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
