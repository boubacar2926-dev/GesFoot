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
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= assetVersion('assets/css/style.css') ?>" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--paper);
            color: var(--text-muted);
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }

        section[id] {
            scroll-margin-top: 80px;
        }

        /* Navbar — surface pleine, pas de flou "verre" */
        .lp-navbar {
            background: var(--ink) !important;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .nav-link {
            color: rgba(255,255,255,.72) !important;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color .2s;
        }
        .nav-link:hover { color: #fff !important; }

        /* Hero */
        .lp-hero {
            position: relative;
            background-color: var(--ink);
            background-image:
                linear-gradient(180deg, rgba(20,38,33,.88) 0%, rgba(20,38,33,.94) 60%, var(--ink) 100%),
                url('https://live.staticflickr.com/8331/8138744735_44c3cc96f0_b.jpg');
            background-position: center 30%;
            background-size: cover;
            background-repeat: no-repeat;
            padding: 100px 0 90px;
        }

        .lp-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(182, 130, 47, .16);
            border: 1px solid rgba(182, 130, 47, .4);
            color: var(--gold-soft);
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 3px;
            margin-bottom: 20px;
        }

        /* Bande de chiffres — flotte sur le bas du hero photo */
        .stats-bar {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            margin-top: -48px;
            position: relative;
            z-index: 10;
            box-shadow: 0 20px 45px rgba(0,0,0,.12);
        }

        /* Cartes de fonctionnalités — retour visuel sobre au survol,
           pas de "décollage" vertical */
        .lp-feat-card {
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            padding: 28px 24px;
            height: 100%;
            background: var(--paper);
            transition: border-color .2s, box-shadow .2s;
        }
        .lp-feat-card:hover {
            box-shadow: var(--shadow-md);
            border-color: #d7d2c2;
        }
        .lp-feat-icon {
            width: 48px; height: 48px; border-radius: var(--r-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; margin-bottom: 18px;
        }

        /* Cartes tarifs */
        .pricing-card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            padding: 32px 24px;
            position: relative;
            transition: border-color .2s, box-shadow .2s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .pricing-card.popular {
            border-color: var(--pitch);
            box-shadow: 0 20px 40px rgba(31, 99, 73, .12);
        }
        .popular-badge {
            position: absolute;
            top: -13px;
            right: 24px;
            background: var(--gold);
            color: var(--ink);
            font-size: 0.72rem;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
    </style>
</head>
<body id="top">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg lp-navbar sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-white d-flex align-items-center gap-2 text-decoration-none" href="#top">
            <div style="width:34px;height:34px;background:var(--gold);border-radius:6px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-trophy-fill" style="font-size:.9rem;color:var(--ink)"></i>
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
                <a href="#commande" class="btn btn-sm fw-bold px-3 py-2" style="background:var(--gold);color:var(--ink)">
                    Obtenir ClubManager
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="lp-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="lp-label"><i class="bi bi-rocket-takeoff-fill"></i> Solution clé en main pour clubs & académies</div>
                <h1 class="display-5 fw-extrabold text-white lh-sm mb-3">
                    Modernisez et digitalisez la gestion de votre <span style="color:var(--gold)">club de football</span>
                </h1>
                <p class="mb-4 mx-auto" style="max-width:640px;font-size:1.1rem;color:rgba(255,255,255,.85);line-height:1.7">
                    Gagnez du temps dans la gestion de vos effectifs, la diffusion des convocations de matchs, le suivi des présences et l'analyse des statistiques de vos joueurs.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="#commande" class="btn btn-lg px-4 fw-bold" style="background:var(--gold);color:var(--ink);border-radius:6px">
                        <i class="bi bi-cart-check me-2"></i>Commander mon logiciel
                    </a>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-lg btn-outline-light px-4" style="border-radius:6px">
                        <i class="bi bi-eye me-2"></i>Accès Démo
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BANDE DE CHIFFRES -->
<div class="container">
    <div class="stats-bar px-4 py-4">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="fw-extrabold fs-3 text-success">20+</div>
                <div class="text-muted small">Joueurs suivis</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-extrabold fs-3 text-success">18–22</div>
                <div class="text-muted small">Joueurs convoqués / match</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-extrabold fs-3 text-success">4</div>
                <div class="text-muted small">Espaces dédiés (admin, coach, staff, joueur)</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="fw-extrabold fs-3 text-success">100%</div>
                <div class="text-muted small">Hébergé et sécurisé pour votre club</div>
            </div>
        </div>
    </div>
</div>

<!-- FONCTIONNALITÉS -->
<section id="fonctionnalites" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-bold text-success small">Tout, au même endroit</span>
            <h2 class="fw-bold mt-1">Ce que ClubManager gère pour vous</h2>
            <p class="text-muted">Les six écrans que votre staff utilisera chaque semaine, du recrutement au jour de match.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon bg-success-subtle text-success"><i class="bi bi-person-badge"></i></div>
                    <h5 class="fw-bold mb-2">Effectif & fiches joueurs</h5>
                    <p class="text-muted small mb-0">Profils complets — poste, âge, gabarit, photo, statut — classés et retrouvables en un clic.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon bg-warning-subtle text-warning"><i class="bi bi-clipboard2-check"></i></div>
                    <h5 class="fw-bold mb-2">Convocations encadrées</h5>
                    <p class="text-muted small mb-0">Entre 18 et 22 joueurs par match, lieu et heure de rendez-vous, notification automatique à ceux qui ne sont pas retenus.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon bg-success-subtle text-success"><i class="bi bi-layout-text-sidebar"></i></div>
                    <h5 class="fw-bold mb-2">Compositions d'équipe</h5>
                    <p class="text-muted small mb-0">Titulaires et remplaçants choisis uniquement parmi les joueurs convoqués — impossible d'aligner quelqu'un par erreur.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon bg-warning-subtle text-warning"><i class="bi bi-bar-chart-line"></i></div>
                    <h5 class="fw-bold mb-2">Statistiques par match</h5>
                    <p class="text-muted small mb-0">Buts, passes décisives, cartons, minutes jouées et note — cumulés automatiquement sur la saison.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon bg-success-subtle text-success"><i class="bi bi-award"></i></div>
                    <h5 class="fw-bold mb-2">Classement & compétitions</h5>
                    <p class="text-muted small mb-0">Suivi des résultats par compétition avec classement calculé match après match.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon bg-warning-subtle text-warning"><i class="bi bi-file-earmark-bar-graph"></i></div>
                    <h5 class="fw-bold mb-2">Rapports exportables</h5>
                    <p class="text-muted small mb-0">Rapports joueurs, matchs et performances générés en PDF, prêts à partager avec le bureau du club.</p>
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
                    <a href="#commande" onclick="selectOffre('Licence Annuelle (150 000 FCFA/an)')" class="btn btn-success w-100 py-2 fw-semibold">Commander maintenant</a>
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
                                <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
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
<footer class="py-4 text-white" style="background:var(--ink);border-top:1px solid rgba(255,255,255,.08)">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <div style="width:28px;height:28px;background:var(--gold);border-radius:6px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-trophy-fill" style="font-size:.75rem;color:var(--ink)"></i>
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
    <div class="container mt-3">
        <p class="text-muted mb-0" style="font-size:.68rem;opacity:.55">
            Photo d'illustration : « El Jaish football team » par Doha Stadium Plus Qatar (Vinod Divakaran), licence
            <a href="https://creativecommons.org/licenses/by/2.0/" target="_blank" rel="noopener" class="text-muted">CC BY 2.0</a> —
            <a href="https://www.flickr.com/photos/dohastadiumplusqatar/8138744735/" target="_blank" rel="noopener" class="text-muted">source</a>.
            À remplacer par une photo de votre propre club.
        </p>
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