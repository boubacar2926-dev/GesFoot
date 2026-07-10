-- ============================================================
-- Plateforme Web de Gestion d'Équipe de Football
-- Schéma de base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS football_club
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE football_club;

-- ============================================================
-- TABLE: clubs
-- ============================================================
CREATE TABLE IF NOT EXISTS clubs (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    nom           VARCHAR(150) NOT NULL,
    logo          VARCHAR(255),
    adresse       TEXT,
    ville         VARCHAR(100),
    telephone     VARCHAR(20),
    email         VARCHAR(150),
    site_web      VARCHAR(255),
    annee_fondation YEAR,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: utilisateurs
-- ============================================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    nom           VARCHAR(100) NOT NULL,
    prenom        VARCHAR(100) NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    telephone     VARCHAR(20),
    mot_de_passe  VARCHAR(255) NOT NULL,
    role          ENUM('admin','coach','staff','joueur') NOT NULL DEFAULT 'joueur',
    statut        ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
    photo         VARCHAR(255),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: joueurs
-- ============================================================
CREATE TABLE IF NOT EXISTS joueurs (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id  INT,
    club_id         INT DEFAULT 1,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    date_naissance  DATE,
    nationalite     VARCHAR(100),
    taille          DECIMAL(4,1) COMMENT 'en cm',
    poids           DECIMAL(5,1) COMMENT 'en kg',
    poste           ENUM('Gardien','Défenseur','Milieu','Attaquant') NOT NULL,
    numero_maillot  TINYINT UNSIGNED,
    photo           VARCHAR(255),
    date_inscription DATE DEFAULT (CURRENT_DATE),
    statut          ENUM('actif','blessé','suspendu','inactif') NOT NULL DEFAULT 'actif',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    FOREIGN KEY (club_id)        REFERENCES clubs(id)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: competitions
-- ============================================================
CREATE TABLE IF NOT EXISTS competitions (
    id       INT PRIMARY KEY AUTO_INCREMENT,
    nom      VARCHAR(150) NOT NULL,
    type     ENUM('Championnat','Coupe','Amical','Tournoi') DEFAULT 'Championnat',
    saison   VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: matchs
-- ============================================================
CREATE TABLE IF NOT EXISTS matchs (
    id                    INT PRIMARY KEY AUTO_INCREMENT,
    competition_id        INT,
    date_match            DATE NOT NULL,
    heure_match           TIME,
    stade                 VARCHAR(200),
    adversaire            VARCHAR(150) NOT NULL,
    domicile_exterieur    ENUM('Domicile','Extérieur') DEFAULT 'Domicile',
    score_equipe          TINYINT UNSIGNED DEFAULT 0,
    score_adverse         TINYINT UNSIGNED DEFAULT 0,
    statut                ENUM('Programmé','En cours','Terminé','Annulé','Reporté') DEFAULT 'Programmé',
    notes                 TEXT,
    created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: convocations
-- ============================================================
CREATE TABLE IF NOT EXISTS convocations (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    match_id         INT NOT NULL,
    date_convocation DATE,
    lieu_rdv         VARCHAR(200),
    heure_rdv        TIME,
    notes            TEXT,
    statut           ENUM('Brouillon','Publiée','Annulée') DEFAULT 'Brouillon',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matchs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: convocation_joueur
-- ============================================================
CREATE TABLE IF NOT EXISTS convocation_joueur (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    convocation_id  INT NOT NULL,
    joueur_id       INT NOT NULL,
    statut          ENUM('Convoqué','Absent','Blessé') DEFAULT 'Convoqué',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_conv_joueur (convocation_id, joueur_id),
    FOREIGN KEY (convocation_id) REFERENCES convocations(id) ON DELETE CASCADE,
    FOREIGN KEY (joueur_id)      REFERENCES joueurs(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: compositions
-- ============================================================
CREATE TABLE IF NOT EXISTS compositions (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    match_id     INT NOT NULL,
    joueur_id    INT NOT NULL,
    type_joueur  ENUM('Titulaire','Remplaçant') NOT NULL DEFAULT 'Titulaire',
    poste_match  VARCHAR(50),
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_comp (match_id, joueur_id),
    FOREIGN KEY (match_id)  REFERENCES matchs(id)  ON DELETE CASCADE,
    FOREIGN KEY (joueur_id) REFERENCES joueurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: statistiques
-- ============================================================
CREATE TABLE IF NOT EXISTS statistiques (
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    joueur_id           INT NOT NULL,
    match_id            INT NOT NULL,
    minutes_jouees      SMALLINT UNSIGNED DEFAULT 0,
    buts                TINYINT UNSIGNED  DEFAULT 0,
    passes_decisives    TINYINT UNSIGNED  DEFAULT 0,
    cartons_jaunes      TINYINT UNSIGNED  DEFAULT 0,
    cartons_rouges      TINYINT UNSIGNED  DEFAULT 0,
    note_match          DECIMAL(3,1),
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_stat (joueur_id, match_id),
    FOREIGN KEY (joueur_id) REFERENCES joueurs(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id)  REFERENCES matchs(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES INITIALES
-- ============================================================

INSERT INTO clubs (nom, ville, email) VALUES
('Mon Club FC', 'Paris', 'contact@monclubfc.fr');

-- Mot de passe : Admin@2026  (remplacer le hash par celui généré par install.php)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Administrateur', 'Système', 'admin@club.fr', 'HASH_PLACEHOLDER', 'admin');

INSERT INTO competitions (nom, type, saison) VALUES
('Championnat Régional', 'Championnat', '2025-2026'),
('Coupe Régionale',      'Coupe',       '2025-2026');
