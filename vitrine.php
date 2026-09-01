<?php
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

startSession();

$success = false;
$errors  = [];
$saved   = [];

// Traitement du formulaire de demande de démo / commande / devis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    csrfVerify();

    $nomContact = trim($_POST['nom_contact'] ?? '');
    $nomClub    = trim($_POST['nom_club']    ?? '');
    $email      = trim($_POST['email']       ?? '');
    $telephone  = trim($_POST['telephone']   ?? '');
    $offre      = trim($_POST['offre']       ?? '');
    $message    = trim($_POST['message']     ?? '');
    $honeypot   = trim($_POST['site_web_club'] ?? '');

    $saved = compact('nomContact', 'nomClub', 'email', 'telephone', 'offre', 'message');

    if ($honeypot !== '') {
        $success = true;
        $saved   = [];
    } else {
        if (!$nomContact) $errors[] = 'Votre nom est requis.';
        if (!$nomClub)    $errors[] = 'Le nom de votre club ou organisation est requis.';
        if (!$email)      $errors[] = "L'adresse email est requise.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse email n'est pas valide.";

        if (!$errors) {
            $pdo = getPDO();
            $pdo->exec("CREATE TABLE IF NOT EXISTS demandes_contact (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                nom_contact VARCHAR(255) NOT NULL,
                nom_club    VARCHAR(255) NOT NULL,
                email       VARCHAR(255) NOT NULL,
                telephone   VARCHAR(50)  NULL,
                offre       VARCHAR(100) NULL,
                message     TEXT         NULL,
                created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $cols = $pdo->query("SHOW COLUMNS FROM demandes_contact LIKE 'offre'")->fetchColumn();
            if (!$cols) {
                $pdo->exec("ALTER TABLE demandes_contact ADD COLUMN offre VARCHAR(100) NULL AFTER telephone");
            }

            $stmt = $pdo->prepare("INSERT INTO demandes_contact (nom_contact, nom_club, email, telephone, offre, message) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nomContact, $nomClub, $email, $telephone ?: null, $offre ?: null, $message ?: null]);
            
            $success = true;
            $saved   = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ClubManager — Logiciel de Gestion Complet pour Clubs de Football</title>
    <meta name="description" content="Digitalisez la gestion de votre club de football : effectifs, convocations, matchs, statistiques et présences en un seul endroit.">
    
    <!-- Typographie : Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        :root {
            --brand-green: #166534;
            --brand-green-hover: #15803d;
            --brand-gold: #eab308;
            --dark-bg: #0f1923;
            --body-bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fff;
            color: #334155;
        }

        html { 
            scroll-behavior: smooth; 
            scroll-padding-top: 80px; 
        }

        section[id] {
            scroll-margin-top: 80px;
        }

        /* Navbar */
        .lp-navbar { 
            background: rgba(15, 25, 35, 0.95) !important; 
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,.08); 
        }
        .nav-link {
            color: rgba(255,255,255,.75) !important;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color .2s;
        }
        .nav-link:hover { color: #fff !important; }

        /* Hero */
        .lp-hero {
            position: relative;
            background-color: var(--dark-bg);
            background-image: linear-gradient(160deg, rgba(15, 25, 35, 0.94) 0%, rgba(22, 101, 52, 0.88) 100%);
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
            padding: 90px 0 110px;
        }

        .lp-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(234, 179, 8, 0.15);
            border: 1px solid rgba(234, 179, 8, 0.35);
            color: #fde047;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 30px;
            margin-bottom: 18px;
        }

        /* Dashboard Preview Mockup */
        .app-window {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,.5);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.2);
        }
        .app-window-header {
            background: #f1f5f9;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #10b981; }

        /* Stats bar */
        .stats-bar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            margin-top: -40px;
            position: relative;
            z-index: 10;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,.05);
        }

        /* Feature Cards */
        .lp-feat-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 28px 24px;
            height: 100%;
            background: #fff;
            transition: all .25s ease;
        }
        .lp-feat-card:hover { 
            box-shadow: 0 12px 24px rgba(0,0,0,.06); 
            transform: translateY(-3px);
            border-color: #cbd5e1;
        }
        .lp-feat-icon {
            width: 50px; height: 50px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-bottom: 18px;
        }

        /* Pricing Cards */
        .pricing-card {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 32px 24px;
            position: relative;
            transition: all .3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .pricing-card.popular {
            border-color: var(--brand-green);
            box-shadow: 0 20px 40px rgba(22, 101, 52, 0.12);
        }
        .popular-badge {
            position: absolute;
            top: -14px;
            right: 24px;
            background: var(--brand-green);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            text-transform: uppercase;
        }
    </style>
</head>
<body id="top">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg lp-navbar sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2 text-decoration-none" href="#top">
            <div style="width:34px;height:34px;background:var(--brand-green);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-trophy-fill text-white" style="font-size:.9rem"></i>
            </div>
            <span>ClubManager</span>
        </a>
        
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link px-3" href="#fonctionnalites">Fonctionnalités</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#tarifs">Tarifs & Offres</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#commande">Commander / Devis</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-sm btn-outline-light px-3 py-2 fw-semibold">
                    Connexion Club
                </a>
                <a href="#commande" class="btn btn-sm fw-semibold text-white px-3 py-2" style="background:var(--brand-green)">
                    Obtenir ClubManager
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="lp-hero">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <div class="lp-label"><i class="bi bi-rocket-takeoff-fill"></i> Solution clé en main pour clubs & académies</div>
                <h1 class="display-5 fw-extrabold text-white lh-sm mb-3">
                    Modernisez et digitalisez la gestion de votre <span style="color:var(--brand-gold)">club de football</span>
                </h1>
                <p class="mb-4" style="font-size:1.1rem;color:rgba(255,255,255,.85);line-height:1.7">
                    Gagnez du temps dans la gestion de vos effectifs, la diffusion des convocations de matchs, le suivi des présences et l'analyse des statistiques de vos joueurs.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#commande" class="btn btn-lg px-4 fw-bold" style="background:var(--brand-gold);color:#0f1923;border-radius:8px">
                        <i class="bi bi-cart-check me-2"></i>Commander mon logiciel
                    </a>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-lg btn-outline-light px-4" style="border-radius:8px">
                        <i class="bi bi-eye me-2"></i>Accès Démo
                    </a>
                </div>
            </div>

            <!-- Preview Dashboard -->
            <div class="col-lg-6">
                <div class="app-window">
                    <div class="app-window-header">
                        <span class="dot dot-red"></span>
                        <span class="dot dot-yellow"></span>
                        <span class="dot dot-green"></span>
                        <span class="ms-2 text-muted small fw-medium">ClubManager Pro — Aperçu de l'application</span>
                    </div>
                    <div class="p-4 bg-light">
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 bg-white border rounded">
                                    <div class="text-muted small">Effectif Total</div>
                                    <div class="fw-bold fs-4 text-dark mt-1">28 Joueurs</div>
                                    <div class="badge bg-success-subtle text-success mt-1">Saison 2025/2026</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-white border rounded">
                                    <div class="text-muted small">Prochain Match</div>
                                    <div class="fw-bold text-dark mt-1">ASC Étoile vs FootPulse</div>
                                    <div class="text-success small fw-semibold mt-1"><i class="bi bi-clock me-1"></i>Samedi à 16:00</div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-white border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small"><i class="bi bi-send-check text-success me-1"></i> Convocations envoyées</span>
                                <span class="badge bg-success">18 Convoqués</span>
                            </div>
                            <div class="progress" style="height:8px">
                                <div class="progress-bar bg-success" style="width:100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TARIFS -->
<section id="tarifs" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold text-success small">Tarification transparente</span>
            <h2 class="fw-bold mt-1">Choisissez la formule adaptée à votre club</h2>
            <p class="text-muted">Des tarifs accessibles pour les clubs amateurs, professionnels et académies.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Offre Mensuelle -->
            <div class="col-md-6 col-lg-4">
                <div class="pricing-card">
                    <h4 class="fw-bold text-dark">Abonnement Mensuel</h4>
                    <p class="text-muted small">Idéal pour démarrer sans engagement lourd.</p>
                    <div class="my-3">
                        <span class="fs-2 fw-extrabold text-dark">15 000 FCFA</span>
                        <span class="text-muted">/ mois</span>
                    </div>
                    <ul class="list-unstyled my-4 flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Hébergement inclus</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Gestion joueurs & staffs illimités</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Matchs & Convocations</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Mises à jour gratuites</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Support technique 7j/7</li>
                    </ul>
                    <a href="#commande" onclick="selectOffre('Abonnement Mensuel (15 000 FCFA/mois)')" class="btn btn-outline-success w-100 py-2 fw-semibold">Choisir cette formule</a>
                </div>
            </div>

            <!-- Offre Annuelle (Populaire) -->
            <div class="col-md-6 col-lg-4">
                <div class="pricing-card popular">
                    <span class="popular-badge">Recommandé</span>
                    <h4 class="fw-bold text-dark">Licence Annuelle</h4>
                    <p class="text-muted small">Économisez 2 mois d'abonnement sur la saison.</p>
                    <div class="my-3">
                        <span class="fs-2 fw-extrabold text-dark">150 000 FCFA</span>
                        <span class="text-muted">/ an</span>
                    </div>
                    <ul class="list-unstyled my-4 flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Tout ce qui est inclus dans le Mensuel</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Configuration initiale personnalisée</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Nom de domaine propre (ex: club.com)</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Assistance prioritaires aux entraîneurs</li>
                    </ul>
                    <a href="#commande" onclick="selectOffre('Licence Annuelle (150 000 FCFA/an)')" class="btn btn-success w-100 py-2 fw-semibold" style="background:var(--brand-green)">Commander maintenant</a>
                </div>
            </div>

            <!-- Licence Définitive / On-Premise -->
            <div class="col-md-6 col-lg-4">
                <div class="pricing-card">
                    <h4 class="fw-bold text-dark">Achat Décorréler / Sur-Mesure</h4>
                    <p class="text-muted small">Installation sur votre propre serveur.</p>
                    <div class="my-3">
                        <span class="fs-2 fw-extrabold text-dark">Sur Devis</span>
                    </div>
                    <ul class="list-unstyled my-4 flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Code source & Installation serveur propre</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Couleurs et logo du club sur-mesure</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Paiement en une seule fois</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Formation du personnel sur place/à distance</li>
                    </ul>
                    <a href="#commande" onclick="selectOffre('Sur-Mesure / Licence Définitive')" class="btn btn-outline-dark w-100 py-2 fw-semibold">Demander un devis</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FORMULAIRE DE COMMANDE / CONTACT -->
<section id="commande" class="py-5">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Commandez ClubManager ou demandez une démo</h2>
                    <p class="text-muted">Remplissez le formulaire ci-dessous, notre équipe vous contactera en moins de 24h pour activer votre espace.</p>
                </div>

                <?php if ($success): ?>
                <div class="alert alert-success text-center p-4 border-0 shadow-sm rounded-3">
                    <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                    <h5 class="fw-bold">Demande envoyée avec succès !</h5>
                    <p class="small text-muted mb-0">Nous vous recontacterons très rapidement pour finaliser l'installation et la configuration de votre club.</p>
                </div>
                <?php else: ?>

                <?php if ($errors): ?>
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0 small">
                        <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="card border-0 shadow p-4 rounded-3">
                    <form method="POST" action="<?= BASE_URL ?>/vitrine.php#commande">
                        <?= csrfField() ?>
                        <input type="hidden" name="contact_submit" value="1">
                        <div style="display:none" aria-hidden="true">
                            <input type="text" name="site_web_club" id="site_web_club" tabindex="-1">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Votre Nom complet *</label>
                                <input type="text" name="nom_contact" class="form-control" value="<?= e($saved['nomContact'] ?? '') ?>" placeholder="Ex: Moussa Diop" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nom du Club / Académie *</label>
                                <input type="text" name="nom_club" class="form-control" value="<?= e($saved['nomClub'] ?? '') ?>" placeholder="Ex: ASC Yakaar / Foot Academy" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Adresse Email *</label>
                                <input type="email" name="email" class="form-control" value="<?= e($saved['email'] ?? '') ?>" placeholder="contact@votreclub.sn" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Téléphone / WhatsApp *</label>
                                <input type="tel" name="telephone" class="form-control" value="<?= e($saved['telephone'] ?? '') ?>" placeholder="+221 77 000 00 00" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Formule souhaitée</label>
                                <select name="offre" id="select_offre" class="form-select">
                                    <option value="Licence Annuelle (150 000 FCFA/an)">Licence Annuelle (150 000 FCFA / an) — Recommandé</option>
                                    <option value="Abonnement Mensuel (15 000 FCFA/mois)">Abonnement Mensuel (15 000 FCFA / mois)</option>
                                    <option value="Sur-Mesure / Licence Définitive">Achat Définitif / Sur-Mesure (Sur Devis)</option>
                                    <option value="Demande de Démonstration">Demander une démonstration gratuite</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Message ou besoins spécifiques</label>
                                <textarea name="message" class="form-control" rows="3" placeholder="Précisez le nombre d'équipes, de joueurs ou vos questions..."><?= e($saved['message'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-success w-100 py-3 fw-bold" style="background:var(--brand-green)">
                                    <i class="bi bi-send-check me-2"></i> Envoyer ma demande
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="py-4 text-white" style="background:var(--dark-bg);border-top:1px solid rgba(255,255,255,.08)">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <div style="width:28px;height:28px;background:var(--brand-green);border-radius:6px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-trophy-fill text-white" style="font-size:.75rem"></i>
            </div>
            <span class="fw-bold small">ClubManager Pro</span>
            <span class="text-muted small">· Solution de gestion sportive commercialisée</span>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <a href="#tarifs" class="text-muted small text-decoration-none">Tarifs</a>
            <a href="#commande" class="text-muted small text-decoration-none">Contact</a>
            <a href="<?= BASE_URL ?>/auth/login.php" class="text-muted small text-decoration-none">Connexion</a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function selectOffre(val) {
    var select = document.getElementById('select_offre');
    if(select) {
        select.value = val;
    }
}

// Fermeture auto du menu mobile
document.querySelectorAll('.navbar-nav a[href^="#"]').forEach(link => {
    link.addEventListener('click', () => {
        const navMenu = document.getElementById('navMenu');
        const bsCollapse = bootstrap.Collapse.getInstance(navMenu);
        if (bsCollapse) {
            bsCollapse.hide();
        }
    });
});
</script>

</body>
</html>