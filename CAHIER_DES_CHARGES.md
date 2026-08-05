# Cahier des Charges
## Développement d'une Plateforme Web de Gestion d'Équipe de Football

| | |
|---|---|
| **Auteur** | Boubacar Ba |
| **Date** | Juin 2026 |
| **Version** | 1.0 |

---

## 1. Présentation du projet

### 1.1 Contexte

La gestion d'une équipe de football au niveau amateur repose encore fréquemment sur des outils manuels (fichiers Excel, cahiers, communications WhatsApp). Cette approche entraîne des pertes d'informations, des erreurs de saisie, des difficultés de suivi et une perte de temps significative pour les dirigeants, coachs et staff.

Afin de moderniser et professionnaliser cette gestion, ce projet propose le développement d'une plateforme web centralisée permettant de gérer l'ensemble des informations relatives à l'équipe de football (joueurs, matchs, statistiques, performances, convocations, etc.).

### 1.2 Problématique

Comment concevoir et développer une application web permettant de gérer efficacement les joueurs, les matchs, les statistiques et les performances d'une équipe de football, tout en offrant un accès sécurisé et adapté selon les rôles des utilisateurs (Administrateur, Coach, Staff Technique et Joueur) ?

### 1.3 Solution proposée

Développer une application web moderne qui permet :

- La gestion complète des utilisateurs et des joueurs
- La planification et le suivi des matchs
- La gestion des convocations et compositions d'équipe
- Le suivi détaillé des statistiques individuelles et collectives
- L'affichage automatique des classements
- La génération de rapports exportables
- Des tableaux de bord dynamiques adaptés à chaque rôle

---

## 2. Objectifs du projet

### 2.1 Objectif général

Concevoir et développer une plateforme web de gestion d'équipe de football permettant d'améliorer l'organisation sportive et administrative du club.

### 2.2 Objectifs spécifiques

- Gérer les utilisateurs et leurs rôles avec des permissions adaptées
- Centraliser la gestion des joueurs et de leur profil
- Planifier, suivre et archiver les matchs
- Automatiser la gestion des convocations et compositions d'équipe
- Enregistrer et analyser les statistiques des joueurs
- Générer automatiquement les classements
- Fournir des tableaux de bord dynamiques par rôle
- Produire des rapports sportifs exportables (PDF)
- Permettre aux joueurs de consulter leurs performances personnelles

---

## 3. Utilisateurs du système

| Rôle | Description | Permissions principales |
|---|---|---|
| Administrateur | Accès total au système | Gestion utilisateurs, club, données, rapports |
| Coach | Responsable de la gestion sportive | Convocations, composition, planification matchs, statistiques |
| Staff Technique | Chargé du suivi des performances | Saisie et mise à jour des statistiques |
| Joueur | Espace personnel | Consultation profil, statistiques, convocations et matchs |

### 3.1 Administrateur

- Gérer les comptes utilisateurs
- Ajouter coachs, staff et joueurs
- Modifier les informations du club
- Gérer les matchs et statistiques
- Consulter tous les rapports
- Supprimer des données

### 3.2 Coach

- Consulter l'effectif
- Créer les convocations
- Composer l'équipe (titulaires et remplaçants)
- Planifier les matchs
- Consulter statistiques et rapports

### 3.3 Staff Technique

- Enregistrer les statistiques après match
- Mettre à jour les performances
- Consulter les matchs et rapports

### 3.4 Joueur

- Consulter son profil et ses statistiques personnelles
- Voir ses convocations et matchs programmés
- Consulter le classement général

---

## 4. Fonctionnalités du système

### 4.1 Module Authentification

- Connexion / Déconnexion
- Gestion des sessions et des rôles
- Réinitialisation du mot de passe
- Protection des accès selon les rôles

### 4.2 Module Gestion des Utilisateurs

- Ajouter, modifier, supprimer, rechercher des utilisateurs
- Activer / désactiver un compte
- Informations : Nom, Prénom, Email, Téléphone, Rôle, Mot de passe

### 4.3 Module Gestion des Joueurs

- Ajouter, modifier, supprimer, rechercher un joueur
- Consulter le profil détaillé
- Informations : Nom, Prénom, Date de naissance, Nationalité, Taille, Poids, Poste, Numéro, Photo, Date d'inscription

### 4.4 Module Gestion des Matchs

- Ajouter, modifier, supprimer un match
- Consulter l'historique
- Informations : Date, Heure, Stade, Adversaire, Compétition, Score, Statut

### 4.5 Module Convocations

- Créer une convocation liée à un match
- Sélectionner les joueurs convoqués
- Afficher et consulter les convocations

### 4.6 Module Composition d'Équipe

- Créer une composition pour un match
- Définir titulaires et remplaçants
- Consulter les compositions passées

### 4.7 Module Statistiques

- Enregistrer et modifier les statistiques par joueur et par match
- Données : Minutes jouées, Buts, Passes décisives, Cartons jaunes, Cartons rouges

### 4.8 Module Classement

- Calcul automatique des points (Victoire = 3 pts, Nul = 1 pt, Défaite = 0 pt)
- Mise à jour automatique
- Affichage du classement général

### 4.9 Module Rapports

- Génération de rapports (joueurs, matchs, statistiques, performances)
- Export au format PDF

---

## 5. Tableaux de bord

| Rôle | Contenu du tableau de bord |
|---|---|
| **Administrateur** | Nombre d'utilisateurs, joueurs, matchs, convocations, derniers matchs, statistiques générales |
| **Coach** | Effectif, joueurs disponibles, matchs programmés, dernières performances |
| **Staff Technique** | Statistiques à saisir, derniers matchs, performances individuelles |
| **Joueur** | Informations personnelles, statistiques, matchs à venir, convocations |

---

## 6. Base de Données

### Tables principales

```sql
utilisateurs       (id, nom, prenom, email, telephone, mot_de_passe, role, statut, created_at, updated_at)
clubs              (id, nom, logo, adresse, ...)
joueurs            (id, utilisateur_id, club_id, date_naissance, nationalite, taille, poids, poste, numero_maillot, photo, date_inscription, statut)
competitions       (id, nom, type)
matchs             (id, date_match, heure_match, stade, adversaire, competition_id, score_equipe, score_adverse, statut)
convocations       (id, match_id, date_convocation, statut)
convocation_joueur (id, convocation_id, joueur_id)
compositions       (id, match_id, joueur_id, type_joueur -- Titulaire/Remplaçant)
statistiques       (id, joueur_id, match_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges)
```

> Toutes les tables incluront `created_at` et `updated_at`.

---

## 7. Exigences Non Fonctionnelles

### Performance

- Temps de chargement rapide des pages
- Optimisation des requêtes SQL

### Sécurité

- Authentification sécurisée avec hashage des mots de passe
- Gestion fine des rôles et permissions
- Protection contre les injections SQL (PDO)
- Protection CSRF et validation des formulaires
- Gestion sécurisée des sessions

### Ergonomie

- Interface intuitive et moderne
- Design responsive (mobile first)

### Compatibilité

- **Navigateurs** : Google Chrome, Mozilla Firefox, Microsoft Edge
- **Appareils** : Smartphones Android et iOS

---

## 8. Technologies Utilisées

| Couche | Technologie |
|---|---|
| Backend | PHP 8 + PDO |
| Base de données | MySQL |
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript |
| Environnement | XAMPP / Laragon |
| Versionnement | Git (recommandé) |

---

## 9. Livrables

- Application web fonctionnelle et testable

---

## 10. Planning Prévisionnel

| Semaine | Activités |
|---|---|
| 3 | Développement du module d'authentification |
| 4 | Gestion des utilisateurs et des rôles |
| 5 | Gestion des joueurs |
| 6 | Gestion des matchs et convocations |
| 7 | Gestion des statistiques et compositions |
| 8 | Tableaux de bord et module rapports |
| 9 | Tests, corrections et optimisation |
