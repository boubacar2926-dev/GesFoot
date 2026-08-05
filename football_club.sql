-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 17 juil. 2026 à 19:53
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `football_club`
--

-- --------------------------------------------------------

--
-- Structure de la table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `annee_fondation` year(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `clubs`
--

INSERT INTO `clubs` (`id`, `nom`, `logo`, `adresse`, `ville`, `telephone`, `email`, `site_web`, `annee_fondation`, `created_at`, `updated_at`) VALUES
(1, 'AS Étoile de Dakar', 'club/8fe9bd6ac5608670.png', 'Route de Ouakam, Dakar-Plateau', 'Dakar', '+221 33 820 00 00', 'contact@etoiledeakar.sn', 'www.etoiledeakar.sn', '1963', '2026-06-26 10:40:39', '2026-07-10 12:35:39');

-- --------------------------------------------------------

--
-- Structure de la table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `type` enum('Championnat','Coupe','Amical','Tournoi') DEFAULT 'Championnat',
  `saison` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `competitions`
--

INSERT INTO `competitions` (`id`, `nom`, `type`, `saison`, `created_at`, `updated_at`) VALUES
(1, 'Ligue 1 Sénégal', 'Championnat', '2025-2026', '2026-06-26 10:40:39', '2026-06-26 10:52:43'),
(2, 'Coupe du Sénégal', 'Coupe', '2025-2026', '2026-06-26 10:40:39', '2026-06-26 10:52:43');

-- --------------------------------------------------------

--
-- Structure de la table `compositions`
--

CREATE TABLE `compositions` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `joueur_id` int(11) NOT NULL,
  `type_joueur` enum('Titulaire','Remplaçant') NOT NULL DEFAULT 'Titulaire',
  `poste_match` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `compositions`
--

INSERT INTO `compositions` (`id`, `match_id`, `joueur_id`, `type_joueur`, `poste_match`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 'Titulaire', 'Gardien', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(2, 6, 2, 'Remplaçant', 'Gardien', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(3, 6, 3, 'Titulaire', 'Défenseur', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(4, 6, 4, 'Titulaire', 'Défenseur', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(5, 6, 5, 'Titulaire', 'Défenseur', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(6, 6, 6, 'Titulaire', 'Défenseur', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(7, 6, 7, 'Titulaire', 'Défenseur', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(8, 6, 8, 'Remplaçant', 'Défenseur', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(9, 6, 9, 'Titulaire', 'Milieu', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(10, 6, 10, 'Titulaire', 'Milieu', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(11, 6, 11, 'Titulaire', 'Milieu', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(12, 6, 13, 'Remplaçant', 'Milieu', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(13, 6, 15, 'Titulaire', 'Attaquant', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(14, 6, 16, 'Remplaçant', 'Attaquant', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(15, 6, 17, 'Titulaire', 'Attaquant', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(16, 7, 1, 'Titulaire', NULL, '2026-06-29 10:16:25', '2026-06-29 10:16:25'),
(17, 7, 2, 'Remplaçant', NULL, '2026-06-29 10:16:25', '2026-06-29 10:16:25'),
(18, 7, 5, 'Titulaire', NULL, '2026-06-29 10:16:25', '2026-06-29 10:16:25'),
(19, 7, 3, 'Titulaire', NULL, '2026-06-29 10:16:25', '2026-06-29 10:16:25'),
(20, 7, 6, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(21, 7, 8, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(22, 7, 4, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(23, 7, 7, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(24, 7, 12, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(25, 7, 13, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(26, 7, 9, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(27, 7, 10, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(28, 7, 14, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(29, 7, 11, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(30, 7, 17, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(31, 7, 15, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(32, 7, 16, 'Titulaire', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(33, 7, 20, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(34, 7, 18, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26'),
(35, 7, 19, 'Remplaçant', NULL, '2026-06-29 10:16:26', '2026-06-29 10:16:26');

-- --------------------------------------------------------

--
-- Structure de la table `convocations`
--

CREATE TABLE `convocations` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `date_convocation` date DEFAULT NULL,
  `lieu_rdv` varchar(200) DEFAULT NULL,
  `heure_rdv` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `statut` enum('Brouillon','Publiée','Annulée') DEFAULT 'Brouillon',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `convocations`
--

INSERT INTO `convocations` (`id`, `match_id`, `date_convocation`, `lieu_rdv`, `heure_rdv`, `notes`, `statut`, `created_at`, `updated_at`) VALUES
(1, 7, '2025-10-17', 'Stade Léopold Sédar Senghor - Vestiaires', '14:00:00', 'Tenue officielle obligatoire. Séance vidéo à 14h30 avant l\'échauffement.', 'Publiée', '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(7, 8, '2026-07-12', 'centre douta seck', '13:30:00', 'aucun retard tolerer', 'Publiée', '2026-07-09 15:11:22', '2026-07-09 15:11:22');

-- --------------------------------------------------------

--
-- Structure de la table `convocation_joueur`
--

CREATE TABLE `convocation_joueur` (
  `id` int(11) NOT NULL,
  `convocation_id` int(11) NOT NULL,
  `joueur_id` int(11) NOT NULL,
  `statut` enum('Convoqué','Absent','Blessé') DEFAULT 'Convoqué',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `convocation_joueur`
--

INSERT INTO `convocation_joueur` (`id`, `convocation_id`, `joueur_id`, `statut`, `created_at`) VALUES
(1, 1, 1, 'Convoqué', '2026-06-26 10:52:44'),
(2, 1, 2, 'Convoqué', '2026-06-26 10:52:44'),
(3, 1, 3, 'Convoqué', '2026-06-26 10:52:44'),
(4, 1, 4, 'Convoqué', '2026-06-26 10:52:44'),
(5, 1, 5, 'Convoqué', '2026-06-26 10:52:44'),
(6, 1, 6, 'Convoqué', '2026-06-26 10:52:44'),
(7, 1, 7, 'Convoqué', '2026-06-26 10:52:44'),
(8, 1, 8, 'Convoqué', '2026-06-26 10:52:44'),
(9, 1, 9, 'Convoqué', '2026-06-26 10:52:44'),
(10, 1, 10, 'Convoqué', '2026-06-26 10:52:44'),
(11, 1, 11, 'Convoqué', '2026-06-26 10:52:44'),
(12, 1, 12, 'Convoqué', '2026-06-26 10:52:44'),
(13, 1, 13, 'Convoqué', '2026-06-26 10:52:44'),
(14, 1, 14, 'Convoqué', '2026-06-26 10:52:44'),
(15, 1, 15, 'Convoqué', '2026-06-26 10:52:44'),
(16, 1, 16, 'Convoqué', '2026-06-26 10:52:44'),
(17, 1, 17, 'Convoqué', '2026-06-26 10:52:44'),
(18, 1, 18, 'Convoqué', '2026-06-26 10:52:44'),
(19, 1, 19, 'Convoqué', '2026-06-26 10:52:44'),
(20, 1, 20, 'Convoqué', '2026-06-26 10:52:44'),
(59, 7, 1, 'Convoqué', '2026-07-09 15:11:22'),
(60, 7, 2, 'Convoqué', '2026-07-09 15:11:22'),
(61, 7, 5, 'Convoqué', '2026-07-09 15:11:22'),
(62, 7, 3, 'Convoqué', '2026-07-09 15:11:22'),
(63, 7, 6, 'Convoqué', '2026-07-09 15:11:22'),
(64, 7, 8, 'Convoqué', '2026-07-09 15:11:22'),
(65, 7, 4, 'Convoqué', '2026-07-09 15:11:22'),
(66, 7, 7, 'Convoqué', '2026-07-09 15:11:22'),
(67, 7, 12, 'Convoqué', '2026-07-09 15:11:22'),
(68, 7, 9, 'Convoqué', '2026-07-09 15:11:22'),
(69, 7, 10, 'Convoqué', '2026-07-09 15:11:22'),
(70, 7, 14, 'Convoqué', '2026-07-09 15:11:22'),
(71, 7, 11, 'Convoqué', '2026-07-09 15:11:22'),
(72, 7, 17, 'Convoqué', '2026-07-09 15:11:22'),
(73, 7, 15, 'Convoqué', '2026-07-09 15:11:22'),
(74, 7, 16, 'Convoqué', '2026-07-09 15:11:22'),
(75, 7, 20, 'Convoqué', '2026-07-09 15:11:22'),
(76, 7, 19, 'Convoqué', '2026-07-09 15:11:22');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_contact`
--

CREATE TABLE `demandes_contact` (
  `id` int(11) NOT NULL,
  `nom_club` varchar(255) NOT NULL,
  `nom_contact` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_contact`
--

INSERT INTO `demandes_contact` (`id`, `nom_club`, `nom_contact`, `email`, `telephone`, `message`, `created_at`) VALUES
(4, 'fallilou tall', 'alioune diakite', 'papadiaw65@gmail.com', '+221 781966159', NULL, '2026-07-13 18:32:11');

-- --------------------------------------------------------

--
-- Structure de la table `historique_matchs`
--

CREATE TABLE `historique_matchs` (
  `id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `match_label` varchar(255) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `action` enum('creation','modification','suppression') NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `joueurs`
--

CREATE TABLE `joueurs` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `club_id` int(11) DEFAULT 1,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `taille` decimal(4,1) DEFAULT NULL,
  `poids` decimal(5,1) DEFAULT NULL,
  `poste` enum('Gardien','Défenseur','Milieu','Attaquant') NOT NULL,
  `numero_maillot` tinyint(3) UNSIGNED DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `date_inscription` date DEFAULT curdate(),
  `statut` enum('actif','blessé','suspendu','inactif') NOT NULL DEFAULT 'actif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `joueurs`
--

INSERT INTO `joueurs` (`id`, `utilisateur_id`, `club_id`, `nom`, `prenom`, `date_naissance`, `nationalite`, `taille`, `poids`, `poste`, `numero_maillot`, `photo`, `date_inscription`, `statut`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'Cissé', 'Moussa', '1997-03-12', 'Sénégalaise', 192.0, 86.0, 'Gardien', 1, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(2, 5, 1, 'Gueye', 'Aliou', '2001-07-25', 'Sénégalaise', 188.0, 82.0, 'Gardien', 16, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(3, 6, 1, 'Diallo', 'Papa', '1998-11-04', 'Sénégalaise', 182.0, 77.0, 'Défenseur', 2, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(4, 7, 1, 'Ndiaye', 'Pape', '1999-06-18', 'Sénégalaise', 184.0, 79.0, 'Défenseur', 3, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(5, 8, 1, 'Ba', 'Mamadou', '1996-09-30', 'Sénégalaise', 186.0, 83.0, 'Défenseur', 4, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(6, 9, 1, 'Fall', 'Lamine', '2000-02-14', 'Sénégalaise', 180.0, 75.0, 'Défenseur', 5, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(7, 10, 1, 'Sow', 'Ibrahima', '1998-12-01', 'Sénégalaise', 179.0, 74.0, 'Défenseur', 6, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(8, 11, 1, 'Mbaye', 'Cheikh', '1995-05-22', 'Sénégalaise', 183.0, 80.0, 'Défenseur', 14, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(9, 12, 1, 'Diouf', 'Ousmane', '1999-08-09', 'Sénégalaise', 176.0, 71.0, 'Milieu', 7, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(10, 13, 1, 'Faye', 'Abdoulaye', '2000-04-17', 'Sénégalaise', 174.0, 70.0, 'Milieu', 8, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(11, 14, 1, 'Thiaw', 'Kalidou', '1997-10-03', 'Sénégalaise', 178.0, 73.0, 'Milieu', 10, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(12, 15, 1, 'Badji', 'Ismaïla', '2002-01-28', 'Sénégalaise', 177.0, 70.0, 'Milieu', 11, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(13, 16, 1, 'Camara', 'Idrissa', '2001-06-05', 'Guinéenne', 175.0, 69.0, 'Milieu', 15, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(14, 17, 1, 'Ndour', 'Gana', '2003-09-11', 'Sénégalaise', 172.0, 67.0, 'Milieu', 17, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(15, 18, 1, 'Kouyaté', 'Salif', '1998-07-19', 'Sénégalaise', 181.0, 77.0, 'Attaquant', 9, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(16, 19, 1, 'Mané', 'Boubacar', '1999-03-08', 'Sénégalaise', 179.0, 74.0, 'Attaquant', 19, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(17, 20, 1, 'Diatta', 'Nicolas', '2000-11-14', 'Sénégalaise', 180.0, 75.0, 'Attaquant', 20, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(18, 21, 1, 'Sarr', 'Bamba', '1996-08-27', 'Sénégalaise', 177.0, 72.0, 'Attaquant', 21, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(19, 22, 1, 'Sylla', 'Amadou', '2002-05-03', 'Guinéenne', 178.0, 73.0, 'Attaquant', 22, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(20, 23, 1, 'Niang', 'Mbaye', '2001-12-20', 'Sénégalaise', 176.0, 71.0, 'Attaquant', 23, NULL, '2026-06-26', 'actif', '2026-06-26 10:52:43', '2026-06-26 10:52:43');

-- --------------------------------------------------------

--
-- Structure de la table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `attempted_at`) VALUES
(8, 'b.sarr@etoiledeakar.sn', '2026-07-09 17:15:08'),
(9, 'b.sarr@etoiledeakar.sn', '2026-07-09 17:15:28'),
(6, 'm.sarr@etoiledeakar.sn', '2026-07-09 17:11:57'),
(7, 'm.sarr@etoiledeakar.sn', '2026-07-09 17:12:22');

-- --------------------------------------------------------

--
-- Structure de la table `matchs`
--

CREATE TABLE `matchs` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) DEFAULT NULL,
  `date_match` date NOT NULL,
  `heure_match` time DEFAULT NULL,
  `stade` varchar(200) DEFAULT NULL,
  `adversaire` varchar(150) NOT NULL,
  `domicile_exterieur` enum('Domicile','Extérieur') DEFAULT 'Domicile',
  `score_equipe` tinyint(3) UNSIGNED DEFAULT 0,
  `score_adverse` tinyint(3) UNSIGNED DEFAULT 0,
  `statut` enum('Programmé','En cours','Terminé','Annulé','Reporté') DEFAULT 'Programmé',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `matchs`
--

INSERT INTO `matchs` (`id`, `competition_id`, `date_match`, `heure_match`, `stade`, `adversaire`, `domicile_exterieur`, `score_equipe`, `score_adverse`, `statut`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-09-06', '16:00:00', 'Stade Léopold Sédar Senghor', 'Jaraaf de Dakar', 'Domicile', 2, 0, 'Terminé', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(2, 1, '2025-09-13', '16:00:00', 'Stade Alassane Djigo', 'AS Pikine', 'Extérieur', 1, 1, 'Terminé', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(3, 1, '2025-09-20', '16:00:00', 'Stade Léopold Sédar Senghor', 'Génération Foot', 'Domicile', 3, 1, 'Terminé', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(4, 1, '2025-09-27', '16:00:00', 'Stade Aline Sitoé Diatta', 'Casa Sports', 'Extérieur', 0, 1, 'Terminé', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(5, 1, '2025-10-04', '16:00:00', 'Stade Léopold Sédar Senghor', 'Teungueth FC', 'Domicile', 4, 2, 'Terminé', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(6, 1, '2025-10-11', '16:00:00', 'Stade Caroline Faye', 'Stade de Mbour', 'Extérieur', 1, 0, 'Terminé', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(7, 1, '2026-07-05', '16:00:00', 'Stade Léopold Sédar Senghor', 'Dakar Sacré-Cœur', 'Domicile', 2, 0, 'Terminé', NULL, '2026-06-26 10:52:43', '2026-06-29 10:33:36'),
(8, 1, '2026-07-12', '16:00:00', 'Stade Alboury Ndiaye', 'Ndiambour de Louga', 'Extérieur', NULL, NULL, 'Programmé', NULL, '2026-06-26 10:52:43', '2026-06-26 11:12:56'),
(9, 2, '2026-07-19', '16:00:00', 'Stade Léopold Sédar Senghor', 'Linguère de Saint-Louis', 'Domicile', NULL, NULL, 'Programmé', NULL, '2026-06-26 10:52:43', '2026-06-26 11:12:56'),
(10, 1, '2026-07-26', '16:00:00', 'Stade Demba Diop', 'US Gorée', 'Extérieur', NULL, NULL, 'Programmé', NULL, '2026-06-26 10:52:43', '2026-06-26 11:12:56');

-- --------------------------------------------------------

--
-- Structure de la table `statistiques`
--

CREATE TABLE `statistiques` (
  `id` int(11) NOT NULL,
  `joueur_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `minutes_jouees` smallint(5) UNSIGNED DEFAULT 0,
  `buts` tinyint(3) UNSIGNED DEFAULT 0,
  `passes_decisives` tinyint(3) UNSIGNED DEFAULT 0,
  `cartons_jaunes` tinyint(3) UNSIGNED DEFAULT 0,
  `cartons_rouges` tinyint(3) UNSIGNED DEFAULT 0,
  `note_match` decimal(3,1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `statistiques`
--

INSERT INTO `statistiques` (`id`, `joueur_id`, `match_id`, `minutes_jouees`, `buts`, `passes_decisives`, `cartons_jaunes`, `cartons_rouges`, `note_match`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 90, 0, 0, 0, 0, 8.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(2, 3, 1, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(3, 4, 1, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(4, 5, 1, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(5, 6, 1, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(6, 7, 1, 90, 0, 0, 1, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(7, 9, 1, 90, 0, 1, 0, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(8, 10, 1, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(9, 11, 1, 90, 0, 1, 0, 0, 7.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(10, 15, 1, 90, 1, 0, 0, 0, 8.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(11, 16, 1, 90, 1, 0, 0, 0, 8.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(16, 1, 2, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(17, 3, 2, 90, 0, 0, 0, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(18, 4, 2, 90, 0, 0, 0, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(19, 5, 2, 90, 0, 0, 1, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(20, 6, 2, 90, 0, 0, 1, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(21, 8, 2, 90, 0, 0, 0, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(22, 9, 2, 90, 0, 0, 0, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(23, 10, 2, 90, 0, 1, 0, 0, 6.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(24, 12, 2, 90, 0, 0, 0, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(25, 17, 2, 90, 1, 0, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(26, 18, 2, 90, 0, 0, 0, 0, 5.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(31, 1, 3, 90, 0, 0, 0, 0, 7.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(32, 3, 3, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(33, 4, 3, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(34, 5, 3, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(35, 6, 3, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(36, 7, 3, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(37, 11, 3, 90, 0, 1, 0, 0, 7.5, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(38, 13, 3, 90, 0, 1, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(39, 14, 3, 90, 0, 1, 0, 0, 7.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(40, 15, 3, 90, 2, 0, 0, 0, 9.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(41, 20, 3, 90, 1, 0, 0, 0, 8.0, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(46, 2, 4, 90, 0, 0, 0, 0, 4.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(47, 3, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(48, 4, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(49, 5, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(50, 6, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(51, 8, 4, 90, 0, 0, 0, 1, 3.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(52, 9, 4, 90, 0, 0, 1, 0, 4.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(53, 10, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(54, 12, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(55, 16, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(56, 18, 4, 90, 0, 0, 0, 0, 5.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(61, 1, 5, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(62, 3, 5, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(63, 4, 5, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(64, 5, 5, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(65, 6, 5, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(66, 7, 5, 90, 0, 0, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(67, 11, 5, 90, 0, 2, 0, 0, 8.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(68, 13, 5, 90, 0, 1, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(69, 14, 5, 90, 0, 1, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(70, 15, 5, 90, 2, 0, 0, 0, 9.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(71, 16, 5, 90, 1, 0, 0, 0, 8.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(72, 19, 5, 90, 1, 0, 0, 0, 8.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(76, 1, 6, 90, 0, 0, 0, 0, 8.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(77, 3, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(78, 4, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(79, 5, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(80, 6, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(81, 7, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(82, 9, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(83, 10, 6, 90, 0, 1, 0, 0, 7.0, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(84, 11, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(85, 15, 6, 90, 0, 0, 0, 0, 6.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(86, 17, 6, 90, 1, 0, 0, 0, 7.5, '2026-06-26 10:52:44', '2026-06-26 10:52:44'),
(102, 1, 7, 90, 0, 0, 0, 0, 7.5, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(103, 5, 7, 90, 0, 0, 0, 0, 7.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(104, 3, 7, 90, 1, 0, 0, 0, 8.5, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(105, 6, 7, 45, 0, 0, 0, 0, 6.5, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(106, 4, 7, 45, 0, 0, 0, 0, 7.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(107, 12, 7, 76, 0, 0, 0, 0, 6.5, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(108, 13, 7, 90, 0, 0, 0, 0, 8.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(109, 9, 7, 89, 0, 1, 0, 0, 8.5, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(110, 10, 7, 14, 0, 0, 0, 0, 6.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(111, 14, 7, 1, 0, 0, 0, 0, 6.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(112, 17, 7, 90, 0, 0, 0, 0, 7.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(113, 15, 7, 80, 0, 0, 0, 0, 7.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(114, 16, 7, 60, 0, 0, 0, 0, 7.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(115, 20, 7, 10, 1, 0, 0, 0, 6.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46'),
(116, 18, 7, 40, 0, 1, 0, 0, 7.0, '2026-06-29 10:42:46', '2026-06-29 10:42:46');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','coach','staff','joueur') NOT NULL DEFAULT 'joueur',
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `telephone`, `mot_de_passe`, `role`, `statut`, `photo`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Système', 'admin@club.fr', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'admin', 'actif', NULL, '2026-06-26 10:40:39', '2026-06-26 10:52:43'),
(2, 'Diagne', 'Ibrahima', 'coach@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'coach', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(3, 'Sarr', 'Cheikh', 'staff@etoiledeakar.sn', NULL, '.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'staff', 'actif', NULL, '2026-06-26 10:52:43', '2026-07-08 14:31:39'),
(4, 'Cissé', 'Moussa', 'm.cisse@etoiledeakar.sn', NULL, '$2y$10$9r7mTKtTf2WBEaLZbcoL7uPxcyHOFQmjRt0vBCDjy7n.p8VmDzDzm', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-07-08 14:40:20'),
(5, 'Gueye', 'Aliou', 'a.gueye@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(6, 'Diallo', 'Papa', 'p.diallo@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(7, 'Ndiaye', 'Pape', 'pa.ndiaye@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(8, 'Ba', 'Mamadou', 'ma.ba@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(9, 'Fall', 'Lamine', 'l.fall@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(10, 'Sow', 'Ibrahima', 'i.sow@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(11, 'Mbaye', 'Cheikh', 'c.mbaye@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(12, 'Diouf', 'Ousmane', 'o.diouf@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(13, 'Faye', 'Abdoulaye', 'ab.faye@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(14, 'Thiaw', 'Kalidou', 'k.thiaw@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(15, 'Badji', 'Ismaïla', 'is.badji@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(16, 'Camara', 'Idrissa', 'id.camara@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(17, 'Ndour', 'Gana', 'g.ndour@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(18, 'Kouyaté', 'Salif', 's.kouyate@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(19, 'Mané', 'Boubacar', 'b.mane@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(20, 'Diatta', 'Nicolas', 'n.diatta@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(21, 'Sarr', 'Bamba', 'ba.sarr@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(22, 'Sylla', 'Amadou', 'am.sylla@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(23, 'Niang', 'Mbaye', 'mb.niang@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'joueur', 'actif', NULL, '2026-06-26 10:52:43', '2026-06-26 10:52:43'),
(24, 'Diop', 'Fatou', 'f.diop@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'staff', 'actif', NULL, '2026-06-29 09:22:13', '2026-06-29 09:22:13'),
(25, 'Ndiaye', 'Moustapha', 'mo.ndiaye@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'staff', 'actif', NULL, '2026-06-29 09:22:13', '2026-06-29 09:22:13'),
(26, 'Kane', 'Aminata', 'a.kane@etoiledeakar.sn', NULL, '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa', 'staff', 'actif', NULL, '2026-06-29 09:22:13', '2026-06-29 09:22:13');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `compositions`
--
ALTER TABLE `compositions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_comp` (`match_id`,`joueur_id`),
  ADD KEY `joueur_id` (`joueur_id`);

--
-- Index pour la table `convocations`
--
ALTER TABLE `convocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`);

--
-- Index pour la table `convocation_joueur`
--
ALTER TABLE `convocation_joueur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_conv_joueur` (`convocation_id`,`joueur_id`),
  ADD KEY `joueur_id` (`joueur_id`);

--
-- Index pour la table `demandes_contact`
--
ALTER TABLE `demandes_contact`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `historique_matchs`
--
ALTER TABLE `historique_matchs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_match` (`match_id`),
  ADD KEY `idx_date` (`created_at`);

--
-- Index pour la table `joueurs`
--
ALTER TABLE `joueurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Index pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_time` (`email`,`attempted_at`);

--
-- Index pour la table `matchs`
--
ALTER TABLE `matchs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Index pour la table `statistiques`
--
ALTER TABLE `statistiques`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_stat` (`joueur_id`,`match_id`),
  ADD KEY `match_id` (`match_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `compositions`
--
ALTER TABLE `compositions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `convocations`
--
ALTER TABLE `convocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `convocation_joueur`
--
ALTER TABLE `convocation_joueur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT pour la table `demandes_contact`
--
ALTER TABLE `demandes_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `historique_matchs`
--
ALTER TABLE `historique_matchs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `joueurs`
--
ALTER TABLE `joueurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `matchs`
--
ALTER TABLE `matchs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `statistiques`
--
ALTER TABLE `statistiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `compositions`
--
ALTER TABLE `compositions`
  ADD CONSTRAINT `compositions_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matchs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compositions_ibfk_2` FOREIGN KEY (`joueur_id`) REFERENCES `joueurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `convocations`
--
ALTER TABLE `convocations`
  ADD CONSTRAINT `convocations_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matchs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `convocation_joueur`
--
ALTER TABLE `convocation_joueur`
  ADD CONSTRAINT `convocation_joueur_ibfk_1` FOREIGN KEY (`convocation_id`) REFERENCES `convocations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `convocation_joueur_ibfk_2` FOREIGN KEY (`joueur_id`) REFERENCES `joueurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `joueurs`
--
ALTER TABLE `joueurs`
  ADD CONSTRAINT `joueurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `joueurs_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `matchs`
--
ALTER TABLE `matchs`
  ADD CONSTRAINT `matchs_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `statistiques`
--
ALTER TABLE `statistiques`
  ADD CONSTRAINT `statistiques_ibfk_1` FOREIGN KEY (`joueur_id`) REFERENCES `joueurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `statistiques_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matchs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
