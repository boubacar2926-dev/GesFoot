<?php
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

startSession();

$success = false;
$errors  = [];
$saved   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    csrfVerify();

    $nomClub    = trim($_POST['nom_club']    ?? '');
    $nomContact = trim($_POST['nom_contact'] ?? '');
    $email      = trim($_POST['email']       ?? '');
    $telephone  = trim($_POST['telephone']   ?? '');
    $message    = trim($_POST['message']     ?? '');
    $honeypot   = trim($_POST['site_web_club'] ?? '');

    $saved = compact('nomClub', 'nomContact', 'email', 'telephone', 'message');

    // Piège à robots : ce champ est invisible pour un humain, un bot le remplit souvent
    if ($honeypot !== '') {
        $success = true;
        $saved   = [];
    } else {

    if (!$nomClub)    $errors[] = 'Le nom du club est requis.';
    if (!$nomContact) $errors[] = 'Le nom du contact est requis.';
    if (!$email)      $errors[] = "L'adresse email est requise.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse email n'est pas valide.";

    if (!$errors) {
        $pdo = getPDO();
        $pdo->exec("CREATE TABLE IF NOT EXISTS demandes_contact (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            nom_club    VARCHAR(255) NOT NULL,
            nom_contact VARCHAR(255) NOT NULL,
            email       VARCHAR(255) NOT NULL,
            telephone   VARCHAR(50)  NULL,
            message     TEXT         NULL,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->prepare("INSERT INTO demandes_contact (nom_club, nom_contact, email, telephone, message) VALUES (?,?,?,?,?)")
            ->execute([$nomClub, $nomContact, $email, $telephone ?: null, $message ?: null]);
        $success = true;
        $saved   = [];
    }

    } // fin du else honeypot
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClubManager — La plateforme de gestion pour clubs de football</title>
    <meta name="description" content="ClubManager, la plateforme de gestion pour clubs de football amateurs : joueurs, matchs, convocations, compositions, statistiques et classement, réunis dans une interface simple et abordable.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="ClubManager — La plateforme de gestion pour clubs de football amateurs">
    <meta property="og:description" content="Gérez vos joueurs, matchs, convocations et statistiques depuis une interface simple, pensée pour les clubs amateurs.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; scroll-padding-top: 62px; }
        body { background: #fff; }

        /* Navbar */
        .lp-navbar { background: var(--sidebar-bg) !important; border-bottom: 1px solid rgba(255,255,255,.06); }

        /* Hero */
        .lp-hero {
            background: linear-gradient(160deg, #1b4332 0%, #0f1923 100%);
            min-height: calc(88vh - 58px);
            display: flex;
            align-items: center;
            padding: 60px 0;
        }

        /* Section label chip */
        .lp-label {
            display: inline-block;
            background: #f0fdf4;
            color: var(--fc-green);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 14px;
        }

        /* Feature cards */
        .lp-feat-icon {
            width: 46px; height: 46px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .lp-feat-card {
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 24px;
            height: 100%;
            background: #fff;
            transition: box-shadow .15s, transform .15s;
        }
        .lp-feat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); transform: translateY(-2px); }

        /* Why cards */
        .lp-why-card {
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 32px 28px;
            height: 100%;
            text-align: center;
        }
        .lp-why-icon {
            width: 60px; height: 60px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; margin: 0 auto 18px;
        }

        /* Hero mock cards */
        .lp-mock-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,.12);
        }
    </style>
</head>
<body>

<!-- ========================================================= NAVBAR -->
<nav class="navbar lp-navbar sticky-top py-2">
    <div class="container">
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2 text-decoration-none" href="<?= BASE_URL ?>/vitrine.php">
            <div style="width:32px;height:32px;background:var(--fc-green);border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-trophy-fill text-white" style="font-size:.85rem"></i>
            </div>
            ClubManager
        </a>
        <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-sm fw-semibold"
           style="background:var(--fc-green);color:#fff;border:none;padding:6px 14px">
            <i class="bi bi-box-arrow-in-right me-1"></i>Connexion membres
        </a>
    </div>
</nav>

<!-- ========================================================= HERO -->
<section class="lp-hero">
    <div class="container">
        <div class="row align-items-center gy-5">

            <!-- Left: copy -->
            <div class="col-lg-6">
                <div class="lp-label">Gestion de club · 100% web</div>
                <h1 class="display-5 fw-bold text-white lh-sm mb-3">
                    La plateforme pensée pour les
                    <span style="color:var(--fc-gold)">clubs de football amateurs</span>
                </h1>
                <p class="mb-4" style="font-size:1.1rem;color:rgba(255,255,255,.7);max-width:500px;line-height:1.7">
                    Gérez vos joueurs, planifiez vos matchs, publiez vos convocations et suivez vos statistiques — depuis une interface simple, sans infrastructure complexe.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= BASE_URL ?>/auth/login.php"
                       class="btn btn-lg px-4 fw-semibold"
                       style="background:var(--fc-gold);color:#1e293b;border:none">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </a>
                    <a href="#contact" class="btn btn-lg btn-outline-light px-4">
                        <i class="bi bi-calendar-check me-2"></i>Demander une démo
                    </a>
                </div>
                <div class="mt-4 d-flex gap-4 flex-wrap" style="color:rgba(255,255,255,.5);font-size:.82rem">
                    <span><i class="bi bi-check2 text-success me-1"></i>Accès multi-rôles</span>
                    <span><i class="bi bi-check2 text-success me-1"></i>Sans abonnement</span>
                    <span><i class="bi bi-check2 text-success me-1"></i>Données sécurisées</span>
                </div>
            </div>

            <!-- Right: mock UI cards -->
            <div class="col-lg-6 d-none d-lg-block">
                <div style="position:relative;height:290px;padding-left:20px">
                    <div class="lp-mock-card d-flex align-items-center gap-3"
                         style="position:absolute;top:0;right:70px;width:215px">
                        <div style="width:44px;height:44px;border-radius:8px;background:#f0fdf4;color:#166534;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1">24</div>
                            <div style="color:#64748b;font-size:.8rem">Joueurs actifs</div>
                        </div>
                    </div>
                    <div class="lp-mock-card d-flex align-items-center gap-3"
                         style="position:absolute;top:90px;right:0;width:215px">
                        <div style="width:44px;height:44px;border-radius:8px;background:#f0fdf4;color:#166534;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1">47</div>
                            <div style="color:#64748b;font-size:.8rem">Buts cette saison</div>
                        </div>
                    </div>
                    <div class="lp-mock-card d-flex align-items-center gap-3"
                         style="position:absolute;top:185px;right:55px;width:215px">
                        <div style="width:44px;height:44px;border-radius:8px;background:#fef9c3;color:#854d0e;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1">12</div>
                            <div style="color:#64748b;font-size:.8rem">Matchs joués</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ========================================================= FEATURES -->
<section id="fonctionnalites" style="background:#fff;padding:80px 0">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-label">Fonctionnalités</div>
            <h2 class="fw-bold mb-2">Tout ce dont votre club a besoin</h2>
            <p class="text-muted mx-auto" style="max-width:500px">
                Une suite complète de modules, pensée pour la gestion sportive et administrative au quotidien.
            </p>
        </div>
        <div class="row g-4">
        <?php
        $features = [
            ['bi-person-badge',          '#f0fdf4','#166534','Gestion des joueurs',
             'Profils complets, photos, statuts (actif, blessé, suspendu) et suivi de l\'effectif en temps réel.'],
            ['bi-calendar-event-fill',   '#f1f5f9','#475569','Planification des matchs',
             'Calendrier des rencontres, saisie des scores, lieu et compétition — historique complet saison par saison.'],
            ['bi-clipboard2-check-fill', '#f0fdf4','#166534','Convocations',
             'Créez et publiez vos convocations en quelques clics. Chaque joueur retrouve les siennes dans son espace personnel.'],
            ['bi-layout-text-sidebar',   '#f1f5f9','#475569','Compositions d\'équipe',
             'Définissez titulaires et remplaçants sur un terrain virtuel, consultez les compositions passées.'],
            ['bi-bar-chart-line',        '#f0fdf4','#166534','Statistiques détaillées',
             'Buts, passes décisives, minutes jouées, cartons — par joueur et par match, cumulés sur toute la saison.'],
            ['bi-award',                 '#fef9c3','#854d0e','Classement automatique',
             'Calculé en temps réel depuis vos résultats : points, différence de buts, buts pour et contre.'],
            ['bi-file-earmark-pdf',      '#f0fdf4','#166534','Rapports exportables',
             'Générez des PDF de performances individuelles et collectives pour vos dirigeants ou partenaires.'],
            ['bi-shield-check',          '#f1f5f9','#475569','Sécurité & Historique',
             'Comptes protégés par rôles distincts, protection CSRF et journal complet des modifications.'],
        ];
        foreach ($features as [$icon, $bg, $color, $title, $desc]):
        ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon mb-3" style="background:<?= $bg ?>;color:<?= $color ?>">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <h6 class="fw-bold mb-2"><?= $title ?></h6>
                    <p class="text-muted mb-0" style="font-size:.82rem;line-height:1.6"><?= $desc ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========================================================= WHY -->
<section id="pourquoi" style="background:#f8fafc;padding:80px 0">
    <div class="container">
        <div class="text-center mb-5">
            <div class="lp-label">Pourquoi ClubManager ?</div>
            <h2 class="fw-bold mb-2">Conçu pour le terrain, pas pour la vitrine</h2>
            <p class="text-muted mx-auto" style="max-width:500px">
                Une solution réaliste pour les clubs amateurs qui veulent s'organiser sans budget excessif ni complexité inutile.
            </p>
        </div>
        <div class="row g-4 justify-content-center">
        <?php
        $whys = [
            ['bi-cursor-fill',     '#f0fdf4','#166534','Simple à prendre en main',
             'Interface épurée, navigation rapide. Pas de formation requise — un dirigeant ou un coach peut démarrer en quelques minutes, sans manuel.'],
            ['bi-geo-alt-fill',    '#fef9c3','#854d0e','Pensé pour le contexte local',
             'Développé en tenant compte des réalités des clubs amateurs africains : pas de fonctionnalités superflues, accent sur l\'essentiel — bien fait, bien organisé.'],
            ['bi-currency-dollar', '#f1f5f9','#475569','Léger et économique',
             'Application web hébergeable sur un serveur basique (XAMPP, mutualisé). Aucune dépendance externe payante, aucun abonnement SaaS imposé.'],
        ];
        foreach ($whys as [$icon, $bg, $color, $title, $desc]):
        ?>
            <div class="col-md-4">
                <div class="lp-why-card">
                    <div class="lp-why-icon" style="background:<?= $bg ?>;color:<?= $color ?>">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= $title ?></h5>
                    <p class="text-muted mb-0" style="font-size:.88rem;line-height:1.7"><?= $desc ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========================================================= TARIFS -->
<section id="tarifs" style="background:#fff;padding:80px 0">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <div class="lp-label">Tarification</div>
                <h2 class="fw-bold mb-3">Un tarif adapté à la taille de votre club</h2>
                <p class="text-muted mb-4" style="line-height:1.7">
                    Pas d'abonnement standard imposé : chaque club est différent (effectif, compétitions, besoins).
                    Nous établissons un devis simple et transparent après un court échange, sans engagement.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-4 mb-4" style="font-size:.88rem;color:#475569">
                    <span><i class="bi bi-check2 text-success me-1"></i>Sans frais d'installation cachés</span>
                    <span><i class="bi bi-check2 text-success me-1"></i>Sans engagement long terme</span>
                    <span><i class="bi bi-check2 text-success me-1"></i>Adapté aux budgets amateurs</span>
                </div>
                <a href="#contact" class="btn btn-lg fw-semibold px-4"
                   style="background:var(--fc-green);color:#fff;border:none">
                    <i class="bi bi-chat-dots me-2"></i>Demander un devis gratuit
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================= CONTACT -->
<section id="contact" style="background:#f8fafc;padding:80px 0">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="text-center mb-5">
                    <div class="lp-label">Contact</div>
                    <h2 class="fw-bold mb-2">Demander une démonstration</h2>
                    <p class="text-muted">
                        Laissez-nous vos coordonnées — nous organisons une démo gratuite adaptée à votre club.
                    </p>
                </div>

                <?php if ($success): ?>
                <div class="text-center p-5" style="border:1px solid #f1f5f9;border-radius:8px">
                    <div class="mb-3" style="font-size:3rem;color:var(--fc-green)">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Demande envoyée !</h5>
                    <p class="text-muted mb-4">
                        Nous avons bien reçu votre demande. Nous vous contacterons très prochainement à l'adresse fournie.
                    </p>
                    <a href="<?= BASE_URL ?>/vitrine.php#contact" class="btn btn-outline-success btn-sm">
                        Envoyer une autre demande
                    </a>
                </div>
                <?php else: ?>

                <?php if ($errors): ?>
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div style="border:1px solid #f1f5f9;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.04)">
                    <div style="padding:32px">
                        <form method="POST" action="<?= BASE_URL ?>/vitrine.php#contact">
                            <?= csrfField() ?>
                            <input type="hidden" name="contact_submit" value="1">
                            <div style="position:absolute;left:-9999px;top:-9999px" aria-hidden="true">
                                <label for="site_web_club">Ne pas remplir ce champ</label>
                                <input type="text" name="site_web_club" id="site_web_club" tabindex="-1" autocomplete="off">
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom du club <span class="text-danger">*</span></label>
                                    <input type="text" name="nom_club" class="form-control"
                                           value="<?= e($saved['nomClub'] ?? '') ?>"
                                           placeholder="Ex : AS Étoile de Dakar" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Votre nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom_contact" class="form-control"
                                           value="<?= e($saved['nomContact'] ?? '') ?>"
                                           placeholder="Prénom et nom" required>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= e($saved['email'] ?? '') ?>"
                                           placeholder="contact@monclub.sn" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">
                                        Téléphone
                                        <span class="text-muted fw-normal">(optionnel)</span>
                                    </label>
                                    <input type="tel" name="telephone" class="form-control"
                                           value="<?= e($saved['telephone'] ?? '') ?>"
                                           placeholder="+221 77 000 00 00">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">
                                        Message
                                        <span class="text-muted fw-normal">(optionnel)</span>
                                    </label>
                                    <textarea name="message" class="form-control" rows="3"
                                              placeholder="Décrivez votre club, vos besoins, votre nombre de joueurs..."><?= e($saved['message'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 mt-1">
                                    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                                        <i class="bi bi-send me-2"></i>Envoyer la demande
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================= FOOTER -->
<footer style="background:var(--sidebar-bg);padding:36px 0">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <div style="width:30px;height:30px;background:var(--fc-green);border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="bi bi-trophy-fill text-white" style="font-size:.8rem"></i>
            </div>
            <strong class="text-white">ClubManager</strong>
            <span class="d-none d-sm-inline" style="color:rgba(255,255,255,.45)">
                · Plateforme de gestion de club de football
            </span>
        </div>
        <a href="<?= BASE_URL ?>/auth/login.php"
           style="color:rgba(255,255,255,.45);font-size:.85rem;text-decoration:none">
            <i class="bi bi-box-arrow-in-right me-1"></i>Accès membres
        </a>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
