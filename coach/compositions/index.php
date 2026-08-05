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

$joueursDispos  = [];
$convocationId  = null;
if ($matchId) {
    $stmt = $pdo->prepare("SELECT id FROM convocations WHERE match_id=? AND statut='Publiée' LIMIT 1");
    $stmt->execute([$matchId]);
    $convoc = $stmt->fetch();
    if ($convoc) {
        $convocationId = $convoc['id'];
        $stmt = $pdo->prepare("
            SELECT j.* FROM joueurs j
            JOIN convocation_joueur cj ON cj.joueur_id = j.id
            WHERE cj.convocation_id = ? AND cj.statut = 'Convoqué'
            ORDER BY j.poste, j.nom
        ");
        $stmt->execute([$convocationId]);
        $joueursDispos = $stmt->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $mid = (int)($_POST['match_id'] ?? 0);
    if ($mid <= 0) { setFlash('error', 'Match invalide.'); redirect('/coach/compositions/index.php'); }

    $validTypes  = ['Titulaire', 'Remplaçant'];
    $titulaires  = 0;
    $remplacants = 0;
    $aJoue       = 0;
    foreach ($_POST['joueurs'] ?? [] as $data) {
        $type = $data['type'] ?? '';
        if ($type === 'Titulaire')  $titulaires++;
        if ($type === 'Remplaçant') {
            $remplacants++;
            if (!empty($data['a_joue'])) $aJoue++;
        }
    }

    $erreurs = [];
    if ($titulaires !== 11)  $erreurs[] = "Il faut exactement 11 titulaires (actuellement : $titulaires).";
    if ($remplacants !== 7)  $erreurs[] = "Il faut exactement 7 remplaçants (actuellement : $remplacants).";
    if ($aJoue > 5)          $erreurs[] = "Au plus 5 remplaçants peuvent être entrés en jeu (actuellement : $aJoue).";

    if ($erreurs) {
        setFlash('error', implode('<br>', $erreurs));
        redirect('/coach/compositions/index.php?match_id=' . $mid);
    }

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM compositions WHERE match_id=?")->execute([$mid]);
        $ins = $pdo->prepare("INSERT INTO compositions (match_id, joueur_id, type_joueur, poste_match, a_joue) VALUES (?,?,?,?,?)");
        foreach ($_POST['joueurs'] ?? [] as $jid => $data) {
            $type = $data['type'] ?? '';
            if (!in_array($type, $validTypes)) continue;
            $played = ($type === 'Remplaçant' && !empty($data['a_joue'])) ? 1 : 0;
            $ins->execute([$mid, (int)$jid, $type, $data['poste'] ?: null, $played]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', "Erreur lors de l'enregistrement. Veuillez réessayer.");
        redirect('/coach/compositions/index.php?match_id=' . $mid);
    }
    setFlash('success', 'Composition enregistrée.');
    redirect('/coach/compositions/index.php?match_id=' . $mid);
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
            <div class="d-flex gap-3">
                <small class="fw-semibold" id="cpt-tit">Titulaires : 0/11</small>
                <small class="fw-semibold" id="cpt-rem">Remplaçants : 0/7</small>
                <small class="fw-semibold" id="cpt-jou">Entrés en jeu : 0/5</small>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead class="table-light"><tr>
                    <th style="width:45px" class="text-center">#</th>
                    <th>Joueur</th>
                    <th style="width:140px">Poste match</th>
                    <th style="width:170px">Rôle</th>
                    <th style="width:120px" class="text-center">Entré en jeu</th>
                </tr></thead>
                <tbody>
                <?php foreach ($joueursDispos as $j):
                    $inComp = null;
                    foreach ($composition as $c) { if ($c['joueur_id'] == $j['id']) { $inComp = $c; break; } }
                    $selectedType = $inComp ? $inComp['type_joueur'] : '';
                    $aJoueChecked = $inComp && $inComp['a_joue'] ? true : false;
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
                            <option value="Titulaire"  <?= $selectedType==='Titulaire'  ?'selected':'' ?>>Titulaire</option>
                            <option value="Remplaçant" <?= $selectedType==='Remplaçant' ?'selected':'' ?>>Remplaçant</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="joue-wrap <?= $selectedType==='Remplaçant' ? '' : 'd-none' ?>">
                            <input type="checkbox"
                                   name="joueurs[<?= $j['id'] ?>][a_joue]"
                                   value="1"
                                   class="form-check-input joue-check"
                                   <?= $aJoueChecked ? 'checked' : '' ?>>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="alerte-comp" class="alert alert-warning d-none mt-3">
        <i class="bi bi-exclamation-triangle me-2"></i><span id="alerte-msg"></span>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="submit" id="btn-save" class="btn btn-success" disabled>
            <i class="bi bi-check2 me-1"></i> Sauvegarder la composition
        </button>
    </div>
</form>

<script>
(function () {
    function update() {
        var t = 0, r = 0, j = 0;
        document.querySelectorAll('.role-select').forEach(function (s) {
            var row = s.closest('tr');
            var wrap = row.querySelector('.joue-wrap');
            var check = row.querySelector('.joue-check');
            if (s.value === 'Titulaire') {
                t++;
                wrap.classList.add('d-none');
                check.checked = false;
            } else if (s.value === 'Remplaçant') {
                r++;
                wrap.classList.remove('d-none');
                if (check.checked) j++;
            } else {
                wrap.classList.add('d-none');
                check.checked = false;
            }
        });

        var cptTit = document.getElementById('cpt-tit');
        var cptRem = document.getElementById('cpt-rem');
        var cptJou = document.getElementById('cpt-jou');

        cptTit.textContent = 'Titulaires : ' + t + '/11';
        cptTit.className   = 'fw-semibold ' + (t === 11 ? 'text-success' : 'text-danger');
        cptRem.textContent = 'Remplaçants : ' + r + '/7';
        cptRem.className   = 'fw-semibold ' + (r === 7  ? 'text-success' : 'text-danger');
        cptJou.textContent = 'Entrés en jeu : ' + j + '/5';
        cptJou.className   = 'fw-semibold ' + (j <= 5  ? 'text-success' : 'text-danger');

        var msgs = [];
        if (t !== 11) msgs.push(t < 11 ? 'Il manque ' + (11-t) + ' titulaire(s).' : 'Trop de titulaires (' + t + '/11).');
        if (r !== 7)  msgs.push(r < 7  ? 'Il manque ' + (7-r)  + ' remplaçant(s).' : 'Trop de remplaçants (' + r + '/7).');
        if (j > 5)    msgs.push('Trop de remplaçants entrés en jeu (' + j + '/5 max).');

        var alerte = document.getElementById('alerte-comp');
        var btn    = document.getElementById('btn-save');
        if (msgs.length) {
            document.getElementById('alerte-msg').textContent = msgs.join(' ');
            alerte.classList.remove('d-none');
            btn.disabled = true;
        } else {
            alerte.classList.add('d-none');
            btn.disabled = false;
        }
    }

    document.querySelectorAll('.role-select').forEach(function (s) {
        s.addEventListener('change', update);
    });
    document.querySelectorAll('.joue-check').forEach(function (c) {
        c.addEventListener('change', update);
    });
    update();
}());
</script>

<?php elseif ($matchId && !$convocationId): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Aucune convocation publiée pour ce match. Publiez d'abord la convocation avant de définir la composition.
        <a href="<?= BASE_URL ?>/admin/convocations/index.php" class="alert-link ms-1">Gérer les convocations</a>
    </div>
<?php elseif ($matchId): ?>
    <div class="alert alert-info">Aucun joueur convoqué pour ce match.</div>
<?php else: ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-layout-text-sidebar fs-1 d-block mb-3"></i>Sélectionnez un match ci-dessus.
    </div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/coach/includes/footer.php'; ?>
