/* ===========================
   TABLES D'ARCHIVES
=========================== */
CREATE TABLE archive_eleve LIKE eleve; ALTER TABLE
    archive_eleve ADD COLUMN annee_archive VARCHAR(20) NOT NULL AFTER id,
    ADD COLUMN date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN archive_par INT NULL;
CREATE TABLE archive_menage LIKE menage; ALTER TABLE
    archive_menage ADD COLUMN annee_archive VARCHAR(20) NOT NULL AFTER id,
    ADD COLUMN date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN archive_par INT NULL;
CREATE TABLE archive_paiement LIKE paiement; ALTER TABLE
    archive_paiement ADD COLUMN annee_archive VARCHAR(20) NOT NULL AFTER id,
    ADD COLUMN date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN archive_par INT NULL;
CREATE TABLE archive_paiement_divers LIKE paiement_divers; ALTER TABLE
    archive_paiement_divers ADD COLUMN annee_archive VARCHAR(20) NOT NULL AFTER id,
    ADD COLUMN date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN archive_par INT NULL;
CREATE TABLE archive_depenses LIKE depenses; ALTER TABLE
    archive_depenses ADD COLUMN annee_archive VARCHAR(20) NOT NULL AFTER id,
    ADD COLUMN date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN archive_par INT NULL;
CREATE TABLE archive_classe LIKE classe; ALTER TABLE
    archive_classe ADD COLUMN annee_archive VARCHAR(20) NOT NULL AFTER id,
    ADD COLUMN date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN archive_par INT NULL;
    /* ===========================
   STATISTIQUES D'ARCHIVE
=========================== */
CREATE TABLE archive_statistiques(
    id INT AUTO_INCREMENT PRIMARY KEY,
    annee_archive VARCHAR(20) NOT NULL,
    date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    archive_par INT NULL,
    total_menages INT DEFAULT 0,
    menages_actifs INT DEFAULT 0,
    menages_inactifs INT DEFAULT 0,
    total_eleves INT DEFAULT 0,
    garcons INT DEFAULT 0,
    filles INT DEFAULT 0,
    personnel_actif INT DEFAULT 0,
    personnel_inactif INT DEFAULT 0,
    encaissement_scolaire DECIMAL(12, 2) DEFAULT 0,
    encaissement_connexe DECIMAL(12, 2) DEFAULT 0,
    depenses DECIMAL(12, 2) DEFAULT 0,
    benefice DECIMAL(12, 2) DEFAULT 0,
    observations TEXT NULL
); CREATE TABLE archive_session(
    id INT AUTO_INCREMENT PRIMARY KEY,
    annee_scolaire_id INT NOT NULL,
    annee_scolaire VARCHAR(50) NOT NULL,
    date_archivage DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    archive_par INT NULL,
    utilisateur VARCHAR(150) NULL,
    commentaire TEXT NULL,
    total_menages INT DEFAULT 0,
    total_eleves INT DEFAULT 0,
    total_classes INT DEFAULT 0,
    total_paiement DECIMAL(12, 2) DEFAULT 0,
    total_paiement_divers DECIMAL(12, 2) DEFAULT 0,
    total_depenses DECIMAL(12, 2) DEFAULT 0,
    statut ENUM('en_cours', 'termine') DEFAULT 'termine'
); ALTER TABLE
    archive_menage ADD COLUMN archive_id INT NOT NULL AFTER id;
ALTER TABLE
    archive_eleve ADD COLUMN archive_id INT NOT NULL AFTER id;
ALTER TABLE
    archive_paiement ADD COLUMN archive_id INT NOT NULL AFTER id;
ALTER TABLE
    archive_paiement_divers ADD COLUMN archive_id INT NOT NULL AFTER id;
ALTER TABLE
    archive_depenses ADD COLUMN archive_id INT NOT NULL AFTER id;
ALTER TABLE
    archive_classe ADD COLUMN archive_id INT NOT NULL AFTER id;
ALTER TABLE
    archive_statistiques ADD COLUMN archive_id INT NOT NULL AFTER id;
ALTER TABLE
    archive_menage ADD CONSTRAINT fk_archive_menage_session FOREIGN KEY(archive_id) REFERENCES archive_session(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE
    archive_eleve ADD CONSTRAINT fk_archive_eleve_session FOREIGN KEY(archive_id) REFERENCES archive_session(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE
    archive_paiement ADD CONSTRAINT fk_archive_paiement_session FOREIGN KEY(archive_id) REFERENCES archive_session(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE
    archive_paiement_divers ADD CONSTRAINT fk_archive_paiement_divers_session FOREIGN KEY(archive_id) REFERENCES archive_session(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE
    archive_depenses ADD CONSTRAINT fk_archive_depenses_session FOREIGN KEY(archive_id) REFERENCES archive_session(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE
    archive_classe ADD CONSTRAINT fk_archive_classe_session FOREIGN KEY(archive_id) REFERENCES archive_session(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE
    archive_statistiques ADD CONSTRAINT fk_archive_statistiques_session FOREIGN KEY(archive_id) REFERENCES archive_session(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE
    archive_menage ADD COLUMN restaure TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN date_restauration DATETIME NULL;
ALTER TABLE
    archive_eleve ADD COLUMN restaure TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN date_restauration DATETIME NULL;
ALTER TABLE
    `archive_classe` ADD `total_eleves` INT NOT NULL AFTER `description`;
ALTER TABLE
    `archive_classe`
DROP
    `cycle`,
DROP
    `dateCreaty`,
DROP
    `dateUpdate`,
DROP
    `createdby`;
ALTER TABLE
    menage ADD COLUMN id_original INT NULL;
ALTER TABLE
    `archive_menage` ADD `code_menage` INT NOT NULL AFTER `annee_archive`;
ALTER TABLE
    `archive_menage` ADD `montantAPayerFC` DECIMAL NOT NULL AFTER `montantAPayer`;
ALTER TABLE
    `menage` ADD `montantAPayerFC` DECIMAL(10, 2) NOT NULL AFTER `montantAPayer`;
ALTER TABLE
    `archive_eleve` ADD `montantAPayerFC` DECIMAL(10, 2) NOT NULL AFTER `montant_a_payer`;
ALTER TABLE
    `eleve` ADD `montantAPayerFC` DECIMAL(10, 2) NOT NULL AFTER `montant_a_payer`;
ALTER TABLE
    `paiement` CHANGE `dateCreated` `dateCreated` TIMESTAMP NOT NULL;