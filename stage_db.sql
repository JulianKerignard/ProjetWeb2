-- Base de données pour le projet de gestion des stages
-- Version: 1.0

-- Supprimer la base si elle existe déjà (à commenter en production)
DROP DATABASE IF EXISTS stages_db;

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS stages_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE stages_db;

-- Table des utilisateurs
CREATE TABLE utilisateurs (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              email VARCHAR(100) UNIQUE NOT NULL,
                              password VARCHAR(255) NOT NULL,
                              role ENUM('admin', 'pilote', 'etudiant') NOT NULL,
                              created_at DATETIME NOT NULL,
                              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des pilotes
CREATE TABLE pilotes (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         user_id INT NOT NULL,
                         nom VARCHAR(50) NOT NULL,
                         prenom VARCHAR(50) NOT NULL,
                         created_at DATETIME NOT NULL,
                         updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des étudiants
CREATE TABLE etudiants (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           user_id INT NOT NULL,
                           nom VARCHAR(50) NOT NULL,
                           prenom VARCHAR(50) NOT NULL,
                           created_at DATETIME NOT NULL,
                           updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                           FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des entreprises
CREATE TABLE entreprises (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             nom VARCHAR(100) NOT NULL,
                             description TEXT,
                             email VARCHAR(100),
                             telephone VARCHAR(20),
                             created_at DATETIME NOT NULL,
                             updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des évaluations d'entreprises
CREATE TABLE evaluations_entreprises (
                                         id INT AUTO_INCREMENT PRIMARY KEY,
                                         entreprise_id INT NOT NULL,
                                         etudiant_id INT NOT NULL,
                                         note INT NOT NULL CHECK (note BETWEEN 1 AND 5),
                                         commentaire TEXT,
                                         created_at DATETIME NOT NULL,
                                         FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE,
                                         FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des compétences
CREATE TABLE competences (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             nom VARCHAR(50) NOT NULL UNIQUE,
                             created_at DATETIME NOT NULL
) ENGINE=InnoDB;

-- Table des offres de stage
CREATE TABLE offres (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        titre VARCHAR(100) NOT NULL,
                        description TEXT NOT NULL,
                        entreprise_id INT NOT NULL,
                        remuneration DECIMAL(10, 2),
                        date_debut DATE NOT NULL,
                        date_fin DATE NOT NULL,
                        created_at DATETIME NOT NULL,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table de relation entre offres et compétences
CREATE TABLE offres_competences (
                                    offre_id INT NOT NULL,
                                    competence_id INT NOT NULL,
                                    PRIMARY KEY (offre_id, competence_id),
                                    FOREIGN KEY (offre_id) REFERENCES offres(id) ON DELETE CASCADE,
                                    FOREIGN KEY (competence_id) REFERENCES competences(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des candidatures
CREATE TABLE candidatures (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              offre_id INT NOT NULL,
                              etudiant_id INT NOT NULL,
                              cv VARCHAR(255) NOT NULL,
                              lettre_motivation TEXT NOT NULL,
                              date_candidature DATETIME NOT NULL,
                              FOREIGN KEY (offre_id) REFERENCES offres(id) ON DELETE CASCADE,
                              FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des wish-lists
CREATE TABLE wishlists (
                           etudiant_id INT NOT NULL,
                           offre_id INT NOT NULL,
                           date_ajout DATETIME NOT NULL,
                           PRIMARY KEY (etudiant_id, offre_id),
                           FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
                           FOREIGN KEY (offre_id) REFERENCES offres(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insertion des données initiales

-- Utilisateur administrateur par défaut (mot de passe: admin123)
INSERT INTO utilisateurs (email, password, role, created_at) VALUES
    ('admin@web4all.fr', '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy', 'admin', NOW());

-- Compétences par défaut
INSERT INTO competences (nom, created_at) VALUES
                                              ('PHP', NOW()),
                                              ('JavaScript', NOW()),
                                              ('HTML/CSS', NOW()),
                                              ('Java', NOW()),
                                              ('Python', NOW()),
                                              ('C#', NOW()),
                                              ('React', NOW()),
                                              ('Angular', NOW()),
                                              ('Vue.js', NOW()),
                                              ('Node.js', NOW()),
                                              ('Laravel', NOW()),
                                              ('Symfony', NOW()),
                                              ('.NET', NOW()),
                                              ('SQL', NOW()),
                                              ('NoSQL', NOW()),
                                              ('Git', NOW()),
                                              ('DevOps', NOW()),
                                              ('Mobile', NOW()),
                                              ('UX/UI', NOW()),
                                              ('SEO', NOW());

-- Index pour optimiser les recherches
CREATE INDEX idx_utilisateurs_email ON utilisateurs(email);
CREATE INDEX idx_utilisateurs_role ON utilisateurs(role);
CREATE INDEX idx_entreprises_nom ON entreprises(nom);
CREATE INDEX idx_offres_entreprise ON offres(entreprise_id);
CREATE INDEX idx_offres_dates ON offres(date_debut, date_fin);
CREATE INDEX idx_candidatures_etudiant ON candidatures(etudiant_id);
CREATE INDEX idx_candidatures_offre ON candidatures(offre_id);
CREATE INDEX idx_wishlists_etudiant ON wishlists(etudiant_id);

-- Table des centres
CREATE TABLE centres (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         nom VARCHAR(100) NOT NULL,
                         code VARCHAR(20) NOT NULL UNIQUE,
                         adresse TEXT,
                         created_at DATETIME NOT NULL,
                         updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         INDEX idx_centres_nom (nom),
                         INDEX idx_centres_code (code)
) ENGINE=InnoDB;

-- Insertion des centres par défaut
INSERT INTO centres (nom, code, adresse, created_at) VALUES
                                                         ('Paris', 'PAR', '93 Boulevard de la Seine, 92000 Paris', NOW()),
                                                         ('Lyon', 'LYO', '19 Avenue Guy de Collongue, 69130 Écully', NOW()),
                                                         ('Arras', 'ARR', '7 Rue Diderot, 62000 Arras', NOW()),
                                                         ('Strasbourg', 'STR', '2 Allée des Foulons, 67380 Lingolsheim', NOW()),
                                                         ('Nancy', 'NAN', '2 Boulevard Henri Becquerel, 57970 Yutz', NOW());