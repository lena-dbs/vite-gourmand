-- 1. Création de la base de données 
-- Projet : Vite & Gourmand 


-- 2. Création des tables 
-- Table role 
CREATE TABLE `role` (
    `role_id`         INT          NOT NULL AUTO_INCREMENT,
    `libelle`         VARCHAR(50)  NOT NULL,
    PRIMARY KEY (`role_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;


-- Table utilisateur 
CREATE TABLE `utilisateur` (
  `utilisateur_id`    INT          NOT NULL AUTO_INCREMENT,
  `role_id`           INT          NOT NULL,
  `email`             VARCHAR(180) NOT NULL UNIQUE,
  `password`          VARCHAR(255) NOT NULL,
  `nom`               VARCHAR(80)  NOT NULL,
  `prenom`            VARCHAR(80)  NOT NULL,
  `telephone`         VARCHAR(20)  NOT NULL,
  `adresse`           VARCHAR(200) NOT NULL,
  `ville`             VARCHAR(100) NOT NULL,
  `code_postal`       VARCHAR(10)  NOT NULL,
  `pays`              VARCHAR(50)  NOT NULL,
  `actif`             BOOLEAN      NOT NULL DEFAULT 1,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`utilisateur_id`),
  CONSTRAINT `fk_util_role` FOREIGN KEY (`role_id`) REFERENCES `role`(`role_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;


-- Table réinitialisation de mot de passe 
CREATE TABLE `password_reset` (
    `token_id`        INT          NOT NULL AUTO_INCREMENT,
    `utilisateur_id`  INT          NOT NULL,
    `token`           VARCHAR(255) NOT NULL UNIQUE,
    `expires_at`      DATETIME     NOT NULL,
    `used`            BOOLEAN      NOT NULL DEFAULT 0,
    PRIMARY KEY (`token_id`),
    CONSTRAINT `fk_token_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur`(`utilisateur_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Table login_attempt (limitation des tentatives de connexion / reset)
CREATE TABLE `login_attempt` (
    `attempt_id`      INT          NOT NULL AUTO_INCREMENT,
    `email`           VARCHAR(255) NOT NULL,
    `ip`              VARCHAR(45)  NOT NULL,
    `type`            VARCHAR(20)  NOT NULL DEFAULT 'login',
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`attempt_id`),
    INDEX `idx_attempt_email` (`email`, `created_at`),
    INDEX `idx_attempt_ip` (`ip`, `created_at`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table theme 
CREATE TABLE `theme` (
    `theme_id`        INT          NOT NULL AUTO_INCREMENT,
    `libelle`         VARCHAR(80)  NOT NULL,
    PRIMARY KEY (`theme_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table regime 
CREATE TABLE `regime`(
    `regime_id`       INT          NOT NULL AUTO_INCREMENT,
    `libelle`         VARCHAR(80)  NOT NULL,
    PRIMARY KEY (`regime_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table menu
CREATE TABLE `menu` (
    `menu_id`         INT          NOT NULL AUTO_INCREMENT,
    `regime_id`       INT          NOT NULL,
    `theme_id`        INT          NOT NULL,
    `titre`           VARCHAR(150) NOT NULL,
    `description`     TEXT         NOT NULL,
    `nb_personnes_min`INT          NOT NULL,
    `prix_base`       DECIMAL(10,2)NOT NULL,
    `stock`           INT          NOT NULL,
    `image`           VARCHAR(255) NOT NULL,
    `actif`           BOOLEAN      NOT NULL DEFAULT 1,
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`menu_id`),
    CONSTRAINT `fk_menu_regime` FOREIGN KEY (`regime_id`) REFERENCES `regime`(`regime_id`),
    CONSTRAINT `fk_menu_theme`  FOREIGN KEY (`theme_id`) REFERENCES `theme`(`theme_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table condition de menu 
CREATE TABLE `condition_menu` (
    `condition_id`    INT          NOT NULL AUTO_INCREMENT,
    `menu_id`         INT          NOT NULL,
    `description`     TEXT         NOT NULL,
    PRIMARY KEY (`condition_id`),
    CONSTRAINT `fk_condition_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu`(`menu_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table plat 
CREATE TABLE `plat` (
    `plat_id`         INT                                   NOT NULL AUTO_INCREMENT,
    `nom`             VARCHAR(150)                          NOT NULL,
    `type`            ENUM('entree', 'plat', 'dessert')     NOT NULL,
    `description`     TEXT                                  NULL,
    `photo`           VARCHAR(300)                          NULL,
    PRIMARY KEY (`plat_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table menu_plat
CREATE TABLE `menu_plat` (
    `menu_id`         INT          NOT NULL,
    `plat_id`         INT          NOT NULL,
    PRIMARY KEY (`menu_id`, `plat_id`),
    CONSTRAINT `fk_menu_plat_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu`(`menu_id`),
    CONSTRAINT `fk_menu_plat_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat`(`plat_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table allergène 
CREATE TABLE `allergene` (
    `allergene_id`    INT          NOT NULL AUTO_INCREMENT,
    `libelle`         VARCHAR(100) NOT NULL,
    PRIMARY KEY (`allergene_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table plat_allergene  
CREATE TABLE `plat_allergene` (
    `plat_id`         INT          NOT NULL,
    `allergene_id`    INT          NOT NULL,
    PRIMARY KEY (`plat_id`, `allergene_id`),
    CONSTRAINT `fk_plat_allergene_plat` FOREIGN KEY (`plat_id`) REFERENCES `plat`(`plat_id`),
    CONSTRAINT `fk_plat_allergene_allergene` FOREIGN KEY (`allergene_id`) REFERENCES `allergene`(`allergene_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table horaire 
CREATE TABLE `horaire` (
    `horaire_id`      INT          NOT NULL AUTO_INCREMENT,
    `jour`            ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,   
    `heure_ouverture` TIME NULL,
    `heure_fermeture` TIME NULL,
    `ferme`           BOOLEAN      NOT NULL DEFAULT 0,
    PRIMARY KEY (`horaire_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table commande 
CREATE TABLE `commande` (
    `commande_id`     INT           NOT NULL AUTO_INCREMENT,
    `utilisateur_id`  INT           NOT NULL,
    `menu_id`         INT           NOT NULL,
    `nb_personnes`    INT           NOT NULL,
    `date_livraison`  DATE          NOT NULL,
    `heure_livraison` TIME          NOT NULL,
    `prix_menu`       DECIMAL(10,2) NOT NULL,
    `prix_livraison`  DECIMAL(10,2) NOT NULL,
    `prix_reduction`  DECIMAL(10,2) NOT NULL,
    `prix_total`      DECIMAL(10,2) NOT NULL,
    `pret_materiel`   BOOLEAN       NOT NULL DEFAULT 0,
    `restitution_materiel` BOOLEAN  NOT NULL DEFAULT 0,
    `motif_annulation`TEXT          NULL,
    `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`commande_id`),
    CONSTRAINT `fk_commande_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur`(`utilisateur_id`),
    CONSTRAINT `fk_commande_menu` FOREIGN KEY (`menu_id`) REFERENCES `menu`(`menu_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table suivi de commande 
CREATE TABLE `suivi_commande` (
    `suivi_id`        INT          NOT NULL AUTO_INCREMENT, 
    `commande_id`     INT          NOT NULL,
    `commentaire`     TEXT         NULL,
    `statut`        ENUM('en_attente', 'acceptee', 'en_preparation', 'en_livraison', 'livree', 'annulee', 'retour_materiel', 'terminee') NOT NULL DEFAULT 'en_attente',
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`suivi_id`),
    CONSTRAINT `fk_suivi_commande` FOREIGN KEY (`commande_id`) REFERENCES `commande`(`commande_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- Table avis 
CREATE TABLE `avis` (
    `avis_id`         INT          NOT NULL AUTO_INCREMENT,
    `utilisateur_id`  INT          NOT NULL,
    `commande_id`     INT          NOT NULL,
    `note`            TINYINT      NOT NULL CHECK (`note` BETWEEN 1 AND 5),
    `commentaire`     TEXT         NOT NULL,
    `statut`          ENUM('en_attente', 'valide', 'refuse') NOT NULL DEFAULT 'en_attente',
    `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`avis_id`),
    CONSTRAINT `fk_avis_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur`(`utilisateur_id`),
    CONSTRAINT `fk_avis_commande` FOREIGN KEY (`commande_id`) REFERENCES `commande`(`commande_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- 3. Données de démonstration 

-- Données pour la table role 
INSERT INTO `role` (`libelle`) VALUES
('administrateur'),
('employe'),
('utilisateur');

-- Données pour la table theme 
INSERT INTO `theme` (`libelle`) VALUES
('Noël'),
('Pâques'),
('Classique'),
('Evénement'),
('Apero Dinatoire'),
('Anniversaire'),
('Mariage');

-- Données pour la table regime 
INSERT INTO `regime` (`libelle`) VALUES
('vegetarien'),
('vegan'),
('sans gluten'),
('halal'),
('classique');


-- Données pour la table allergènes 
INSERT INTO `allergene` (`libelle`) VALUES 
('gluten'),
('oeufs'),
('arachides'),
('moutarde'),
('sesame'),
('mollusques'),
('crustacés'),
('lait');

-- Données pour la table horaire
INSERT INTO `horaire` (`jour`, `heure_ouverture`, `heure_fermeture`, `ferme`) VALUES
  ('lundi',    '09:00', '18:00', 0),
  ('mardi',    '09:00', '18:00', 0),
  ('mercredi', '09:00', '18:00', 0),
  ('jeudi',    '09:00', '18:00', 0),
  ('vendredi', '09:00', '20:00', 0),
  ('samedi',   '10:00', '20:00', 0),
  ('dimanche', NULL, NULL, 1);

-- Données pour la table utilisateur 
-- Comptes de démonstration (mot de passe : password) — actifs pour permettre de tester les parcours.
INSERT INTO `utilisateur` (`role_id`, `email`, `password`, `nom`, `prenom`, `telephone`, `adresse`, `ville`, `code_postal`, `pays`, `actif`) VALUES
(1, 'jose@vitegourmand.fr', '$2y$10$BT2Dy3h901a3aWNfsquUi.sm2N0e9UABMBTgGxK8MgDoW3c2Ms/Pm', 'Santos', 'José', '0600000001', '1 rue Sainte-Catherine', 'Bordeaux', '33000', 'France', 1),
(2, 'julie@vitegourmand.fr', '$2y$10$BT2Dy3h901a3aWNfsquUi.sm2N0e9UABMBTgGxK8MgDoW3c2Ms/Pm', 'Santos', 'Julie', '0600000002', '1 rue Sainte-Catherine', 'Bordeaux', '33000', 'France', 1),
(3, 'test@email.fr', '$2y$10$BT2Dy3h901a3aWNfsquUi.sm2N0e9UABMBTgGxK8MgDoW3c2Ms/Pm', 'Durand', 'Marie', '0600000003', '12 rue des Remparts', 'Bordeaux', '33000', 'France', 1);


-- Données pour la table plat 
INSERT INTO `plat` (`nom`, `type`, `description`, `photo`) VALUES
('Foie gras mi-cuit aux figues', 'entree', 'foie gras mi-cuit aux figues confites, toast de pain brioché et gelée de Sauternes', '/assets/images/noel-foie-gras.jpg'),
('Magret de canard confit aux épices', 'plat', 'Magret de canard confit aux épices de Noël, sauce au miel et au poivre, gratin dauphinois maison', '/assets/images/menu-noel.jpg'),
('Bûche artisanale chocolat Valrhona', 'dessert', 'Bûche artisanale au chocolat grand cru Valrhona, insert praliné noisette et glaçage miroir', '/assets/images/noel-buche.jpg'),
('Tarte salée poireaux et chèvre', 'entree', 'Tarte fine aux poireaux fondants, fromage de chèvre frais et thym citronné', '/assets/images/brunch-tarte.jpg'),
('Salade printanière aux fleurs comestibles', 'plat', 'Mélange de jeunes pousses, radis, concombre et fleurs comestibles, vinaigrette au miel', '/assets/images/brunch-paques.jpg'),
('Sablés décorés maison', 'dessert', 'Sablés bretons décorés à la royale, parfumés à la vanille de Madagascar', '/assets/images/brunch-sables.jpg'),
('Verrines avocat-crevettes', 'entree', 'Verrines fraîcheur avocat, crevettes roses et sauce cocktail maison', '/assets/images/cocktail-verrines.jpg'),
('Planches charcuterie et fromages affinés', 'plat', 'Sélection de charcuteries ibériques et fromages affinés, accompagnés de confiture de figues', '/assets/images/cocktail-planche.jpg'),
('Mignardises et macarons maison', 'dessert', 'Assortiment de mignardises et macarons aux parfums de saison', '/assets/images/cocktail-macaron.jpg'),
('Carpaccio de saint-jacques et agrumes', 'entree', 'Carpaccio de noix de saint-jacques, vinaigrette aux agrumes et zestes de citron vert', '/assets/images/anniversaire-carpaccio.jpg'),
('Filet de boeuf sauce bordelaise', 'plat', 'Filet de boeuf sauce bordelaise au vin rouge, légumes rôtis de saison', '/assets/images/menu-anniversaire.jpg'),
('Fondant chocolat sans gluten', 'dessert', 'Fondant au chocolat noir sans gluten, coulis de framboises et chantilly maison', '/assets/images/anniversaire-fondant.jpg');


-- Données pour la table menu 
INSERT INTO `menu` (`titre`, `theme_id`, `regime_id`, `nb_personnes_min`, `prix_base`, `stock`,`image`, `description`) VALUES 
('Noël Prestige', 1, 5, '10', '890.00', '25', '/assets/images/menu-noel.jpg', 'Un menu d''exception pour célébrer Noël en grande pompe. Foie gras mi-cuit, magret de canard confit aux épices et bûche artisanale chocolat Valrhona pour un repas inoubliable.' ),
('Brunch Pâques Végétarien', 2, 1, '6', '252.00', '20', '/assets/images/brunch-paques.jpg', 'Un brunch printanier et végétarien pour fêter Pâques en famille. Tarte salée, salade aux fleurs comestibles et sablés décorés maison pour une table colorée et gourmande.' ),
('Cocktail Dînatoire Prestige', 4, 5,'8', '280.00', '20','/assets/images/menu-cocktail.jpg', 'Un cocktail dînatoire raffiné pour vos événements professionnels ou personnels. Verrines, planches de charcuterie et fromages affinés, mignardises et macarons maison.' ),
('Anniversaire Sans Gluten', 6, 3, '10', '350.00', '25', '/assets/images/menu-anniversaire.jpg', 'Un menu festif et accessible à tous, entièrement sans gluten. Carpaccio de saint-jacques, filet de bœuf sauce bordelaise et fondant au chocolat pour un anniversaire mémorable.' );


-- Données pour la table menu_plat
INSERT INTO `menu_plat` (`menu_id`, `plat_id`) VALUES 
(1, 1),
(1, 2),
(1, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 7),
(3, 8),
(3, 9),
(4, 10),
(4, 11),
(4, 12);


-- Données pour la table plat_allergene
INSERT INTO `plat_allergene` ( `plat_id`,`allergene_id`) VALUES
(1, 1),  
(1, 8),
(2, 8),
(3, 1),
(3, 2),
(3, 8),
(4, 1),
(4, 2),
(4, 8),
(5, 4),
(6, 1),
(6, 2),
(6, 8),
(7, 7),
(8, 8),
(9, 1),
(9, 2),
(9, 8),
(10, 6),
(11, 8),
(12, 2),
(12, 8);

-- Données pour la table condition_menu
INSERT INTO `condition_menu` (`menu_id`, `description`) VALUES 
(1, 'Commander 7 jours à l''avance minimum'),
(1, 'Livraison gratuite à Bordeaux, 5.00 + 0.59/km au-delà'),
(1, 'Réduction de 10% à partir de 15 personnes'),
(2, 'Commander 5 jours à l''avance minimum'),
(2, 'Livraison gratuite à Bordeaux, 5.00 + 0.59/km au-delà'),
(2, 'Réduction de 10% à partir de 11 personnes'),
(3, 'Commander 3 jours à l''avance minimum'),
(3, 'Livraison gratuite à Bordeaux, 5.00 + 0.59/km au-delà'),
(3, 'Réduction de 10% à partir de 13 personnes'),
(4, 'Commander 5 jours à l''avance minimum'),
(4, 'Livraison gratuite à Bordeaux, 5.00 + 0.59/km au-delà'),
(4, 'Réduction de 10% à partir de 15 personnes');


-- Données pour la table commande
INSERT INTO `commande` (`utilisateur_id`, `menu_id`, `nb_personnes`, `date_livraison`, `heure_livraison`, `prix_menu`, `prix_livraison`, `prix_reduction`, `prix_total`, `pret_materiel`, `restitution_materiel`, `motif_annulation`) VALUES
(3, 1, 10, '2026-12-23', '11:00', '890.00', '0.00', '0.00', '890.00', 0, 0, NULL ),
(3, 2, 15, '2026-04-04', '9:00', '252.00', '5.00', '25.20', '231.80', 1 , 1, NULL ),
(3, 3, 8, '2026-07-10', '14:00', '280.00', '0.00', '0.00', '280.00', 0, 0, NULL);


-- Données pour la table suivi de commande
INSERT INTO `suivi_commande` (`commande_id`, `statut`, `commentaire`) VALUES
(1, 'en_attente', NULL),
(1, 'acceptee', 'Commande validée par l''équipe'),
(1, 'en_preparation', 'Commande prise en charge par Julie'),
(1, 'en_livraison', 'Commande en cours de livraison'),
(1, 'livree', 'Livraison effectuée à 11h10'),
(1, 'terminee', 'Commande clôturée'),
(2, 'en_attente', NULL ),
(2, 'acceptee', 'Commande validée par l''équipe'),
(2, 'en_preparation', 'Commande prise en charge par Julie'),
(2, 'en_livraison', 'Commande en cours de livraison'),
(2, 'livree', 'Livraison effectuée à 9h00'),
(2, 'retour_materiel', 'Matériel retourné à temps'),
(2, 'terminee', 'Commande clôturée'),
(3, 'en_preparation', NULL);


-- Données pour la table avis 
INSERT INTO `avis` (`utilisateur_id`, `commande_id`, `note`, `commentaire`, `statut`) VALUES 
(3, 1, 5, 'Un repas de Noël absolument inoubliable. Le foie gras était fondant, le magret parfaitement cuit et la bûche Valrhona à tomber. Merci à toute l''équipe !', 'valide' ),
(3, 2, 5, 'Un brunch frais et coloré, parfait pour fêter Pâques en famille. Les sablés décorés maison ont fait l''unanimité. Livraison ponctuelle et présentation soignée !', 'valide' );

