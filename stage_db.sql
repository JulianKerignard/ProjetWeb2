-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : jeu. 03 avr. 2025 à 17:31
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

--
-- Déchargement des données de la table `candidatures`
--

INSERT INTO `candidatures` (`id`, `offre_id`, `etudiant_id`, `cv`, `lettre_motivation`, `date_candidature`) VALUES
                                                                                                                (1, 1, 1, 'cv_julie_leroy.pdf', 'Je suis très intéressée par le poste de développeur web full stack car il correspond parfaitement à mon profil...', '2025-04-01 13:51:16'),
                                                                                                                (2, 2, 2, 'cv_lucas_petit.pdf', 'Actuellement en formation de designer UX/UI, je suis à la recherche d\'un stage pour mettre en pratique mes connaissances...', '2025-04-01 13:51:16'),
(3, 3, 3, 'cv_emma_moreau.pdf', 'Passionnée par le développement mobile, je souhaiterais rejoindre votre équipe pour travailler sur vos projets React Native...', '2025-04-01 13:51:16'),
(4, 4, 1, 'cv_julie_leroy.pdf', 'Ayant des compétences en analyse de données, je pense pouvoir apporter une contribution significative à votre projet...', '2025-04-01 13:51:16'),
(5, 7, 2, 'cv_lucas_petit.pdf', 'Je suis très intéressé par le développement front-end avec React. Ce stage serait une opportunité idéale pour moi...', '2025-04-01 13:51:16'),
(12, 1, 4, 'cv_4_1743578780_8427.pdf', 'testestetstett', '2025-04-02 09:26:20'),
(13, 5, 4, 'cv_4_1743578835_7771.docx', 'testtesttesttesttest', '2025-04-02 09:27:15'),
(14, 6, 4, 'cv_4_1743585340_7311.docx', 'testet_gsqdgqpsdhqs^dh', '2025-04-02 09:15:40'),
(15, 13, 4, 'cv_4_1743595740_9085.pdf', 'IOQSDFHIO¨QDHFOWSIMDOHFHIO%QDPHJIOQFDJPOIDSJPIODSFJPOJDSQFJDQF', '2025-04-02 14:09:00');

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

--
-- Déchargement des données de la table `centres`
--

INSERT INTO `centres` (`id`, `nom`, `code`, `adresse`, `created_at`, `updated_at`) VALUES
(1, 'Paris', 'PAR', '93 Boulevard de la Seine, 92000 Paris', '2025-04-02 20:55:51', '2025-04-02 20:55:51'),
(2, 'Lyon', 'LYO', '19 Avenue Guy de Collongue, 69130 Écully', '2025-04-02 20:55:51', '2025-04-02 20:55:51'),
(3, 'Arras', 'ARR', '7 Rue Diderot, 62000 Arras', '2025-04-02 20:55:51', '2025-04-02 20:55:51'),
(4, 'Strasbourg', 'STR', '2 Allée des Foulons, 67380 Lingolsheim', '2025-04-02 20:55:51', '2025-04-02 20:55:51'),
(5, 'Nancy', 'NAN', '2 Boulevard Henri Becquerel, 57970 Yutz', '2025-04-02 20:55:51', '2025-04-02 20:55:51');

-- --------------------------------------------------------

--
-- Structure de la table `competences`
--

CREATE TABLE `competences` (
  `id` int NOT NULL,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `competences`
--

INSERT INTO `competences` (`id`, `nom`, `created_at`) VALUES
(1, 'PHP', '2025-04-01 13:51:16'),
(2, 'JavaScript', '2025-04-01 13:51:16'),
(3, 'HTML/CSS', '2025-04-01 13:51:16'),
(4, 'Java', '2025-04-01 13:51:16'),
(5, 'Python', '2025-04-01 13:51:16'),
(6, 'C#', '2025-04-01 13:51:16'),
(7, 'React', '2025-04-01 13:51:16'),
(8, 'Angular', '2025-04-01 13:51:16'),
(9, 'Vue.js', '2025-04-01 13:51:16'),
(10, 'Node.js', '2025-04-01 13:51:16'),
(11, 'Laravel', '2025-04-01 13:51:16'),
(12, 'Symfony', '2025-04-01 13:51:16'),
(13, '.NET', '2025-04-01 13:51:16'),
(14, 'SQL', '2025-04-01 13:51:16'),
(15, 'NoSQL', '2025-04-01 13:51:16'),
(16, 'Git', '2025-04-01 13:51:16'),
(17, 'DevOps', '2025-04-01 13:51:16'),
(18, 'Mobile', '2025-04-01 13:51:16'),
(19, 'UX/UI', '2025-04-01 13:51:16'),
(20, 'SEO', '2025-04-01 13:51:16');

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

--
-- Déchargement des données de la table `entreprises`
--

INSERT INTO `entreprises` (`id`, `nom`, `description`, `email`, `telephone`, `created_at`, `updated_at`) VALUES
(1, 'TechSolutions', 'Entreprise spécialisée dans le développement de solutions web et mobiles.', 'contact@techsolutions.fr', '01 23 45 67 89', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(2, 'InnovaDigital', 'Agence digitale spécialisée dans l\'innovation technologique.', 'info@innovadigital.fr', '01 34 56 78 90', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
                                                                                                                (3, 'DataCorp', 'Expertise en analyse de données et intelligence artificielle.', 'contact@datacorp.fr', '01 45 67 89 01', '2025-04-01 13:51:16', '2025-04-01 20:19:46'),
                                                                                                                (4, 'WebDesign', 'Studio de création de sites web et applications.', 'hello@webdesign.fr', '01 56 78 90 12', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
                                                                                                                (5, 'MobileTech', 'Développement d\'applications mobiles iOS et Android.', 'contact@mobiletech.fr', '01 67 89 01 23', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(6, 'Momo&amp;Co', 'Entreprise de momo le choco qui fait kaka partout ', 'momo@gmail.com', '0766856390', '2025-04-01 15:14:06', '2025-04-01 15:14:06'),
(7, 'Momo&amp;Co', 'Entreprise de momo le choco qui fait kaka partout ljkndoùqfnpqùs,dsq, kdqspdùqsn', 'momo@gmail.com', '0766856390', '2025-04-01 15:14:13', '2025-04-01 15:14:13'),
(9, 'test', 'testtest', 'test@gmail.com', '0614823251', '2025-04-02 09:18:56', '2025-04-02 09:18:56'),
(10, 'MOMOCORP;net', 's;dgjlSkfjbmlsfbnùmFS,bSkf,bLSK,blk?FSBL,', 'jeanluc@gmail.com', '0601848903', '2025-04-02 09:30:02', '2025-04-02 09:30:02');

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

--
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id`, `user_id`, `nom`, `prenom`, `created_at`, `updated_at`, `centre_id`) VALUES
(1, 4, 'Leroy', 'Julie', '2025-04-01 13:51:16', '2025-04-03 08:24:42', 1),
(2, 5, 'Petit', 'Lucas', '2025-04-01 13:51:16', '2025-04-02 22:03:21', 2),
(3, 6, 'Moreau', 'Emma', '2025-04-01 13:51:16', '2025-04-03 08:32:47', 5),
(4, 8, 'momo', 'momo', '2025-04-01 21:01:50', '2025-04-03 08:31:51', 5);

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

--
-- Déchargement des données de la table `evaluations_entreprises`
--

INSERT INTO `evaluations_entreprises` (`id`, `entreprise_id`, `etudiant_id`, `note`, `commentaire`, `created_at`) VALUES
(1, 1, 1, 5, 'Très bonne expérience de stage. L\'équipe est accueillante et les projets sont intéressants.', '2025-04-01 13:51:16'),
(2, 1, 2, 4, 'Bonne ambiance de travail. Les projets sont variés et permettent d\'apprendre beaucoup.', '2025-04-01 13:51:16'),
(3, 2, 1, 5, 'test test test test', '2025-04-01 13:51:16'),
(4, 3, 3, 5, 'Excellent stage. J\'ai beaucoup appris et l\'équipe était très à l\'écoute.', '2025-04-01 13:51:16'),
(5, 4, 2, 4, 'Entreprise dynamique avec des projets innovants. Bon encadrement des stagiaires.', '2025-04-01 13:51:16'),
(9, 3, 1, 1, 'y&#039;a momo donc forcement c nullll', '2025-04-02 10:05:49'),
(10, 10, 1, 5, 'j&#039;aime bcp', '2025-04-02 09:30:16');

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

--
-- Déchargement des données de la table `offres`
--

INSERT INTO `offres` (`id`, `titre`, `description`, `entreprise_id`, `remuneration`, `date_debut`, `date_fin`, `created_at`, `updated_at`) VALUES
                                                                                                                                               (1, 'Développeur Web Full Stack', 'Nous recherchons un développeur web full stack pour participer au développement de nos applications web. Vous travaillerez sur des projets variés et innovants.', 1, 800.00, '2025-05-01', '2025-07-31', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
                                                                                                                                               (2, 'Designer UX/UI', 'Stage de conception d\'interfaces utilisateurs pour nos applications web et mobiles. Vous travaillerez en étroite collaboration avec l\'équipe de développement.', 2, 700.00, '2025-05-15', '2025-08-15', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
                                                                                                                                               (3, 'Développeur Mobile React Native', 'Développement d\'applications mobiles cross-platform avec React Native. Vous participerez à toutes les phases du projet, de la conception à la mise en production.', 3, 850.00, '2025-06-01', '2025-09-30', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(4, 'Data Analyst', 'Stage d\'analyse de données pour notre département marketing. Vous serez amené à collecter, analyser et présenter des données pour aider à la prise de décision.', 4, 900.00, '2025-06-15', '2025-09-15', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
                                                                                                                                               (5, 'Développeur Back-End PHP/Symfony', 'Développement de l\'API de notre application web principale. Vous travaillerez avec les technologies PHP, Symfony et MySQL.', 5, 750.00, '2025-07-01', '2025-10-31', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(6, 'Intégrateur Web', 'Intégration de maquettes graphiques en HTML/CSS/JavaScript. Vous travaillerez sur des sites responsive et cross-browser.', 1, 600.00, '2025-07-15', '2025-10-15', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(7, 'Développeur Front-End React', 'Développement d\'interfaces utilisateurs avec React et Redux. Vous participerez à l\'amélioration de notre application web principale.', 2, 800.00, '2025-08-01', '2025-11-30', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(8, 'DevOps Engineer', 'Stage de mise en place et gestion d\'infrastructures CI/CD. Vous travaillerez avec Docker, Kubernetes et les services cloud AWS.', 3, 950.00, '2025-08-15', '2025-11-15', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
                                                                                                                                               (9, 'Développeur Fullstack JavaScript', 'Développement d\'applications web avec Node.js et Vue.js. Stage idéal pour se familiariser avec l\'écosystème JavaScript moderne.', 4, 850.00, '2025-09-01', '2025-12-31', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
                                                                                                                                               (10, 'SEO Specialist', 'Stage d\'optimisation pour les moteurs de recherche. Vous aiderez à améliorer la visibilité de nos sites web.', 5, 700.00, '2025-09-15', '2025-12-15', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(12, 'testtestt', 'testtesttesttesttest  testtesttesttesttest  testtesttesttesttest  testtesttesttesttest  testtesttesttesttest  ', 9, 2378.00, '2025-04-07', '2025-07-25', '2025-04-02 09:19:43', '2025-04-02 09:20:25'),
(13, 'momoprout', 'pgeufhi$sdnfwdnl^f$sdpkfnsdopgeufhi$sdnfwdnl^f$sdpkfnsdopgeufhi$sdnfwdnl^f$sdpkfnsdopgeufhi$sdnfwdnl^f$sdpkfnsdo', 7, 0.00, '2023-01-03', '2025-04-02', '2025-04-02 09:42:41', '2025-04-02 09:42:41'),
(15, 'testlog', 'testlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlogtestlog', 2, 0.00, '2025-04-24', '2025-05-11', '2025-04-02 14:51:30', '2025-04-02 14:51:30');

-- --------------------------------------------------------

--
-- Structure de la table `offres_competences`
--

CREATE TABLE `offres_competences` (
  `offre_id` int NOT NULL,
  `competence_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `offres_competences`
--

INSERT INTO `offres_competences` (`offre_id`, `competence_id`) VALUES
(1, 1),
(5, 1),
(1, 2),
(3, 2),
(6, 2),
(7, 2),
(9, 2),
(1, 3),
(2, 3),
(6, 3),
(10, 3),
(12, 3),
(13, 3),
(4, 5),
(13, 6),
(3, 7),
(7, 7),
(12, 7),
(9, 9),
(9, 10),
(12, 11),
(13, 11),
(15, 11),
(5, 12),
(1, 14),
(4, 14),
(5, 14),
(4, 15),
(12, 15),
(13, 15),
(8, 16),
(8, 17),
(3, 18),
(2, 19),
(10, 20);

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

--
-- Déchargement des données de la table `pilotes`
--

INSERT INTO `pilotes` (`id`, `user_id`, `nom`, `prenom`, `created_at`, `updated_at`, `centre_id`) VALUES
(3, 7, 'krg', 'Julian', '2025-04-01 20:57:43', '2025-04-03 08:30:17', 5);

-- --------------------------------------------------------

--
-- Structure de la table `pilote_etudiant`
--

CREATE TABLE `pilote_etudiant` (
  `pilote_id` int NOT NULL,
  `etudiant_id` int NOT NULL,
  `date_attribution` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `pilote_etudiant`
--

INSERT INTO `pilote_etudiant` (`pilote_id`, `etudiant_id`, `date_attribution`) VALUES
(3, 1, '2025-04-02 21:50:43');

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

--
-- Déchargement des données de la table `system_logs`
--

INSERT INTO `system_logs` (`id`, `timestamp`, `user`, `action`, `ip`, `level`, `context`, `created_at`) VALUES
(1, '2025-04-02 12:36:57', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"stats\"}', '2025-04-02 12:36:57'),
                                                                                                                                               (2, '2025-04-02 12:36:59', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:36:59'),
(3, '2025-04-02 12:37:00', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:37:00'),
(4, '2025-04-02 12:37:00', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:37:00'),
(5, '2025-04-02 12:37:20', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:37:20'),
(6, '2025-04-02 12:37:20', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:37:20'),
(7, '2025-04-02 12:37:27', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:37:27'),
(8, '2025-04-02 12:38:25', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:38:25'),
(9, '2025-04-02 12:38:38', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:38:38'),
(10, '2025-04-02 12:38:38', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:38:38'),
(11, '2025-04-02 12:38:41', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:38:41'),
(12, '2025-04-02 12:38:41', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:38:41'),
(13, '2025-04-02 12:46:26', 'admin@web4all.fr', 'Accès au panel d\'administration', '195.25.86.161', 'INFO', '{\"section\":\"permissions\"}', '2025-04-02 12:46:26'),
(14, '2025-04-02 12:47:36', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:47:36'),
(15, '2025-04-02 12:47:36', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:47:36'),
(16, '2025-04-02 12:48:03', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:48:03'),
(17, '2025-04-02 12:48:04', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:48:04'),
(18, '2025-04-02 12:48:04', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:48:04'),
(19, '2025-04-02 12:48:06', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:48:06'),
(20, '2025-04-02 12:48:06', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:48:06'),
(21, '2025-04-02 12:48:07', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:48:07'),
(22, '2025-04-02 12:48:07', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:48:07'),
(23, '2025-04-02 12:48:17', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 12:48:17'),
(24, '2025-04-02 12:48:18', 'admin@web4all.fr', 'Accès à la page de confirmation de suppression d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":14,\"offre_titre\":\"testlog\"}', '2025-04-02 12:48:18'),
(25, '2025-04-02 12:48:19', 'admin@web4all.fr', 'Suppression d\'une offre de stage', '83.115.83.6', 'SUCCESS', '{\"id\":14,\"titre\":\"testlog\",\"entreprise_id\":3,\"entreprise_nom\":\"DataCorp\"}', '2025-04-02 12:48:19'),
(26, '2025-04-02 12:48:19', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 12:48:19'),
(27, '2025-04-02 12:48:21', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:48:21'),
(28, '2025-04-02 12:48:22', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:48:22'),
(29, '2025-04-02 12:48:22', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:48:22'),
(30, '2025-04-02 12:48:24', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:48:24'),
(31, '2025-04-02 12:48:24', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:48:24'),
(32, '2025-04-02 12:48:32', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:48:32'),
(33, '2025-04-02 12:48:34', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"maintenance\"}', '2025-04-02 12:48:34'),
(34, '2025-04-02 12:49:30', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:49:30'),
(35, '2025-04-02 12:49:34', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-02 12:49:34'),
(36, '2025-04-02 12:49:42', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-02 12:49:42'),
(37, '2025-04-02 12:49:54', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:49:54'),
(38, '2025-04-02 12:49:56', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:49:56'),
(39, '2025-04-02 12:49:56', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:49:56'),
(40, '2025-04-02 12:51:09', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:51:09'),
(41, '2025-04-02 12:51:14', 'admin@web4all.fr', 'Accès au panel d\'administration', '195.25.86.161', 'INFO', '{\"section\":\"permissions\"}', '2025-04-02 12:51:14'),
(42, '2025-04-02 12:51:30', 'admin@web4all.fr', 'Création d\'une offre de stage', '83.115.83.6', 'SUCCESS', '{\"offre_id\":\"15\",\"offre_titre\":\"testlog\",\"entreprise_id\":2,\"date_debut\":\"2025-04-24\",\"date_fin\":\"2025-05-11\",\"competences\":\"11\"}', '2025-04-02 12:51:30'),
(43, '2025-04-02 12:51:30', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":15,\"offre_titre\":\"testlog\",\"entreprise_id\":2,\"entreprise_nom\":\"InnovaDigital\"}', '2025-04-02 12:51:30'),
(44, '2025-04-02 12:51:33', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:51:33'),
(45, '2025-04-02 12:51:35', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:51:35'),
(46, '2025-04-02 12:51:35', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:51:35'),
(47, '2025-04-02 12:51:39', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:51:39'),
(48, '2025-04-02 12:51:39', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":{\"type\":\"connexion\"},\"page\":1}', '2025-04-02 12:51:39'),
(49, '2025-04-02 12:51:44', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:51:44'),
(50, '2025-04-02 12:51:44', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:51:44'),
(51, '2025-04-02 12:53:07', 'admin@web4all.fr', 'Accès au panel d\'administration', '195.25.86.161', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:53:07'),
(52, '2025-04-02 12:53:10', 'admin@web4all.fr', 'Accès au panel d\'administration', '195.25.86.161', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:53:10'),
(53, '2025-04-02 12:53:10', 'admin@web4all.fr', 'Accès au panel d\'administration', '195.25.86.161', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:53:10'),
(54, '2025-04-02 12:53:10', 'admin@web4all.fr', 'Accès au panel d\'administration', '195.25.86.161', 'INFO', '{\"section\":\"index\"}', '2025-04-02 12:53:10'),
(55, '2025-04-02 12:56:01', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:56:01'),
(56, '2025-04-02 12:56:01', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:56:01'),
(57, '2025-04-02 12:56:03', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:56:03'),
(58, '2025-04-02 12:56:03', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:56:03'),
(59, '2025-04-02 12:56:04', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 12:56:04'),
(60, '2025-04-02 12:56:04', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 12:56:04'),
(61, '2025-04-02 12:58:14', 'admin@web4all.fr', 'Accès au panel d\'administration', '195.25.86.161', 'INFO', '{\"section\":\"permissions\"}', '2025-04-02 12:58:14'),
(62, '2025-04-02 13:00:52', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 13:00:52'),
(63, '2025-04-02 13:00:52', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 13:00:52'),
(64, '2025-04-02 13:00:56', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 13:00:56'),
(65, '2025-04-02 13:00:57', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 13:00:57'),
(66, '2025-04-02 13:00:57', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 13:00:57'),
(67, '2025-04-02 13:01:29', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:01:29\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:01:29'),
(68, '2025-04-02 13:01:29', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 13:01:29'),
(69, '2025-04-02 13:01:30', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:01:30\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:01:30'),
(70, '2025-04-02 13:01:30', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 13:01:30'),
(71, '2025-04-02 13:01:30', 'admin@web4all.fr', 'TEST MANUEL - Vérification affichage des logs', '83.115.83.6', 'WARNING', '{\"test_timestamp\":\"2025-04-02 13:01:30\"}', '2025-04-02 13:01:30'),
(72, '2025-04-02 13:01:30', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 13:01:30'),
(73, '2025-04-02 13:05:38', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:05:38\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:05:38'),
(74, '2025-04-02 13:05:38', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 13:05:38'),
(75, '2025-04-02 13:05:38', 'admin@web4all.fr', 'TEST MANUEL - Vérification affichage des logs', '83.115.83.6', 'WARNING', '{\"test_timestamp\":\"2025-04-02 13:05:38\"}', '2025-04-02 13:05:38'),
(76, '2025-04-02 13:05:38', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 13:05:38'),
(77, '2025-04-02 13:06:35', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:06:35\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:06:35'),
(78, '2025-04-02 13:06:35', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 13:06:35'),
(79, '2025-04-02 13:06:36', 'admin@web4all.fr', 'TEST MANUEL - Vérification affichage des logs', '83.115.83.6', 'WARNING', '{\"test_timestamp\":\"2025-04-02 13:06:36\"}', '2025-04-02 13:06:36'),
(80, '2025-04-02 13:06:36', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 13:06:36'),
(81, '2025-04-02 13:06:45', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:06:45\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:06:45'),
(82, '2025-04-02 13:06:45', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 13:06:45'),
(83, '2025-04-02 13:06:49', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:06:49\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:06:49'),
(84, '2025-04-02 13:06:49', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 13:06:49'),
(85, '2025-04-02 13:06:53', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:06:53\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:06:53'),
(86, '2025-04-02 13:06:53', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 13:06:53'),
(87, '2025-04-02 13:06:54', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:06:54\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:06:54'),
(88, '2025-04-02 13:06:54', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 13:06:54'),
(89, '2025-04-02 13:06:54', 'admin@web4all.fr', 'TEST MANUEL - Vérification affichage des logs', '83.115.83.6', 'WARNING', '{\"test_timestamp\":\"2025-04-02 13:06:54\"}', '2025-04-02 13:06:54'),
(90, '2025-04-02 13:06:54', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 13:06:54'),
(91, '2025-04-02 13:06:56', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:06:56\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:06:56'),
(92, '2025-04-02 13:06:56', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 13:06:56'),
(93, '2025-04-02 13:07:06', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:07:06\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:07:06'),
(94, '2025-04-02 13:07:06', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-02 13:07:06'),
(95, '2025-04-02 13:07:10', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:07:10\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:07:10'),
(96, '2025-04-02 13:07:10', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 13:07:10'),
(97, '2025-04-02 13:07:12', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 13:07:12\",\"request_uri\":\"\\/index.php?page=admin&action=stats\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 13:07:12'),
(98, '2025-04-02 13:07:12', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"stats\"}', '2025-04-02 13:07:12'),
(99, '2025-04-02 13:07:29', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 13:07:29'),
(100, '2025-04-02 16:10:55', NULL, 'Tentative d\'accès non authentifiée à une action protégée: offres/statistiques', '83.115.83.6', 'WARNING', '{\"ip\":\"83.115.83.6\"}', '2025-04-02 16:10:55'),
(101, '2025-04-02 16:11:15', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:11:15\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:11:15'),
(102, '2025-04-02 16:11:15', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 16:11:15'),
(103, '2025-04-02 16:11:16', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:11:16\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:11:16'),
(104, '2025-04-02 16:11:16', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 16:11:16'),
(105, '2025-04-02 16:11:16', 'admin@web4all.fr', 'TEST MANUEL - Vérification affichage des logs', '83.115.83.6', 'WARNING', '{\"test_timestamp\":\"2025-04-02 16:11:16\"}', '2025-04-02 16:11:16'),
(106, '2025-04-02 16:11:16', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 16:11:16'),
(107, '2025-04-02 16:12:03', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:12:03\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:12:03'),
(108, '2025-04-02 16:12:03', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 16:12:03'),
(109, '2025-04-02 16:24:47', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 16:24:47'),
(110, '2025-04-02 16:24:50', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:24:50\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:24:50'),
(111, '2025-04-02 16:24:50', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 16:24:50'),
(112, '2025-04-02 16:24:51', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:24:51\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:24:51'),
(113, '2025-04-02 16:24:51', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 16:24:51'),
(114, '2025-04-02 16:24:51', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 16:24:51'),
(115, '2025-04-02 16:24:53', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:24:53\",\"request_uri\":\"\\/index.php?page=admin&action=logs&sort=action&order=asc\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:24:53'),
(116, '2025-04-02 16:24:53', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 16:24:53'),
(117, '2025-04-02 16:24:53', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 16:24:53'),
(118, '2025-04-02 16:24:55', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:24:55\",\"request_uri\":\"\\/index.php?page=admin&action=logs&sort=timestamp&order=asc\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:24:55'),
(119, '2025-04-02 16:24:55', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 16:24:55'),
(120, '2025-04-02 16:24:55', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 16:24:55'),
(121, '2025-04-02 16:24:58', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:24:58\",\"request_uri\":\"\\/index.php?page=admin&action=logs&sort=ip&order=asc\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:24:58'),
(122, '2025-04-02 16:24:58', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 16:24:58'),
(123, '2025-04-02 16:24:58', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 16:24:58'),
(124, '2025-04-02 16:24:59', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:24:59\",\"request_uri\":\"\\/index.php?page=admin&action=logs&sort=ip&order=desc\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:24:59'),
(125, '2025-04-02 16:24:59', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 16:24:59'),
(126, '2025-04-02 16:24:59', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 16:24:59'),
(127, '2025-04-02 16:25:01', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:25:01\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:25:01'),
(128, '2025-04-02 16:25:01', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 16:25:01'),
(129, '2025-04-02 16:27:06', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 16:27:06\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 16:27:06'),
(130, '2025-04-02 16:27:06', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 16:27:06'),
(131, '2025-04-02 16:27:06', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 16:27:06'),
(132, '2025-04-02 18:39:10', 'admin@web4all.fr', 'Test manuel', '127.0.0.1', 'INFO', NULL, '2025-04-02 16:39:10'),
(133, '2025-04-02 18:40:37', 'admin@web4all.fr', 'Test manuel', '127.0.0.1', 'INFO', NULL, '2025-04-02 16:40:37'),
(134, '2025-04-02 17:28:18', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:28:18'),
(135, '2025-04-02 17:28:18', 'momo@gmail.com', 'Consultation des candidatures personnelles', '91.169.248.206', 'INFO', '{\"etudiant_id\":4,\"nombre_candidatures\":4}', '2025-04-02 17:28:18'),
(136, '2025-04-02 17:28:26', 'momo@gmail.com', 'Consultation des offres de stage', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 17:28:26'),
(137, '2025-04-02 17:28:29', 'momo@gmail.com', 'Consultation de la liste des entreprises', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-02 17:28:29'),
(138, '2025-04-02 17:28:31', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:28:31'),
(139, '2025-04-02 17:28:31', 'momo@gmail.com', 'Récupération wishlist', '91.169.248.206', 'INFO', '{\"etudiant_id\":4,\"page\":1}', '2025-04-02 17:28:31'),
(140, '2025-04-02 17:28:31', 'momo@gmail.com', 'Nombre d\'éléments dans la wishlist', '91.169.248.206', 'INFO', '{\"count\":4}', '2025-04-02 17:28:31'),
(141, '2025-04-02 17:28:33', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:28:33'),
(142, '2025-04-02 17:28:33', 'momo@gmail.com', 'Consultation des candidatures personnelles', '91.169.248.206', 'INFO', '{\"etudiant_id\":4,\"nombre_candidatures\":4}', '2025-04-02 17:28:33'),
(143, '2025-04-02 17:32:37', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:32:37'),
(144, '2025-04-02 17:32:37', 'momo@gmail.com', 'Récupération wishlist', '91.169.248.206', 'INFO', '{\"etudiant_id\":4,\"page\":1}', '2025-04-02 17:32:37'),
(145, '2025-04-02 17:32:37', 'momo@gmail.com', 'Nombre d\'éléments dans la wishlist', '91.169.248.206', 'INFO', '{\"count\":4}', '2025-04-02 17:32:37'),
(146, '2025-04-02 17:32:38', 'momo@gmail.com', 'Consultation de la liste des entreprises', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-02 17:32:38'),
(147, '2025-04-02 17:32:41', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:32:41'),
(148, '2025-04-02 17:32:41', 'momo@gmail.com', 'Récupération wishlist', '91.169.248.206', 'INFO', '{\"etudiant_id\":4,\"page\":1}', '2025-04-02 17:32:41'),
(149, '2025-04-02 17:32:41', 'momo@gmail.com', 'Nombre d\'éléments dans la wishlist', '91.169.248.206', 'INFO', '{\"count\":4}', '2025-04-02 17:32:41'),
(150, '2025-04-02 17:35:17', 'momo@gmail.com', 'Consultation des offres de stage', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 17:35:17'),
(151, '2025-04-02 17:35:21', 'momo@gmail.com', 'Consultation du détail d\'une offre', '91.169.248.206', 'INFO', '{\"offre_id\":1,\"offre_titre\":\"D\\u00e9veloppeur Web Full Stack\",\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:35:21'),
(152, '2025-04-02 17:35:22', 'momo@gmail.com', 'Début méthode postuler()', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:22'),
(153, '2025-04-02 17:35:22', 'momo@gmail.com', 'ID offre: 1', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:22'),
(154, '2025-04-02 17:35:22', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:22'),
(155, '2025-04-02 17:35:22', 'momo@gmail.com', 'ID étudiant récupéré: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:22'),
(156, '2025-04-02 17:35:22', 'momo@gmail.com', 'Vérification candidature existante: oui', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:22'),
(157, '2025-04-02 17:35:22', 'momo@gmail.com', 'Candidature déjà existante', '91.169.248.206', 'WARNING', '{\"offre_id\":1,\"etudiant_id\":4}', '2025-04-02 17:35:22'),
(158, '2025-04-02 17:35:22', 'momo@gmail.com', 'Consultation du détail d\'une offre', '91.169.248.206', 'INFO', '{\"offre_id\":1,\"offre_titre\":\"D\\u00e9veloppeur Web Full Stack\",\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:35:22'),
(159, '2025-04-02 17:35:28', 'momo@gmail.com', 'Consultation du détail d\'une offre', '91.169.248.206', 'INFO', '{\"offre_id\":1,\"offre_titre\":\"D\\u00e9veloppeur Web Full Stack\",\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:35:28'),
(160, '2025-04-02 17:35:29', 'momo@gmail.com', 'Consultation des offres de stage', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 17:35:29'),
(161, '2025-04-02 17:35:32', 'momo@gmail.com', 'Consultation du détail d\'une offre', '91.169.248.206', 'INFO', '{\"offre_id\":5,\"offre_titre\":\"D\\u00e9veloppeur Back-End PHP\\/Symfony\",\"entreprise_id\":5,\"entreprise_nom\":\"MobileTech\"}', '2025-04-02 17:35:32'),
(162, '2025-04-02 17:35:41', 'momo@gmail.com', 'Début méthode postuler()', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:41'),
(163, '2025-04-02 17:35:41', 'momo@gmail.com', 'ID offre: 5', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:41'),
(164, '2025-04-02 17:35:41', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:41'),
(165, '2025-04-02 17:35:41', 'momo@gmail.com', 'ID étudiant récupéré: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:41'),
(166, '2025-04-02 17:35:41', 'momo@gmail.com', 'Vérification candidature existante: oui', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:41'),
(167, '2025-04-02 17:35:41', 'momo@gmail.com', 'Candidature déjà existante', '91.169.248.206', 'WARNING', '{\"offre_id\":5,\"etudiant_id\":4}', '2025-04-02 17:35:41'),
(168, '2025-04-02 17:35:41', 'momo@gmail.com', 'Consultation du détail d\'une offre', '91.169.248.206', 'INFO', '{\"offre_id\":5,\"offre_titre\":\"D\\u00e9veloppeur Back-End PHP\\/Symfony\",\"entreprise_id\":5,\"entreprise_nom\":\"MobileTech\"}', '2025-04-02 17:35:41'),
(169, '2025-04-02 17:35:45', 'momo@gmail.com', 'Consultation du détail d\'une offre', '91.169.248.206', 'INFO', '{\"offre_id\":5,\"offre_titre\":\"D\\u00e9veloppeur Back-End PHP\\/Symfony\",\"entreprise_id\":5,\"entreprise_nom\":\"MobileTech\"}', '2025-04-02 17:35:45'),
(170, '2025-04-02 17:35:46', 'momo@gmail.com', 'Consultation des offres de stage', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 17:35:46'),
(171, '2025-04-02 17:35:48', 'momo@gmail.com', 'Consultation du détail d\'une offre', '91.169.248.206', 'INFO', '{\"offre_id\":15,\"offre_titre\":\"testlog\",\"entreprise_id\":2,\"entreprise_nom\":\"InnovaDigital\"}', '2025-04-02 17:35:48'),
(172, '2025-04-02 17:35:49', 'momo@gmail.com', 'Début méthode postuler()', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:49'),
(173, '2025-04-02 17:35:49', 'momo@gmail.com', 'ID offre: 15', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:49'),
(174, '2025-04-02 17:35:49', 'momo@gmail.com', 'Utilisation de l\'ID étudiant en cache: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:49'),
(175, '2025-04-02 17:35:49', 'momo@gmail.com', 'ID étudiant récupéré: 4', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:49'),
(176, '2025-04-02 17:35:49', 'momo@gmail.com', 'Vérification candidature existante: non', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:49'),
(177, '2025-04-02 17:35:49', 'momo@gmail.com', 'Accès au formulaire de candidature', '91.169.248.206', 'INFO', '{\"method\":\"GET\",\"offre_id\":15,\"offre_titre\":\"testlog\",\"entreprise_nom\":\"InnovaDigital\"}', '2025-04-02 17:35:49'),
(178, '2025-04-02 17:35:49', 'momo@gmail.com', 'Fin méthode postuler()', '91.169.248.206', 'INFO', NULL, '2025-04-02 17:35:49'),
(179, '2025-04-02 17:47:40', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:47:40\",\"request_uri\":\"\\/index.php?page=admin&action=stats\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:47:40'),
(180, '2025-04-02 17:47:40', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"stats\"}', '2025-04-02 17:47:40'),
(181, '2025-04-02 17:47:42', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:47:42\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:47:42'),
(182, '2025-04-02 17:47:42', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:47:42'),
(183, '2025-04-02 17:47:43', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:47:43\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:47:43'),
(184, '2025-04-02 17:47:43', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 17:47:43'),
(185, '2025-04-02 17:47:43', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 17:47:43'),
(186, '2025-04-02 17:47:59', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:47:59\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:47:59'),
(187, '2025-04-02 17:47:59', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 17:47:59'),
(188, '2025-04-02 17:47:59', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 17:47:59'),
(189, '2025-04-02 17:48:58', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:48:58\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:48:58'),
(190, '2025-04-02 17:48:58', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:48:58'),
(191, '2025-04-02 17:50:38', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:50:38\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:50:38'),
(192, '2025-04-02 17:50:38', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 17:50:38'),
(193, '2025-04-02 17:50:38', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 17:50:38'),
(194, '2025-04-02 17:50:57', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:50:57\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:50:57'),
(195, '2025-04-02 17:50:57', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:50:57'),
(196, '2025-04-02 17:50:59', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:50:59\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:50:59'),
(197, '2025-04-02 17:50:59', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 17:50:59'),
(198, '2025-04-02 17:50:59', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 17:50:59'),
(199, '2025-04-02 17:51:39', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-02 17:51:39'),
(200, '2025-04-02 17:51:45', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:51:45\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:51:45'),
(201, '2025-04-02 17:51:45', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:51:45'),
(202, '2025-04-02 17:51:47', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:51:47\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:51:47'),
(203, '2025-04-02 17:51:47', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 17:51:47'),
(204, '2025-04-02 17:51:47', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 17:51:47'),
(205, '2025-04-02 17:51:56', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:51:56\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:51:56'),
(206, '2025-04-02 17:51:56', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:51:56'),
(207, '2025-04-02 17:51:59', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:51:59\",\"request_uri\":\"\\/index.php?page=admin&action=maintenance\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:51:59'),
(208, '2025-04-02 17:51:59', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"maintenance\"}', '2025-04-02 17:51:59'),
(209, '2025-04-02 17:52:17', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:52:17\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:52:17'),
(210, '2025-04-02 17:52:17', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:52:17'),
(211, '2025-04-02 17:52:20', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:52:20\",\"request_uri\":\"\\/index.php?page=admin&action=stats\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:52:20'),
(212, '2025-04-02 17:52:20', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"stats\"}', '2025-04-02 17:52:20'),
(213, '2025-04-02 17:52:38', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:52:38\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:52:38'),
(214, '2025-04-02 17:52:38', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:52:38'),
(215, '2025-04-02 17:52:42', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:52:42\",\"request_uri\":\"\\/index.php?page=admin&action=stats\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:52:42'),
(216, '2025-04-02 17:52:42', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"stats\"}', '2025-04-02 17:52:42'),
(217, '2025-04-02 17:52:44', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:52:44\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:52:44'),
(218, '2025-04-02 17:52:44', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 17:52:44'),
(219, '2025-04-02 17:52:46', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 17:52:46\",\"request_uri\":\"\\/index.php?page=admin&action=maintenance\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 17:52:46'),
(220, '2025-04-02 17:52:46', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"maintenance\"}', '2025-04-02 17:52:46'),
(221, '2025-04-02 17:54:47', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":1,\"offre_titre\":\"D\\u00e9veloppeur Web Full Stack\",\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:54:47'),
(222, '2025-04-02 17:54:49', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:54:49'),
(223, '2025-04-02 17:55:00', 'admin@web4all.fr', 'Accès au formulaire d\'évaluation d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:55:00'),
(224, '2025-04-02 17:55:02', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:55:02'),
(225, '2025-04-02 17:55:03', 'admin@web4all.fr', 'Accès au formulaire de modification d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:55:03'),
(226, '2025-04-02 17:55:09', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-02 17:55:09'),
(227, '2025-04-02 18:02:01', 'admin@web4all.fr', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-02 18:02:01'),
(228, '2025-04-02 19:30:26', 'admin@web4all.fr', 'Consultation des détails d\'un pilote', '83.115.83.6', 'INFO', '{\"pilote_id\":3,\"pilote_nom\":\"krg\"}', '2025-04-02 19:30:26'),
(229, '2025-04-02 19:30:27', 'admin@web4all.fr', 'Consultation des étudiants assignés à un pilote', '83.115.83.6', 'INFO', '{\"pilote_id\":3,\"pilote_nom\":\"krg\",\"nb_etudiants\":0}', '2025-04-02 19:30:27'),
(230, '2025-04-02 19:50:43', 'admin@web4all.fr', 'Attribution d\'étudiants à un pilote', '83.115.83.6', 'SUCCESS', '{\"pilote_id\":3,\"pilote_nom\":\"krg\",\"nb_etudiants\":1}', '2025-04-02 19:50:43'),
(231, '2025-04-02 19:50:43', 'admin@web4all.fr', 'Consultation des étudiants assignés à un pilote', '83.115.83.6', 'INFO', '{\"pilote_id\":3,\"pilote_nom\":\"krg\",\"nb_etudiants\":1}', '2025-04-02 19:50:43'),
(232, '2025-04-02 19:50:57', 'admin@web4all.fr', 'Consultation des étudiants assignés à un pilote', '83.115.83.6', 'INFO', '{\"pilote_id\":3,\"pilote_nom\":\"krg\",\"nb_etudiants\":1}', '2025-04-02 19:50:57'),
(233, '2025-04-02 19:51:13', 'admin@web4all.fr', 'Consultation des détails d\'un pilote', '83.115.83.6', 'INFO', '{\"pilote_id\":3,\"pilote_nom\":\"krg\"}', '2025-04-02 19:51:13'),
(234, '2025-04-02 19:51:18', 'admin@web4all.fr', 'Modification d\'un pilote', '83.115.83.6', 'SUCCESS', '{\"pilote_id\":3,\"pilote_nom\":\"krg\",\"pilote_centre_id\":5}', '2025-04-02 19:51:18'),
(235, '2025-04-02 19:51:34', 'admin@web4all.fr', 'Modification d\'un pilote', '83.115.83.6', 'SUCCESS', '{\"pilote_id\":3,\"pilote_nom\":\"krg\",\"pilote_centre_id\":5}', '2025-04-02 19:51:34'),
(236, '2025-04-02 20:10:05', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 20:10:05\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 20:10:05'),
(237, '2025-04-02 20:10:05', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-02 20:10:05'),
(238, '2025-04-02 20:10:06', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 20:10:06\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 20:10:06'),
(239, '2025-04-02 20:10:06', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 20:10:06'),
(240, '2025-04-02 20:10:06', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 20:10:06'),
(241, '2025-04-02 20:10:15', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 20:10:15\",\"request_uri\":\"\\/index.php?page=admin&action=add-test-logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 20:10:15'),
(242, '2025-04-02 20:10:15', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"add-test-logs\"}', '2025-04-02 20:10:15'),
(243, '2025-04-02 22:10:15', 'admin@web4all.fr', 'Test log insertion', '83.115.83.6', 'INFO', NULL, '2025-04-02 20:10:15');
INSERT INTO `system_logs` (`id`, `timestamp`, `user`, `action`, `ip`, `level`, `context`, `created_at`) VALUES
(244, '2025-04-02 22:10:15', 'admin@web4all.fr', 'Consultation des logs de test', '83.115.83.6', 'INFO', NULL, '2025-04-02 20:10:15'),
(245, '2025-04-02 22:10:15', 'admin@web4all.fr', 'Action de test réussie', '83.115.83.6', 'SUCCESS', NULL, '2025-04-02 20:10:15'),
(246, '2025-04-02 20:10:17', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-02 20:10:17\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-02 20:10:17'),
(247, '2025-04-02 20:10:17', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-02 20:10:17'),
(248, '2025-04-02 20:10:17', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-02 20:10:17'),
(249, '2025-04-02 20:27:13', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-02 20:27:13'),
(250, '2025-04-02 20:27:16', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-02 20:27:16'),
(251, '2025-04-02 20:27:19', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-02 20:27:19'),
(252, '2025-04-02 20:27:21', 'admin@web4all.fr', 'Consultation des détails d\'un étudiant', '83.115.83.6', 'INFO', '{\"etudiant_id\":1,\"etudiant_nom\":\"Leroy Julie\"}', '2025-04-02 20:27:21'),
(253, '2025-04-03 06:21:38', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:21:38\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:21:38'),
(254, '2025-04-03 06:21:38', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 06:21:38'),
(255, '2025-04-03 06:21:39', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:21:39\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:21:39'),
(256, '2025-04-03 06:21:39', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-03 06:21:39'),
(257, '2025-04-03 06:21:39', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-03 06:21:39'),
(258, '2025-04-03 06:21:44', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:21:44\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:21:44'),
(259, '2025-04-03 06:21:44', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 06:21:44'),
(260, '2025-04-03 06:21:46', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:21:46\",\"request_uri\":\"\\/index.php?page=admin&action=maintenance\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:21:46'),
(261, '2025-04-03 06:21:46', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"maintenance\"}', '2025-04-03 06:21:46'),
(262, '2025-04-03 06:21:53', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:21:53\",\"request_uri\":\"\\/index.php?page=admin&action=logs\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:21:53'),
(263, '2025-04-03 06:21:53', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"logs\"}', '2025-04-03 06:21:53'),
(264, '2025-04-03 06:21:54', 'admin@web4all.fr', 'Consultation des journaux d\'activité', '83.115.83.6', 'INFO', '{\"filters\":[],\"page\":1}', '2025-04-03 06:21:54'),
(265, '2025-04-03 06:21:55', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:21:55\",\"request_uri\":\"\\/index.php?page=admin&action=maintenance\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:21:55'),
(266, '2025-04-03 06:21:55', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"maintenance\"}', '2025-04-03 06:21:55'),
(267, '2025-04-03 06:22:23', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-03 06:22:23'),
(268, '2025-04-03 06:24:35', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-03 06:24:35'),
(269, '2025-04-03 06:24:36', 'admin@web4all.fr', 'Consultation des détails d\'un étudiant', '83.115.83.6', 'INFO', '{\"etudiant_id\":1,\"etudiant_nom\":\"Leroy Julie\"}', '2025-04-03 06:24:36'),
(270, '2025-04-03 06:24:38', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-03 06:24:38'),
(271, '2025-04-03 06:24:42', 'admin@web4all.fr', 'Modification d\'un étudiant', '83.115.83.6', 'SUCCESS', '{\"etudiant_id\":1,\"etudiant_nom\":\"Leroy Julie\",\"etudiant_centre_id\":1}', '2025-04-03 06:24:42'),
(272, '2025-04-03 06:30:17', 'admin@web4all.fr', 'Modification d\'un pilote', '83.115.83.6', 'SUCCESS', '{\"pilote_id\":3,\"pilote_nom\":\"krg\",\"pilote_centre_id\":5}', '2025-04-03 06:30:17'),
(273, '2025-04-03 06:30:27', 'jujukerignard@gmail.com', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":{\"pilote_centre_id\":5},\"results_count\":1}', '2025-04-03 06:30:27'),
(274, '2025-04-03 06:30:37', 'jujukerignard@gmail.com', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":{\"pilote_centre_id\":5},\"results_count\":1}', '2025-04-03 06:30:37'),
(275, '2025-04-03 06:31:42', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-03 06:31:42'),
(276, '2025-04-03 06:31:44', 'admin@web4all.fr', 'Consultation des détails d\'un étudiant', '83.115.83.6', 'INFO', '{\"etudiant_id\":4,\"etudiant_nom\":\"momo momo\"}', '2025-04-03 06:31:44'),
(277, '2025-04-03 06:31:51', 'admin@web4all.fr', 'Modification d\'un étudiant', '83.115.83.6', 'SUCCESS', '{\"etudiant_id\":4,\"etudiant_nom\":\"momo momo\",\"etudiant_centre_id\":5}', '2025-04-03 06:31:51'),
(278, '2025-04-03 06:31:56', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-03 06:31:56'),
(279, '2025-04-03 06:32:47', 'admin@web4all.fr', 'Modification d\'un étudiant', '83.115.83.6', 'SUCCESS', '{\"etudiant_id\":3,\"etudiant_nom\":\"Moreau Emma\",\"etudiant_centre_id\":5}', '2025-04-03 06:32:47'),
(280, '2025-04-03 06:32:56', 'jujukerignard@gmail.com', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":{\"pilote_centre_id\":5},\"results_count\":2}', '2025-04-03 06:32:56'),
(281, '2025-04-03 06:36:45', 'jujukerignard@gmail.com', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":{\"pilote_centre_id\":5},\"results_count\":2}', '2025-04-03 06:36:45'),
(282, '2025-04-03 06:37:36', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 06:37:36'),
(283, '2025-04-03 06:39:56', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:39:56\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:39:56'),
(284, '2025-04-03 06:39:56', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 06:39:56'),
(285, '2025-04-03 06:40:15', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 06:40:15\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 06:40:15'),
(286, '2025-04-03 06:40:15', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 06:40:15'),
(287, '2025-04-03 06:40:43', 'admin@web4all.fr', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 06:40:43'),
(288, '2025-04-03 06:41:40', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 06:41:40'),
(289, '2025-04-03 06:54:06', 'admin@web4all.fr', 'Recherche avancée d\'entreprises', '83.115.83.6', 'INFO', '{\"filters\":[],\"results_count\":9}', '2025-04-03 06:54:06'),
(290, '2025-04-03 06:54:15', 'admin@web4all.fr', 'Recherche avancée d\'entreprises', '83.115.83.6', 'INFO', '{\"filters\":{\"order_by\":\"e.nom\",\"order_dir\":\"DESC\"},\"results_count\":9}', '2025-04-03 06:54:15'),
(291, '2025-04-03 06:54:21', 'admin@web4all.fr', 'Recherche avancée d\'entreprises', '83.115.83.6', 'INFO', '{\"filters\":{\"order_by\":\"e.nom\",\"order_dir\":\"ASC\"},\"results_count\":9}', '2025-04-03 06:54:21'),
(292, '2025-04-03 06:54:30', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":3,\"entreprise_nom\":\"DataCorp\"}', '2025-04-03 06:54:30'),
(293, '2025-04-03 07:23:44', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 07:23:44'),
(294, '2025-04-03 07:23:46', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":13,\"offre_titre\":\"momoprout\",\"entreprise_id\":7,\"entreprise_nom\":\"Momo&amp;Co\"}', '2025-04-03 07:23:46'),
(295, '2025-04-03 07:23:49', 'admin@web4all.fr', 'Recherche avancée d\'offres', '83.115.83.6', 'INFO', '{\"filters\":{\"page\":\"offres\",\"action\":\"rechercher\",\"competence_id\":\"6\"}}', '2025-04-03 07:23:49'),
(296, '2025-04-03 07:23:49', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":{\"competence_id\":6},\"results_count\":1}', '2025-04-03 07:23:49'),
(297, '2025-04-03 07:23:50', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":13,\"offre_titre\":\"momoprout\",\"entreprise_id\":7,\"entreprise_nom\":\"Momo&amp;Co\"}', '2025-04-03 07:23:50'),
(298, '2025-04-03 07:23:53', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 07:23:53\",\"request_uri\":\"\\/index.php?page=admin&action=stats\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 07:23:53'),
(299, '2025-04-03 07:23:53', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"stats\"}', '2025-04-03 07:23:53'),
(300, '2025-04-03 07:24:10', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 07:24:10\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 07:24:10'),
(301, '2025-04-03 07:24:10', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 07:24:10'),
(302, '2025-04-03 07:24:12', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 07:24:12\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 07:24:12'),
(303, '2025-04-03 07:24:12', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 07:24:12'),
(304, '2025-04-03 07:36:45', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 07:36:45'),
(305, '2025-04-03 07:36:50', 'admin@web4all.fr', 'Consultation des statistiques des offres', '83.115.83.6', 'INFO', NULL, '2025-04-03 07:36:50'),
(306, '2025-04-03 07:36:54', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":1,\"offre_titre\":\"D\\u00e9veloppeur Web Full Stack\",\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-03 07:36:54'),
(307, '2025-04-03 07:37:01', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":1,\"entreprise_nom\":\"TechSolutions\"}', '2025-04-03 07:37:01'),
(308, '2025-04-03 07:39:25', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 07:39:25'),
(309, '2025-04-03 07:39:27', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":15,\"offre_titre\":\"testlog\",\"entreprise_id\":2,\"entreprise_nom\":\"InnovaDigital\"}', '2025-04-03 07:39:27'),
(310, '2025-04-03 07:39:44', 'admin@web4all.fr', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 07:39:44'),
(311, '2025-04-03 07:39:45', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":3,\"entreprise_nom\":\"DataCorp\"}', '2025-04-03 07:39:45'),
(312, '2025-04-03 11:45:40', 'admin@web4all.fr', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 11:45:40'),
(313, '2025-04-03 11:45:41', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":3,\"entreprise_nom\":\"DataCorp\"}', '2025-04-03 11:45:41'),
(314, '2025-04-03 11:47:02', 'admin@web4all.fr', 'Consultation de la liste des entreprises', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 11:47:02'),
(315, '2025-04-03 11:47:04', 'admin@web4all.fr', 'Consultation des offres de stage', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 11:47:04'),
(316, '2025-04-03 11:47:12', 'admin@web4all.fr', 'Consultation des statistiques des offres', '91.169.248.206', 'INFO', NULL, '2025-04-03 11:47:12'),
(317, '2025-04-03 11:47:30', 'admin@web4all.fr', 'Consultation de la liste des étudiants', '91.169.248.206', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":4}', '2025-04-03 11:47:30'),
(318, '2025-04-03 12:32:19', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 12:32:19'),
(319, '2025-04-03 12:32:20', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":15,\"offre_titre\":\"testlog\",\"entreprise_id\":2,\"entreprise_nom\":\"InnovaDigital\"}', '2025-04-03 12:32:20'),
(320, '2025-04-03 12:41:11', 'admin@web4all.fr', 'Consultation du détail d\'une offre', '83.115.83.6', 'INFO', '{\"offre_id\":15,\"offre_titre\":\"testlog\",\"entreprise_id\":2,\"entreprise_nom\":\"InnovaDigital\"}', '2025-04-03 12:41:11'),
(321, '2025-04-03 12:41:14', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 12:41:14'),
(322, '2025-04-03 14:04:24', NULL, 'Tentative d\'accès non authentifiée à une action protégée: entreprises/rechercher', '91.169.248.206', 'WARNING', '{\"ip\":\"91.169.248.206\"}', '2025-04-03 14:04:24'),
(323, '2025-04-03 14:04:52', NULL, 'Tentative d\'accès non authentifiée à une action protégée: entreprises/rechercher', '91.169.248.206', 'WARNING', '{\"ip\":\"91.169.248.206\"}', '2025-04-03 14:04:52'),
(324, '2025-04-03 14:10:59', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '37.165.206.167', 'INFO', '{\"timestamp\":\"2025-04-03 14:10:59\",\"request_uri\":\"\\/index.php?page=admin&action=stats\",\"user_agent\":\"Mozilla\\/5.0 (iPhone; CPU iPhone OS 18_3_1 like Mac OS X) AppleWebKit\\/605.1.15 (KHTML, like Gecko) Version\\/18.3 Mobile\\/15E148 Safari\\/604.1\"}', '2025-04-03 14:10:59'),
(325, '2025-04-03 14:10:59', 'admin@web4all.fr', 'Accès au panel d\'administration', '37.165.206.167', 'INFO', '{\"section\":\"stats\"}', '2025-04-03 14:10:59'),
(326, '2025-04-03 14:11:09', 'admin@web4all.fr', 'Consultation des statistiques des offres', '37.165.206.167', 'INFO', NULL, '2025-04-03 14:11:09'),
(327, '2025-04-03 16:29:56', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:29:56\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:29:56'),
(328, '2025-04-03 16:29:56', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 16:29:56'),
(329, '2025-04-03 16:29:58', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:29:58\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:29:58'),
(330, '2025-04-03 16:29:58', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:29:58'),
(331, '2025-04-03 16:39:16', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 16:39:16'),
(332, '2025-04-03 16:39:20', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:39:20\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:39:20'),
(333, '2025-04-03 16:39:20', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 16:39:20'),
(334, '2025-04-03 16:39:28', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:39:28\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:39:28'),
(335, '2025-04-03 16:39:28', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:39:28'),
(336, '2025-04-03 16:43:28', 'jujukerignard@gmail.com', 'Consultation de la liste des étudiants', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":{\"pilote_centre_id\":5},\"results_count\":2}', '2025-04-03 16:43:28'),
(337, '2025-04-03 16:43:46', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:43:46\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:43:46'),
(338, '2025-04-03 16:43:46', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 16:43:46'),
(339, '2025-04-03 16:43:47', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:43:47\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:43:47'),
(340, '2025-04-03 16:43:47', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:43:47'),
(341, '2025-04-03 16:43:56', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:43:56\",\"request_uri\":\"\\/index.php?page=admin&action=save-permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:43:56'),
(342, '2025-04-03 16:43:56', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"save-permissions\"}', '2025-04-03 16:43:56'),
(343, '2025-04-03 16:48:20', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:48:20\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:48:20'),
(344, '2025-04-03 16:48:20', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 16:48:20'),
(345, '2025-04-03 16:48:22', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:48:22\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:48:22'),
(346, '2025-04-03 16:48:22', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:48:22'),
(347, '2025-04-03 16:48:25', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:48:25\",\"request_uri\":\"\\/index.php?page=admin&action=save-permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:48:25'),
(348, '2025-04-03 16:48:25', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"save-permissions\"}', '2025-04-03 16:48:25'),
(349, '2025-04-03 16:49:15', 'admin@web4all.fr', 'Modification des permissions', '83.115.83.6', 'SUCCESS', '{\"roles\":[\"pilote\",\"etudiant\"]}', '2025-04-03 16:49:15'),
(350, '2025-04-03 16:49:18', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:49:18\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:49:18'),
(351, '2025-04-03 16:49:19', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 16:49:19'),
(352, '2025-04-03 16:49:20', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:49:20\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:49:20'),
(353, '2025-04-03 16:49:20', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:49:20'),
(354, '2025-04-03 16:49:23', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:49:23\",\"request_uri\":\"\\/index.php?page=admin&action=save-permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:49:23'),
(355, '2025-04-03 16:49:23', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"save-permissions\"}', '2025-04-03 16:49:23'),
(356, '2025-04-03 16:49:47', 'admin@web4all.fr', 'Modification des permissions', '83.115.83.6', 'SUCCESS', '{\"roles\":[\"pilote\",\"etudiant\"]}', '2025-04-03 16:49:47'),
(357, '2025-04-03 16:50:13', 'admin@web4all.fr', 'Modification des permissions', '83.115.83.6', 'SUCCESS', '{\"roles\":[\"pilote\",\"etudiant\"]}', '2025-04-03 16:50:13'),
(358, '2025-04-03 16:50:13', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:50:13\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:50:13'),
(359, '2025-04-03 16:50:13', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:50:13'),
(360, '2025-04-03 16:52:16', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:52:16\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36 Edg\\/134.0.0.0\"}', '2025-04-03 16:52:16'),
(361, '2025-04-03 16:52:16', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:52:16'),
(362, '2025-04-03 16:52:54', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:52:54\",\"request_uri\":\"\\/index.php?page=admin\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:52:54'),
(363, '2025-04-03 16:52:54', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"index\"}', '2025-04-03 16:52:54'),
(364, '2025-04-03 16:52:55', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:52:55\",\"request_uri\":\"\\/index.php?page=admin&action=permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:52:55'),
(365, '2025-04-03 16:52:55', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"permissions\"}', '2025-04-03 16:52:55'),
(366, '2025-04-03 16:53:01', 'admin@web4all.fr', 'AUDIT: Initialisation du système de logs', '83.115.83.6', 'INFO', '{\"timestamp\":\"2025-04-03 16:53:01\",\"request_uri\":\"\\/index.php?page=admin&action=save-permissions\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/134.0.0.0 Safari\\/537.36\"}', '2025-04-03 16:53:01'),
(367, '2025-04-03 16:53:01', 'admin@web4all.fr', 'Accès au panel d\'administration', '83.115.83.6', 'INFO', '{\"section\":\"save-permissions\"}', '2025-04-03 16:53:01'),
(368, '2025-04-03 16:56:21', 'admin@web4all.fr', 'Modification des permissions', '83.115.83.6', 'SUCCESS', '{\"roles\":[\"pilote\",\"etudiant\"]}', '2025-04-03 16:56:21'),
(369, '2025-04-03 16:56:21', 'admin@web4all.fr', 'Consultation des offres de stage', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":10}', '2025-04-03 16:56:21'),
(370, '2025-04-03 16:56:52', 'admin@web4all.fr', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 16:56:52'),
(371, '2025-04-03 16:56:53', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":3,\"entreprise_nom\":\"DataCorp\"}', '2025-04-03 16:56:53'),
(372, '2025-04-03 16:57:15', 'admin@web4all.fr', 'Consultation du détail d\'une entreprise', '83.115.83.6', 'INFO', '{\"entreprise_id\":3,\"entreprise_nom\":\"DataCorp\"}', '2025-04-03 16:57:15'),
(373, '2025-04-03 17:28:51', 'jujukerignard@gmail.com', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 17:28:51'),
(374, '2025-04-03 17:28:55', 'jujukerignard@gmail.com', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 17:28:55'),
(375, '2025-04-03 17:29:11', 'jujukerignard@gmail.com', 'Consultation de la liste des entreprises', '83.115.83.6', 'INFO', '{\"page\":1,\"filters\":[],\"results_count\":9}', '2025-04-03 17:29:11');

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

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin@web4all.fr', '$2y$10$w702Wg/IHNhUyr9o2HCgF.y.ViY5KuGwHjBiiS29V8jQK0x3G9pSG', 'admin', '2025-04-01 13:51:16', '2025-04-03 18:56:49'),
(4, 'etudiant1@web4all.fr', '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy', 'etudiant', '2025-04-01 13:51:16', '2025-04-03 08:24:42'),
(5, 'etudiant2@web4all.fr', '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy', 'etudiant', '2025-04-01 13:51:16', '2025-04-01 13:51:16'),
(6, 'etudiant3@web4all.fr', '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy', 'etudiant', '2025-04-01 13:51:16', '2025-04-03 08:32:47'),
(7, 'jujukerignard@gmail.com', '$2y$10$XSMQBTp0cDfo/IoNG65r5.hdpFA.c2EIGeQ0N4uoPBGbvV6G.w9WK', 'pilote', '2025-04-01 20:57:43', '2025-04-03 08:30:17'),
(8, 'momo@gmail.com', '$2y$12$e.s2IOACmiTC/2vsYiK3g.9PAraxXZpYgw5je7zXesC24fSqjHNci', 'etudiant', '2025-04-01 21:01:50', '2025-04-03 08:31:51');

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
-- Déchargement des données de la table `wishlists`
--

INSERT INTO `wishlists` (`etudiant_id`, `offre_id`, `date_ajout`) VALUES
(1, 1, '2025-04-01 23:21:30'),
(1, 5, '2025-04-01 13:51:16'),
(1, 9, '2025-04-01 13:51:16'),
(2, 6, '2025-04-01 13:51:16'),
(2, 10, '2025-04-01 13:51:16'),
(3, 1, '2025-04-01 13:51:16'),
(3, 8, '2025-04-01 13:51:16'),
(4, 1, '2025-04-02 09:38:33'),
(4, 2, '2025-04-02 09:27:51'),
(4, 3, '2025-04-02 09:46:09'),
(4, 4, '2025-04-02 09:38:23');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `centres`
--
ALTER TABLE `centres`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `competences`
--
ALTER TABLE `competences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `entreprises`
--
ALTER TABLE `entreprises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `etudiants`
--
ALTER TABLE `etudiants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `evaluations_entreprises`
--
ALTER TABLE `evaluations_entreprises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `offres`
--
ALTER TABLE `offres`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `pilotes`
--
ALTER TABLE `pilotes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=376;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
