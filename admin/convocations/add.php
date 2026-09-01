<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
$pdo = getPDO();

// Mode édition si id passé
$editId = (int)($_GET['id'] ?? 0);
$conv   = null;
$selected_joueurs = [];

if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM convocations WHERE id=?");
    $stmt->execute([$editId]);
    $conv = $stmt->fetch();
    if ($conv) {
        $sj = $pdo->prepare("SELECT joueur_id FROM convocation_joueur WHERE convocation_id=?");
        $sj->execute([$editId]);
        $selected_joueurs = array_column($sj->fetchAll(), 'joueur_id');
    }
}

// Match pré-sélectionné (depuis la page matchs)
$preMatchId = (int)($_GET['match_id'] ?? ($conv['match_id'] ?? 0));

$stmtMatchs = $pdo->prepare("SELECT m.*, c.nom AS competition FROM matchs m LEFT JOIN competitions c ON c.id=m.competition_id WHERE (m.statut IN ('Programmé','Reporté') AND m.date_match >= CURDATE()) OR m.id = ? ORDER BY m.date_match ASC");
$stmtMatchs->execute([$conv['match_id'] ?? 0]);
$matchs = $stmtMatchs->fetchAll();
$joueurs = $pdo->query("SELECT * FROM joueurs WHERE statut='actif' ORDER BY poste, nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $match_id   = (int)($_POST['match_id']   ?? 0);
    $date_conv  = $_POST['date_convocation'] ?? null;
    $lieu_rdv   = trim($_POST['lieu_rdv']    ?? '');
    $heure_rdv  = $_POST['heure_rdv']        ?? null;
    $notes      = trim($_POST['notes']       ?? '');
    $statut     = $_POST['statut']           ?? 'Brouillon';
    $joueurs_ids = array_map('intval', $_POST['joueurs'] ?? []);
    $errors = [];

    if (!$match_id) $errors[] = 'Veuillez sélectionner un match.';
    if (count($joueurs_ids) > 22) $errors[] = 'Vous ne pouvez pas convoquer plus de 22 joueurs (actuellement : ' . count($joueurs_ids) . ' sélectionnés).';
    if (count($joueurs_ids) < 18) $errors[] = 'Vous devez convoquer au moins 18 joueurs (actuellement : ' . count($joueurs_ids) . ' sélectionnés).';

    if (!$errors) {
        if ($editId && $conv) {
            $pdo->prepare("UPDATE convocations SET match_id=?,date_convocation=?,lieu_rdv=?,heure_rdv=?,notes=?,statut=? WHERE id=?")
                ->execute([$match_id,$date_conv?:null,$lieu_rdv?:null,$heure_rdv?:null,$notes?:null,$statut,$editId]);
            $pdo->prepare("DELETE FROM convocation_joueur WHERE convocation_id=?")->execute([$editId]);
            $convId = $editId;
        } else {
            $pdo->prepare("INSERT INTO convocations (match_id,date_convocation,lieu_rdv,heure_rdv,notes,statut) VALUES (?,?,?,?,?,?)")
                ->execute([$match_id,$date_conv?:null,$lieu_rdv?:null,$heure_rdv?:null,$notes?:null,$statut]);
            $convId = $pdo->lastInsertId();
        }
        // Ajouter les joueurs
        $ins = $pdo->prepare("INSERT IGNORE INTO convocation_joueur (convocation_id, joueur_id) VALUES (?,?)");
        foreach ($joueurs_ids as $jid) {
            if ($jid > 0) $ins->execute([$convId, $jid]);
        }
        setFlash('success', $editId ? 'Convocation mise à jour.' : 'Convocation créée.');
        redirect('/admin/convocations/index.php');
    }
    $selected_joueurs = $joueurs_ids;
    $preMatchId = $match_id;
}

$pageTitle = $editId ? 'Modifier la convocation' : 'Créer une convocation';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/convocations/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><?= $pageTitle ?></h4>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="POST" id="convForm">
<?= csrfField() ?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>Informations</div>
            <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Match <span class="text-danger">*</span></label>
                        <select name="match_id" class="form-select" required>
                            <option value="">— Sélectionner un match —</option>
                            <?php foreach ($matchs as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $preMatchId===$m['id']?'selected':'' ?>>
                                    <?= formatDate($m['date_match']) ?> — vs <?= e($m['adversaire']) ?>
                                    <?php if ($m['competition']): ?>(<?= e($m['competition']) ?>)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date de convocation</label>
                        <input type="date" name="date_convocation" class="form-control"
                               value="<?= e($conv['date_convocation'] ?? '') ?>">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label">Lieu de RDV</label>
                            <input type="text" name="lieu_rdv" class="form-control"
                                   value="<?= e($conv['lieu_rdv'] ?? '') ?>" placeholder="Ex: Stade municipal">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Heure RDV</label>
                            <input type="time" name="heure_rdv" class="form-control"
                                   value="<?= e(substr($conv['heure_rdv'] ?? '',0,5)) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <?php foreach (['Brouillon','Publiée','Annulée'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($conv['statut'] ?? 'Brouillon')===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= e($conv['notes'] ?? '') ?></textarea>
                    </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-people me-2"></i>Joueurs à convoquer</span>
                <div class="d-flex align-items-center gap-2 small">
                    <span id="convCounter" class="fw-semibold text-warning">0 / 22 (min. 18)</span>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAll(true)">Tous</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(false)">Aucun</button>
                </div>
            </div>
            <div class="card-body p-0" style="max-height:460px;overflow-y:auto">
                <?php
                $byPoste = [];
                foreach ($joueurs as $j) $byPoste[$j['poste']][] = $j;
                foreach ($byPoste as $poste => $liste):
                ?>
                <div class="px-3 pt-3 pb-1 bg-light border-bottom">
                    <small class="text-uppercase fw-bold text-muted"><?= $poste ?>s</small>
                </div>
                <?php foreach ($liste as $j): ?>
                <label class="d-flex align-items-center gap-3 px-3 py-2 border-bottom hover-bg" style="cursor:pointer">
                    <input type="checkbox" name="joueurs[]" value="<?= $j['id'] ?>" class="form-check-input joueur-check"
                           <?= in_array($j['id'], $selected_joueurs) ? 'checked' : '' ?>>
                    <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar-sm">
                    <div>
                        <div class="fw-semibold small"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></div>
                        <?php if ($j['numero_maillot']): ?>
                            <small class="text-muted">#<?= $j['numero_maillot'] ?></small>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-light text-dark ms-auto"><?= statusBadge($j['statut']) ?></span>
                </label>
                <?php endforeach; ?>
                <?php endforeach; ?>
                <?php if (!$joueurs): ?>
                    <div class="text-center py-4 text-muted small">Aucun joueur actif</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end mt-4">
    <a href="<?= BASE_URL ?>/admin/convocations/index.php" class="btn btn-outline-secondary">Annuler</a>
    <button type="submit" form="convForm" class="btn btn-success"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
</div>
</form>

<script>
(function () {
    const MIN     = 18;
    const MAX     = 22;
    const counter = document.getElementById('convCounter');
    const allBoxes = () => [...document.querySelectorAll('.joueur-check')];

    function update() {
        const boxes   = allBoxes();
        const n       = boxes.filter(c => c.checked).length;

        counter.textContent = n + ' / ' + MAX + ' (min. ' + MIN + ')';
        counter.className   = n >= MAX ? 'fw-semibold text-danger'
                             : n < MIN  ? 'fw-semibold text-warning'
                             :            'fw-semibold text-success';

        boxes.forEach(c => { if (!c.checked) c.disabled = (n >= MAX); });
    }

    window.selectAll = function (state) {
        allBoxes().forEach((c, i) => {
            c.disabled = false;
            c.checked  = state && i < MAX;
        });
        update();
    };

    document.addEventListener('change', e => {
        if (e.target.classList.contains('joueur-check')) update();
    });

    update();
}());
</script>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
