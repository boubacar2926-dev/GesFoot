-- ============================================================
-- DONNÉES DE TEST - AS Étoile de Dakar (Club fictif)
-- Mot de passe pour tous les comptes : Test1234
-- ============================================================

USE football_club;

-- 1. CLUB
UPDATE clubs SET
    nom             = 'AS Étoile de Dakar',
    ville           = 'Dakar',
    adresse         = 'Route de Ouakam, Dakar-Plateau',
    telephone       = '+221 33 820 00 00',
    email           = 'contact@etoiledeakar.sn',
    site_web        = 'www.etoiledeakar.sn',
    annee_fondation = 1963
WHERE id = 1;

-- 2. COMPÉTITIONS
UPDATE competitions SET nom='Ligue 1 Sénégal', saison='2025-2026' WHERE id=1;
UPDATE competitions SET nom='Coupe du Sénégal', saison='2025-2026' WHERE id=2;

-- 3. UTILISATEURS (mot de passe : Test1234)
SET @hash = '$2y$10$gurrIpoDe.UmZtjqTWVwZumo6.Lc2Sw/wcle58XVplwDeLmJ8kyVa';

-- Admin (déjà créé)
UPDATE utilisateurs SET prenom='Système', nom='Admin', mot_de_passe=@hash WHERE role='admin';

-- Coach
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES
('Diagne', 'Ibrahima', 'coach@etoiledeakar.sn', @hash, 'coach', 'actif');

-- Staff
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES
('Sarr', 'Cheikh', 'staff@etoiledeakar.sn', @hash, 'staff', 'actif');

-- 20 joueurs
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES
('Cissé',     'Moussa',     'm.cisse@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Gueye',     'Aliou',      'a.gueye@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Diallo',    'Papa',       'p.diallo@etoiledeakar.sn',   @hash, 'joueur', 'actif'),
('Ndiaye',    'Pape',       'pa.ndiaye@etoiledeakar.sn',  @hash, 'joueur', 'actif'),
('Ba',        'Mamadou',    'ma.ba@etoiledeakar.sn',      @hash, 'joueur', 'actif'),
('Fall',      'Lamine',     'l.fall@etoiledeakar.sn',     @hash, 'joueur', 'actif'),
('Sow',       'Ibrahima',   'i.sow@etoiledeakar.sn',      @hash, 'joueur', 'actif'),
('Mbaye',     'Cheikh',     'c.mbaye@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Diouf',     'Ousmane',    'o.diouf@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Faye',      'Abdoulaye',  'ab.faye@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Thiaw',     'Kalidou',    'k.thiaw@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Badji',     'Ismaïla',    'is.badji@etoiledeakar.sn',   @hash, 'joueur', 'actif'),
('Camara',    'Idrissa',    'id.camara@etoiledeakar.sn',  @hash, 'joueur', 'actif'),
('Ndour',     'Gana',       'g.ndour@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Kouyaté',   'Salif',      's.kouyate@etoiledeakar.sn',  @hash, 'joueur', 'actif'),
('Mané',      'Boubacar',   'b.mane@etoiledeakar.sn',     @hash, 'joueur', 'actif'),
('Diatta',    'Nicolas',    'n.diatta@etoiledeakar.sn',   @hash, 'joueur', 'actif'),
('Sarr',      'Bamba',      'ba.sarr@etoiledeakar.sn',    @hash, 'joueur', 'actif'),
('Sylla',     'Amadou',     'am.sylla@etoiledeakar.sn',   @hash, 'joueur', 'actif'),
('Niang',     'Mbaye',      'mb.niang@etoiledeakar.sn',   @hash, 'joueur', 'actif');

-- 4. FICHES JOUEURS
INSERT INTO joueurs (utilisateur_id, club_id, nom, prenom, date_naissance, nationalite, poste, numero_maillot, taille, poids, statut, date_inscription) VALUES
((SELECT id FROM utilisateurs WHERE email='m.cisse@etoiledeakar.sn'),    1, 'Cissé',    'Moussa',     '1997-03-12', 'Sénégalaise', 'Gardien',   1,  192, 86, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='a.gueye@etoiledeakar.sn'),    1, 'Gueye',    'Aliou',      '2001-07-25', 'Sénégalaise', 'Gardien',   16, 188, 82, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='p.diallo@etoiledeakar.sn'),   1, 'Diallo',   'Papa',       '1998-11-04', 'Sénégalaise', 'Défenseur', 2,  182, 77, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='pa.ndiaye@etoiledeakar.sn'),  1, 'Ndiaye',   'Pape',       '1999-06-18', 'Sénégalaise', 'Défenseur', 3,  184, 79, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='ma.ba@etoiledeakar.sn'),      1, 'Ba',       'Mamadou',    '1996-09-30', 'Sénégalaise', 'Défenseur', 4,  186, 83, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='l.fall@etoiledeakar.sn'),     1, 'Fall',     'Lamine',     '2000-02-14', 'Sénégalaise', 'Défenseur', 5,  180, 75, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='i.sow@etoiledeakar.sn'),      1, 'Sow',      'Ibrahima',   '1998-12-01', 'Sénégalaise', 'Défenseur', 6,  179, 74, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='c.mbaye@etoiledeakar.sn'),    1, 'Mbaye',    'Cheikh',     '1995-05-22', 'Sénégalaise', 'Défenseur', 14, 183, 80, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='o.diouf@etoiledeakar.sn'),    1, 'Diouf',    'Ousmane',    '1999-08-09', 'Sénégalaise', 'Milieu',    7,  176, 71, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='ab.faye@etoiledeakar.sn'),    1, 'Faye',     'Abdoulaye',  '2000-04-17', 'Sénégalaise', 'Milieu',    8,  174, 70, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='k.thiaw@etoiledeakar.sn'),    1, 'Thiaw',    'Kalidou',    '1997-10-03', 'Sénégalaise', 'Milieu',    10, 178, 73, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='is.badji@etoiledeakar.sn'),   1, 'Badji',    'Ismaïla',    '2002-01-28', 'Sénégalaise', 'Milieu',    11, 177, 70, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='id.camara@etoiledeakar.sn'),  1, 'Camara',   'Idrissa',    '2001-06-05', 'Guinéenne',   'Milieu',    15, 175, 69, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='g.ndour@etoiledeakar.sn'),    1, 'Ndour',    'Gana',       '2003-09-11', 'Sénégalaise', 'Milieu',    17, 172, 67, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='s.kouyate@etoiledeakar.sn'),  1, 'Kouyaté',  'Salif',      '1998-07-19', 'Sénégalaise', 'Attaquant', 9,  181, 77, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='b.mane@etoiledeakar.sn'),     1, 'Mané',     'Boubacar',   '1999-03-08', 'Sénégalaise', 'Attaquant', 19, 179, 74, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='n.diatta@etoiledeakar.sn'),   1, 'Diatta',   'Nicolas',    '2000-11-14', 'Sénégalaise', 'Attaquant', 20, 180, 75, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='ba.sarr@etoiledeakar.sn'),    1, 'Sarr',     'Bamba',      '1996-08-27', 'Sénégalaise', 'Attaquant', 21, 177, 72, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='am.sylla@etoiledeakar.sn'),   1, 'Sylla',    'Amadou',     '2002-05-03', 'Guinéenne',   'Attaquant', 22, 178, 73, 'actif', CURDATE()),
((SELECT id FROM utilisateurs WHERE email='mb.niang@etoiledeakar.sn'),   1, 'Niang',    'Mbaye',      '2001-12-20', 'Sénégalaise', 'Attaquant', 23, 176, 71, 'actif', CURDATE());

-- 5. MATCHS (adversaires = clubs réels de Ligue 1 Sénégal)
INSERT INTO matchs (adversaire, date_match, heure_match, stade, domicile_exterieur, competition_id, score_equipe, score_adverse, statut) VALUES
('Jaraaf de Dakar',       '2025-09-06', '16:00:00', 'Stade Léopold Sédar Senghor', 'Domicile', 1, 2, 0, 'Terminé'),
('AS Pikine',             '2025-09-13', '16:00:00', 'Stade Alassane Djigo',        'Extérieur',1, 1, 1, 'Terminé'),
('Génération Foot',       '2025-09-20', '16:00:00', 'Stade Léopold Sédar Senghor', 'Domicile', 1, 3, 1, 'Terminé'),
('Casa Sports',           '2025-09-27', '16:00:00', 'Stade Aline Sitoé Diatta',    'Extérieur',1, 0, 1, 'Terminé'),
('Teungueth FC',          '2025-10-04', '16:00:00', 'Stade Léopold Sédar Senghor', 'Domicile', 1, 4, 2, 'Terminé'),
('Stade de Mbour',        '2025-10-11', '16:00:00', 'Stade Caroline Faye',         'Extérieur',1, 1, 0, 'Terminé'),
('Dakar Sacré-Cœur',      '2025-10-18', '16:00:00', 'Stade Léopold Sédar Senghor', 'Domicile', 1, NULL, NULL, 'Programmé'),
('Ndiambour de Louga',    '2025-10-25', '16:00:00', 'Stade Alboury Ndiaye',        'Extérieur',1, NULL, NULL, 'Programmé'),
('Linguère de Saint-Louis','2025-11-01','16:00:00', 'Stade Léopold Sédar Senghor', 'Domicile', 2, NULL, NULL, 'Programmé'),
('US Gorée',              '2025-11-08', '16:00:00', 'Stade Demba Diop',            'Extérieur',1, NULL, NULL, 'Programmé');

-- 6. STATISTIQUES pour les 6 matchs terminés
-- Match 1 : Victoire 2-0 vs Jaraaf
INSERT INTO statistiques (match_id, joueur_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
SELECT m.id, j.id, 90,
    CASE j.nom WHEN 'Kouyaté' THEN 1 WHEN 'Mané' THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Thiaw'   THEN 1 WHEN 'Diouf' THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Sow'     THEN 1 ELSE 0 END,
    0,
    CASE j.nom
        WHEN 'Cissé'    THEN 8.0
        WHEN 'Kouyaté'  THEN 8.5
        WHEN 'Mané'     THEN 8.0
        WHEN 'Thiaw'    THEN 7.5
        WHEN 'Ba'       THEN 7.0
        ELSE 6.5
    END
FROM matchs m, joueurs j
WHERE m.adversaire='Jaraaf de Dakar'
  AND j.nom IN ('Cissé','Diallo','Ndiaye','Ba','Fall','Sow','Diouf','Faye','Thiaw','Kouyaté','Mané');

-- Match 2 : Nul 1-1 vs AS Pikine
INSERT INTO statistiques (match_id, joueur_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
SELECT m.id, j.id, 90,
    CASE j.nom WHEN 'Diatta' THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Faye'   THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Fall' THEN 1 WHEN 'Ba' THEN 1 ELSE 0 END,
    0,
    CASE j.nom
        WHEN 'Cissé'  THEN 6.5
        WHEN 'Diatta' THEN 7.0
        WHEN 'Faye'   THEN 6.5
        ELSE 5.5
    END
FROM matchs m, joueurs j
WHERE m.adversaire='AS Pikine'
  AND j.nom IN ('Cissé','Diallo','Ndiaye','Ba','Fall','Mbaye','Diouf','Faye','Badji','Diatta','Sarr');

-- Match 3 : Victoire 3-1 vs Génération Foot
INSERT INTO statistiques (match_id, joueur_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
SELECT m.id, j.id, 90,
    CASE j.nom WHEN 'Kouyaté' THEN 2 WHEN 'Niang' THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Thiaw'   THEN 1 WHEN 'Camara' THEN 1 WHEN 'Ndour' THEN 1 ELSE 0 END,
    0, 0,
    CASE j.nom
        WHEN 'Cissé'    THEN 7.5
        WHEN 'Kouyaté'  THEN 9.0
        WHEN 'Niang'    THEN 8.0
        WHEN 'Thiaw'    THEN 7.5
        WHEN 'Camara'   THEN 7.0
        ELSE 7.0
    END
FROM matchs m, joueurs j
WHERE m.adversaire='Génération Foot'
  AND j.nom IN ('Cissé','Diallo','Ndiaye','Ba','Fall','Sow','Thiaw','Camara','Ndour','Kouyaté','Niang');

-- Match 4 : Défaite 0-1 vs Casa Sports
INSERT INTO statistiques (match_id, joueur_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
SELECT m.id, j.id, 90,
    0, 0,
    CASE j.nom WHEN 'Diouf'  THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Mbaye'  THEN 1 ELSE 0 END,
    CASE j.nom
        WHEN 'Gueye'  THEN 4.5
        WHEN 'Mbaye'  THEN 3.5
        WHEN 'Diouf'  THEN 4.0
        ELSE 5.0
    END
FROM matchs m, joueurs j
WHERE m.adversaire='Casa Sports'
  AND j.nom IN ('Gueye','Diallo','Ndiaye','Ba','Fall','Mbaye','Diouf','Faye','Badji','Mané','Sarr');

-- Match 5 : Victoire 4-2 vs Teungueth FC
INSERT INTO statistiques (match_id, joueur_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
SELECT m.id, j.id, 90,
    CASE j.nom WHEN 'Kouyaté' THEN 2 WHEN 'Mané' THEN 1 WHEN 'Sylla' THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Thiaw'   THEN 2 WHEN 'Ndour' THEN 1 WHEN 'Camara' THEN 1 ELSE 0 END,
    0, 0,
    CASE j.nom
        WHEN 'Cissé'    THEN 7.0
        WHEN 'Kouyaté'  THEN 9.5
        WHEN 'Mané'     THEN 8.5
        WHEN 'Sylla'    THEN 8.0
        WHEN 'Thiaw'    THEN 8.5
        ELSE 7.0
    END
FROM matchs m, joueurs j
WHERE m.adversaire='Teungueth FC'
  AND j.nom IN ('Cissé','Diallo','Ndiaye','Ba','Fall','Sow','Thiaw','Camara','Ndour','Kouyaté','Mané','Sylla');

-- Match 6 : Victoire 1-0 vs Stade de Mbour
INSERT INTO statistiques (match_id, joueur_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
SELECT m.id, j.id, 90,
    CASE j.nom WHEN 'Diatta' THEN 1 ELSE 0 END,
    CASE j.nom WHEN 'Faye'   THEN 1 ELSE 0 END,
    0, 0,
    CASE j.nom
        WHEN 'Cissé'  THEN 8.5
        WHEN 'Diatta' THEN 7.5
        WHEN 'Faye'   THEN 7.0
        ELSE 6.5
    END
FROM matchs m, joueurs j
WHERE m.adversaire='Stade de Mbour'
  AND j.nom IN ('Cissé','Diallo','Ndiaye','Ba','Fall','Sow','Diouf','Faye','Thiaw','Kouyaté','Diatta');

-- 7. CONVOCATION pour Dakar Sacré-Cœur (prochain match)
INSERT INTO convocations (match_id, date_convocation, lieu_rdv, heure_rdv, notes, statut)
SELECT id, DATE_SUB(date_match, INTERVAL 1 DAY),
    'Stade Léopold Sédar Senghor - Vestiaires', '14:00:00',
    'Tenue officielle obligatoire. Séance vidéo à 14h30 avant l\'échauffement.',
    'Publiée'
FROM matchs WHERE adversaire='Dakar Sacré-Cœur';

INSERT INTO convocation_joueur (convocation_id, joueur_id)
SELECT cv.id, j.id
FROM convocations cv
JOIN matchs m ON m.id = cv.match_id
JOIN joueurs j ON j.statut = 'actif'
WHERE m.adversaire = 'Dakar Sacré-Cœur';

-- 8. COMPOSITION pour le dernier match terminé (Stade de Mbour)
INSERT INTO compositions (match_id, joueur_id, type_joueur, poste_match)
SELECT m.id, j.id,
    CASE j.nom
        WHEN 'Cissé'   THEN 'Titulaire'
        WHEN 'Diallo'  THEN 'Titulaire'
        WHEN 'Ndiaye'  THEN 'Titulaire'
        WHEN 'Ba'      THEN 'Titulaire'
        WHEN 'Fall'    THEN 'Titulaire'
        WHEN 'Sow'     THEN 'Titulaire'
        WHEN 'Diouf'   THEN 'Titulaire'
        WHEN 'Faye'    THEN 'Titulaire'
        WHEN 'Thiaw'   THEN 'Titulaire'
        WHEN 'Kouyaté' THEN 'Titulaire'
        WHEN 'Diatta'  THEN 'Titulaire'
        ELSE 'Remplaçant'
    END,
    j.poste
FROM matchs m, joueurs j
WHERE m.adversaire = 'Stade de Mbour'
  AND j.nom IN ('Cissé','Diallo','Ndiaye','Ba','Fall','Sow','Diouf','Faye','Thiaw','Kouyaté','Diatta',
                'Gueye','Mbaye','Camara','Mané');

SELECT '✓ Données de test insérées avec succès ! Club : AS Étoile de Dakar' AS statut;
