<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('coach');
$pdo = getPDO();

$matchId = (int)($_GET['match_id'] ?? 0);
$matchs  = $pdo->query("SELECT m.*, c.nom AS competition FROM matchs m LEFT JOIN competitions c ON c.id=m.competition_id ORDER BY m.date_match DESC")->fetchAll();

$composition = [];
if ($matchId) {
    $stmt = $pdo->prepare("
        SELECT co.*, j.nom, j.prenom, j.poste, j.numero_maillot, j.photo
        FROM compositions co JOIN joueurs j ON j.id=co.joueur_id
        WHERE co.match_id=? ORDER BY co.type_joueur, j.poste, j.nom
    ");
    $stmt->execute([$matchId]);
    $composition = $stmt->fetchAll();
}

$joueursDispos = [];
if ($matchId) {
    $joueursDispos = $pdo->query("SELECT * FROM joueurs WHERE statut='actif' ORDER BY poste, nom")->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $mid = (int)($_POST['match_id'] ?? 0);
    if ($mid <= 0) { setFlash('error', 'Match invalide.'); redirect('/coach/compositions/index.php'); }
    $validTypes = ['Titulaire', 'Remplaçant'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM compositions WHERE match_id=?")->execute([$mid]);
        $ins = $pdo->prepare("INSERT INTO compositions (match_id, joueur_id, type_joueur, poste_match) VALUES (?,?,?,?)");
        foreach ($_POST['joueurs'] ?? [] as $jid => $data) {
            $type = $data['type'] ?? '';
            if (!in_array($type, $validTypes)) continue;
            $ins->execute([$mid, (int)$jid, $type, $data['poste'] ?: null]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Erreur lors de l\'enregistrement. Veuillez réessayer.');
        redirect('/coach/compositions/index.php?match_id=' . $mid);
    }
    setFlash('success','Composition enregistrée.');
    redirect('/coach/compositions/index.php?match_id='.$mid);
}

$pageTitle = 'Compositions';
require_once ROOT_PATH . '/coach/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Composition d'équipe</h4>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <select name="match_id" class="form-select" onchange="this.form.submit()">
                    <option value="">— Choisir un match —</option>
                    <?php foreach ($matchs as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $matchId===$m['id']?'selected':'' ?>>
                            <?= formatDate($m['date_match']) ?> — vs <?= e($m['adversaire']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($matchId && $joueursDispos): ?>
<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="match_id" value="<?= $matchId ?>">

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="bi bi-layout-text-sidebar me-2"></i>Composition de l'équipe</span>
            <small class="text-muted fw-semibold" id="compteur">Titulaires: 0 | Remplaçants: 0</small>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr>
                    <th style="width:45px" class="text-center">#</th>
                    <th>Joueur</th>
                    <th style="width:140px">Poste match</th>
                    <th style="width:170px">Rôle</th>
                </tr></thead>
                <tbody>
                <?php foreach ($joueursDispos as $j):
                    $inComp = null;
                    foreach ($composition as $c) { if ($c['joueur_id'] == $j['id']) { $inComp = $c; break; } }
                    $selectedType = $inComp ? $inComp['type_joueur'] : '';
                ?>
                <tr>
                    <td class="text-center text-muted small"><?= $j['numero_maillot'] ? '#'.$j['numero_maillot'] : '—' ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar-sm">
                            <div>
                                <div class="fw-semibold small"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></div>
                                <small class="text-muted"><?= e($j['poste']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="joueurs[<?= $j['id'] ?>][poste]"
                               class="form-control form-control-sm"
                               placeholder="<?= e($j['poste']) ?>"
                               value="<?= e($inComp['poste_match'] ?? '') ?>">
                    </td>
                    <td>
                        <select name="joueurs[<?= $j['id'] ?>][type]" class="form-select form-select-sm role-select">
                            <option value="">— Non sélectionné —</option>
                            <option value="Titulaire" <?= $selectedType==='Titulaire'?'selected':'' ?>>Titulaire</option>
                            <option value="Remplaçant" <?= $selectedType==='Remplaçant'?'selected':'' ?>>Remplaçant</option>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i> Sauvegarder la composition</button>
    </div>
</form>

<script>
(function () {
    function updateCompteur() {
        var t = 0, r = 0;
        document.querySelectorAll('.role-select').forEach(function (s) {
            if (s.value === 'Titulaire') t++;
            else if (s.value === 'Remplaçant') r++;
        });
        document.getElementById('compteur').textContent = 'Titulaires : ' + t + ' | Remplaçants : ' + r;
    }
    document.querySelectorAll('.role-select').forEach(function (s) {
        s.addEventListener('change', updateCompteur);
    });
    updateCompteur();
}());
</script>

<?php elseif ($matchId): ?>
    <div class="alert alert-info">Aucun joueur actif disponible.</div>
<?php else: ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-layout-text-sidebar fs-1 d-block mb-3"></i>Sélectionnez un match ci-dessus.
    </div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/coach/includes/footer.php'; ?>
