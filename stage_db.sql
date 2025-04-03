-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 03 avr. 2025 à 17:34
-- Version du serveur : 8.0.41-0ubuntu0.24.04.1
-- Version de PHP : 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `stages_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `candidatures`
--

CREATE TABLE `candidatures` (
                                `id` int NOT NULL,
                                `offre_id` int NOT NULL,
                                `etudiant_id` int NOT NULL,
                                `cv` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `lettre_motivation` text COLLATE utf8mb4_unicode_ci NOT NULL,
                                `date_candidature` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `centres`
--

CREATE TABLE `centres` (
                           `id` int NOT NULL,
                           `nom` varchar(100) NOT NULL,
                           `code` varchar(20) NOT NULL,
                           `adresse` text,
                           `created_at` datetime NOT NULL,
                           `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `competences`
--

CREATE TABLE `competences` (
                               `id` int NOT NULL,
                               `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprises`
--

CREATE TABLE `entreprises` (
                               `id` int NOT NULL,
                               `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `description` text COLLATE utf8mb4_unicode_ci,
                               `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                               `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                               `created_at` datetime NOT NULL,
                               `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
--

CREATE TABLE `etudiants` (
                             `id` int NOT NULL,
                             `user_id` int NOT NULL,
                             `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `created_at` datetime NOT NULL,
                             `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                             `centre_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evaluations_entreprises`
--

CREATE TABLE `evaluations_entreprises` (
                                           `id` int NOT NULL,
                                           `entreprise_id` int NOT NULL,
                                           `etudiant_id` int NOT NULL,
                                           `note` int NOT NULL,
                                           `commentaire` text COLLATE utf8mb4_unicode_ci,
                                           `created_at` datetime NOT NULL
) ;

-- --------------------------------------------------------

--
-- Structure de la table `offres`
--

CREATE TABLE `offres` (
                          `id` int NOT NULL,
                          `titre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                          `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
                          `entreprise_id` int NOT NULL,
                          `remuneration` decimal(10,2) DEFAULT NULL,
                          `date_debut` date NOT NULL,
                          `date_fin` date NOT NULL,
                          `created_at` datetime NOT NULL,
                          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offres_competences`
--

CREATE TABLE `offres_competences` (
                                      `offre_id` int NOT NULL,
                                      `competence_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pilotes`
--

CREATE TABLE `pilotes` (
                           `id` int NOT NULL,
                           `user_id` int NOT NULL,
                           `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                           `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                           `created_at` datetime NOT NULL,
                           `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                           `centre_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pilote_etudiant`
--

CREATE TABLE `pilote_etudiant` (
                                   `pilote_id` int NOT NULL,
                                   `etudiant_id` int NOT NULL,
                                   `date_attribution` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

CREATE TABLE `role_permissions` (
                                    `id` int NOT NULL,
                                    `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `permission` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `system_logs`
--

CREATE TABLE `system_logs` (
                               `id` int NOT NULL,
                               `timestamp` datetime NOT NULL,
                               `user` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                               `action` text COLLATE utf8mb4_unicode_ci NOT NULL,
                               `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                               `level` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'INFO',
                               `context` text COLLATE utf8mb4_unicode_ci,
                               `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
                                `id` int NOT NULL,
                                `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `role` enum('admin','pilote','etudiant') COLLATE utf8mb4_unicode_ci NOT NULL,
                                `created_at` datetime NOT NULL,
                                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `wishlists`
--

CREATE TABLE `wishlists` (
                             `etudiant_id` int NOT NULL,
                             `offre_id` int NOT NULL,
                             `date_ajout` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `candidatures`
--
ALTER TABLE `candidatures`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_candidatures_etudiant` (`etudiant_id`),
  ADD KEY `idx_candidatures_offre` (`offre_id`);

--
-- Index pour la table `centres`
--
ALTER TABLE `centres`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_centres_nom` (`nom`),
  ADD KEY `idx_centres_code` (`code`);

--
-- Index pour la table `competences`
--
ALTER TABLE `competences`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `entreprises`
--
ALTER TABLE `entreprises`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entreprises_nom` (`nom`);

--
-- Index pour la table `etudiants`
--
ALTER TABLE `etudiants`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_etudiants_centre` (`centre_id`);

--
-- Index pour la table `evaluations_entreprises`
--
ALTER TABLE `evaluations_entreprises`
    ADD PRIMARY KEY (`id`),
  ADD KEY `entreprise_id` (`entreprise_id`),
  ADD KEY `etudiant_id` (`etudiant_id`);

--
-- Index pour la table `offres`
--
ALTER TABLE `offres`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_offres_entreprise` (`entreprise_id`),
  ADD KEY `idx_offres_dates` (`date_debut`,`date_fin`);

--
-- Index pour la table `offres_competences`
--
ALTER TABLE `offres_competences`
    ADD PRIMARY KEY (`offre_id`,`competence_id`),
  ADD KEY `competence_id` (`competence_id`);

--
-- Index pour la table `pilotes`
--
ALTER TABLE `pilotes`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_pilotes_centre` (`centre_id`);

--
-- Index pour la table `pilote_etudiant`
--
ALTER TABLE `pilote_etudiant`
    ADD PRIMARY KEY (`pilote_id`,`etudiant_id`),
  ADD KEY `etudiant_id` (`etudiant_id`);

--
-- Index pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_unique` (`role`,`permission`);

--
-- Index pour la table `system_logs`
--
ALTER TABLE `system_logs`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_user` (`user`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_utilisateurs_email` (`email`),
  ADD KEY `idx_utilisateurs_role` (`role`);

--
-- Index pour la table `wishlists`
--
ALTER TABLE `wishlists`
    ADD PRIMARY KEY (`etudiant_id`,`offre_id`),
  ADD KEY `offre_id` (`offre_id`),
  ADD KEY `idx_wishlists_etudiant` (`etudiant_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `candidatures`
--
ALTER TABLE `candidatures`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `centres`
--
ALTER TABLE `centres`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `competences`
--
ALTER TABLE `competences`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `entreprises`
--
ALTER TABLE `entreprises`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `etudiants`
--
ALTER TABLE `etudiants`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evaluations_entreprises`
--
ALTER TABLE `evaluations_entreprises`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `offres`
--
ALTER TABLE `offres`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pilotes`
--
ALTER TABLE `pilotes`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `system_logs`
--
ALTER TABLE `system_logs`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `candidatures`
--
ALTER TABLE `candidatures`
    ADD CONSTRAINT `candidatures_ibfk_1` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidatures_ibfk_2` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `etudiants`
--
ALTER TABLE `etudiants`
    ADD CONSTRAINT `etudiants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_etudiants_centre` FOREIGN KEY (`centre_id`) REFERENCES `centres` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `evaluations_entreprises`
--
ALTER TABLE `evaluations_entreprises`
    ADD CONSTRAINT `evaluations_entreprises_ibfk_1` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_entreprises_ibfk_2` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offres`
--
ALTER TABLE `offres`
    ADD CONSTRAINT `offres_ibfk_1` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offres_competences`
--
ALTER TABLE `offres_competences`
    ADD CONSTRAINT `offres_competences_ibfk_1` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offres_competences_ibfk_2` FOREIGN KEY (`competence_id`) REFERENCES `competences` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pilotes`
--
ALTER TABLE `pilotes`
    ADD CONSTRAINT `fk_pilotes_centre` FOREIGN KEY (`centre_id`) REFERENCES `centres` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pilotes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pilote_etudiant`
--
ALTER TABLE `pilote_etudiant`
    ADD CONSTRAINT `pilote_etudiant_ibfk_1` FOREIGN KEY (`pilote_id`) REFERENCES `pilotes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pilote_etudiant_ibfk_2` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `wishlists`
--
ALTER TABLE `wishlists`
    ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `etudiants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
