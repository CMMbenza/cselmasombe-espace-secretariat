-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 20 juil. 2025 à 11:10
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
-- Base de données : `cselma`
--

-- --------------------------------------------------------

--
-- Structure de la table `affectation_prof_classe`
--

CREATE TABLE `affectation_prof_classe` (
  `id` int(11) NOT NULL,
  `agent` int(11) DEFAULT NULL,
  `noms` varchar(50) NOT NULL,
  `classe` int(11) DEFAULT NULL,
  `date_affect` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `affectation_prof_classe`
--

INSERT INTO `affectation_prof_classe` (`id`, `agent`, `noms`, `classe`, `date_affect`) VALUES
(1, 1, 'IRENE', 6, '2025-05-26');

-- --------------------------------------------------------

--
-- Structure de la table `agent`
--

CREATE TABLE `agent` (
  `id` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `postnom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `genre` varchar(1) NOT NULL,
  `lieu` varchar(20) NOT NULL,
  `dateDeNaissance` date NOT NULL,
  `niveau_d_etude` varchar(10) NOT NULL,
  `grade` int(11) NOT NULL,
  `fonction` int(11) NOT NULL,
  `salaire` decimal(10,0) NOT NULL,
  `dateCreated` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createdby` text NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `annee_scolaire`
--

CREATE TABLE `annee_scolaire` (
  `id` int(11) NOT NULL,
  `annee_scolaire` varchar(10) NOT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date NOT NULL,
  `status` enum('encours','fin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `annee_scolaire`
--

INSERT INTO `annee_scolaire` (`id`, `annee_scolaire`, `dateDebut`, `dateFin`, `status`) VALUES
(1, '2024-2025', '2024-09-09', '2025-07-02', 'fin'),
(2, '2025-2026', '2025-09-01', '2026-07-02', 'encours'),
(3, '', '0000-00-00', '0000-00-00', '');

-- --------------------------------------------------------

--
-- Structure de la table `annonce`
--

CREATE TABLE `annonce` (
  `id` int(11) NOT NULL,
  `object` text NOT NULL,
  `message` text NOT NULL,
  `signature` varchar(50) NOT NULL,
  `datePubliee` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `assignment_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `type_answers` enum('Pièce jointe','Composé') NOT NULL DEFAULT 'Composé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `answers`
--

INSERT INTO `answers` (`id`, `user_id`, `question_id`, `assignment_id`, `answer_text`, `submission_date`, `type_answers`) VALUES
(1, 308, 1, 1, '800', '2025-06-05 09:05:51', 'Composé'),
(2, 308, 2, 1, '2 487 214', '2025-06-05 09:05:51', 'Composé'),
(3, 308, 3, 1, '0', '2025-06-05 09:05:51', 'Composé'),
(4, 308, 0, 3, 'math 7eb.docx', '2025-05-30 22:00:00', 'Pièce jointe');

-- --------------------------------------------------------

--
-- Structure de la table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_due` date NOT NULL,
  `classe` varchar(50) NOT NULL,
  `professor_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `statut` enum('a faire','terminer') NOT NULL,
  `overall_score` int(11) NOT NULL,
  `name` text NOT NULL,
  `size` int(11) NOT NULL,
  `downloads` int(11) NOT NULL,
  `type_answers` enum('Composé','Pièce jointe') NOT NULL DEFAULT 'Composé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `description`, `date_creation`, `date_due`, `classe`, `professor_id`, `user_id`, `statut`, `overall_score`, `name`, `size`, `downloads`, `type_answers`) VALUES
(1, 'MATH PROBLEME', 'UTILISEZ AUSSI LA CALCULATRICE OU VOS NOTES.', '2025-06-05 09:03:45', '2025-06-05', '6', 1, 1, 'a faire', 20, 'Composé', 0, 0, 'Composé'),
(2, 'statistique', 'Pièce jointe', '2025-06-05 09:36:37', '2025-06-05', '6', 1, 1, 'a faire', 10, 'COMMUNIQUE maternelle (1).docx', 12257, 0, 'Pièce jointe'),
(3, 'statistique', 'Pièce jointe', '2025-06-05 09:37:56', '2025-06-05', '6', 1, 1, 'a faire', 10, 'EXAMENS DE L_EVF.docx', 53989, 1, 'Pièce jointe'),
(4, 'essaie', 'Pièce jointe', '2025-06-05 09:41:21', '2025-06-05', '6', 1, 1, 'a faire', 10, 'EXAMENS DE L\'EVF.docx', 53989, 0, 'Pièce jointe'),
(5, 'math', 'Pièce jointe', '2025-06-09 10:57:54', '2025-06-09', '6', 1, 1, 'a faire', 20, 'Document sans titre (5).pdf', 30620, 0, 'Pièce jointe');

-- --------------------------------------------------------

--
-- Structure de la table `assignment_corrections`
--

CREATE TABLE `assignment_corrections` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `overall_score` int(11) DEFAULT NULL,
  `global_feedback` text DEFAULT NULL,
  `correction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `assignment_corrections`
--

INSERT INTO `assignment_corrections` (`id`, `assignment_id`, `eleve_id`, `overall_score`, `global_feedback`, `correction_date`) VALUES
(1, 1, 308, 14, 'VOUS AVEZ ECHOUER  A UNE SEULE QUESTION NUMERO 3. 5-4 = 1', '2025-06-04 22:00:00'),
(2, 3, 308, 5, 'ras', '2025-06-04 22:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `balance`
--

CREATE TABLE `balance` (
  `id` int(11) NOT NULL,
  `typeReference` text NOT NULL,
  `entre` decimal(10,2) NOT NULL,
  `sorti` decimal(10,2) NOT NULL,
  `reste` decimal(10,2) NOT NULL,
  `dateUpdate` date NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `balance`
--

INSERT INTO `balance` (`id`, `typeReference`, `entre`, `sorti`, `reste`, `dateUpdate`, `anneeScolaire`) VALUES
(232, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(233, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(234, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(235, 'paiement frais scolaire', 35.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(236, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(237, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(238, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(240, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(241, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(242, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(243, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(244, 'paiement frais scolaire', 25.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(245, 'paiement frais scolaire', 23.11, 0.00, 0.00, '2025-03-24', '2024-2025'),
(246, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(247, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(248, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(249, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(250, 'paiement frais scolaire', 35.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(251, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(252, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(253, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(254, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(255, 'Paiement frais scolaires ', 10.00, 0.00, 0.00, '2025-03-24', '2024-2025'),
(256, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(257, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(258, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(259, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(260, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(261, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(262, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(263, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(264, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-03-26', '2024-2025'),
(265, 'paiement frais scolaire', 57.93, 0.00, 0.00, '2025-03-26', '2024-2025'),
(266, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(267, 'ACHAT CARBURANT (dépense)', 0.00, 6.00, 0.00, '2025-03-28', '2024-2025'),
(268, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(269, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(270, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(271, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(272, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(273, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(274, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-28', '2024-2025'),
(275, 'paiement frais scolaire', 16.00, 0.00, 0.00, '2025-03-31', '2024-2025'),
(276, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-03-31', '2024-2025'),
(277, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-03-31', '2024-2025'),
(278, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-03-31', '2024-2025'),
(279, 'COLLATION CARBURANT CONSEIL D_ADMINISTRATION (dépense)', 0.00, 45.00, 0.00, '2025-03-31', '2024-2025'),
(280, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-01', '2024-2025'),
(281, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-04-01', '2024-2025'),
(282, 'SOLDE SALAIRE PERSONNEL ENSEIGNANT MOIS DE MARS 2025 (dépense)', 0.00, 4100.00, 0.00, '2025-04-01', '2024-2025'),
(283, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-03', '2024-2025'),
(284, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-03', '2024-2025'),
(285, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-04-03', '2024-2025'),
(286, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-07', '2024-2025'),
(287, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-10', '2024-2025'),
(288, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-10', '2024-2025'),
(289, 'paiement frais scolaire', 280.00, 0.00, 0.00, '2025-04-10', '2024-2025'),
(290, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-10', '2024-2025'),
(291, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-18', '2024-2025'),
(292, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-18', '2024-2025'),
(293, 'paiement frais scolaire', 45.00, 0.00, 0.00, '2025-04-18', '2024-2025'),
(294, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-04-18', '2024-2025'),
(295, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-18', '2024-2025'),
(296, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-18', '2024-2025'),
(297, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-19', '2024-2025'),
(298, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-19', '2024-2025'),
(299, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(300, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(301, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(302, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(303, 'paiement frais scolaire', 200.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(304, 'paiement frais scolaire', 34.48, 0.00, 0.00, '2025-04-21', '2024-2025'),
(305, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(306, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(307, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(308, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(309, 'paiement frais scolaire', 200.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(310, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(311, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(312, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(313, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(314, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(315, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(316, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(317, 'PAIEMENT FACTURE SNEL MARS 2025 + ACHAT CARBURANT MERCREDI ET JEUDI (dépense)', 0.00, 22.30, 0.00, '2025-04-03', '2024-2025'),
(318, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(319, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(320, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(321, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(322, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(323, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-21', '2024-2025'),
(324, 'paiement frais scolaire', 34.65, 0.00, 0.00, '2025-04-21', '2024-2025'),
(325, 'paiement frais scolaire', 600.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(326, 'paiement frais scolaire', 200.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(327, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(328, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(329, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(330, 'paiement frais scolaire', 5.51, 0.00, 0.00, '2025-04-22', '2024-2025'),
(331, 'paiement frais scolaire', 0.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(332, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(333, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(334, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(335, 'paiement frais scolaire', 130.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(336, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(337, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(338, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(339, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(340, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(341, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(342, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(343, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(344, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(345, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(346, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(347, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(348, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(349, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(350, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(351, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(352, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(353, 'paiement frais scolaire', 590.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(354, 'paiement frais scolaire', 200.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(355, 'paiement frais scolaire', 840.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(356, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(357, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(358, 'paiement frais scolaire', 80.34, 0.00, 0.00, '2025-04-22', '2024-2025'),
(359, 'paiement frais scolaire', 140.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(360, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(361, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(362, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(363, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(364, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(365, 'paiement frais scolaire', 300.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(366, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(367, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(368, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(369, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(370, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(371, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(372, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(373, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-22', '2024-2025'),
(374, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(375, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(376, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(377, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(378, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(379, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(380, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(381, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(382, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(383, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(384, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(385, 'paiement frais scolaire', 600.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(386, 'paiement frais scolaire', 250.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(387, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(388, 'paiement frais scolaire', 320.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(389, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(390, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(391, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(392, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(393, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(394, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(395, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(396, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(397, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(398, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(399, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(400, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(401, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(402, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(403, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(404, 'paiement frais scolaire', 200.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(405, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(406, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(407, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(408, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(409, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(410, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(411, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(412, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(413, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(414, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(415, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(416, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(417, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(418, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-23', '2024-2025'),
(419, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(420, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(421, 'paiement frais scolaire', 17.24, 0.00, 0.00, '2025-04-24', '2024-2025'),
(422, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(423, 'paiement frais scolaire', 110.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(424, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(425, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(426, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(427, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(428, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(429, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(430, 'paiement frais scolaire', 300.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(431, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(432, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(433, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(434, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(435, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(436, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(437, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-24', '2024-2025'),
(438, 'QUINZAINE (AVANCE SUR SALAIRE) MOIS D_AVRIL 2025 (dépense)', 0.00, 1070.00, 0.00, '2025-04-24', '2024-2025'),
(439, 'PAIEMENT SALAIRE DGAF ET AVOCAT MOIS DE MARS 2025 (dépense)', 0.00, 1000.00, 0.00, '2025-04-24', '2024-2025'),
(440, 'PAIEMENT LOYER MOIS D_AVRIL 2025 (dépense)', 0.00, 3005.00, 0.00, '2025-04-24', '2024-2025'),
(441, 'TRANSPORT DGAF CNSS ET ASSONEPA BANQUE; TRANSPORT AVOCAT DOSSIER DIMBA INSPECTION DU TRAVAIL. (dépense)', 0.00, 70.00, 0.00, '2025-04-24', '2024-2025'),
(442, 'QUOTITE MADAME MARIA MOIS D_AVRIL 2025 (dépense)', 0.00, 520.00, 0.00, '2025-04-24', '2024-2025'),
(443, 'REABONNEMENT ROUTER AIRTEL ECOLE (dépense)', 0.00, 52.00, 0.00, '2025-04-24', '2024-2025'),
(444, 'ENTRETIEN ET REPARATION IMPRIMANTE (dépense)', 0.00, 37.00, 0.00, '2025-04-24', '2024-2025'),
(445, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(446, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(447, 'paiement frais scolaire', 240.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(448, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(449, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(450, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(451, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(452, 'paiement frais scolaire', 45.00, 0.00, 0.00, '2025-04-25', '2024-2025'),
(453, 'ACHAT CARBURANT DU MERCREDI 16 MARS 2025 ET TRANSPORT NGOMA CHRIS DGRK DEPOT DECLARATION 1er TRIMESTRE 2025 (dépense)', 0.00, 12.00, 0.00, '2025-04-25', '2024-2025'),
(454, 'PAIEMENT FACTURE REGIDESO MARS 2025 (dépense)', 0.00, 34.00, 0.00, '2025-04-25', '2024-2025'),
(455, 'ACHAT CARBURANT DU MARDI 08 AVRIL 2025 (dépense)', 0.00, 6.00, 0.00, '2025-04-25', '2024-2025'),
(456, 'REPARATION GROUPE ET ACHAT TUYAU CARBURATEUR (dépense)', 0.00, 10.00, 0.00, '2025-04-25', '2024-2025'),
(457, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-26', '2024-2025'),
(458, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-26', '2024-2025'),
(459, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-04-26', '2024-2025'),
(460, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-26', '2024-2025'),
(461, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(462, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(463, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(464, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(465, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(466, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(467, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(468, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(469, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(470, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(471, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(472, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(473, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(474, 'paiement frais scolaire', 1050.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(475, 'paiement frais scolaire', 140.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(476, 'paiement frais scolaire', 640.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(477, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(478, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(479, 'paiement frais scolaire', 200.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(480, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(481, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(482, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(483, 'paiement frais scolaire', 135.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(484, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(485, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-28', '2024-2025'),
(486, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(487, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(488, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(489, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(490, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(491, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(492, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(493, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(494, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(495, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(496, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-04-29', '2024-2025'),
(497, 'ACHAT CARBURANT DU LUNDI 28 AVRIL 2025 (dépense)', 0.00, 10.00, 0.00, '2025-04-29', '2024-2025'),
(498, 'SOLDE SALAIRE PERSONNEL MOIS D_AVRIL 2025 (dépense)', 0.00, 5550.00, 0.00, '2025-04-29', '2024-2025'),
(499, 'SOLDE COMPTE MONSIEUR MPUTU ACAHT UNIFORME ET FRAIS ALBUM PHOTOS SORTIE SCOLAIRE (dépense)', 0.00, 430.00, 0.00, '2025-04-29', '2024-2025'),
(500, 'FRAIS DE FONCTIONNEMENT DIRECTIONS MATERNELLE ET PRIMAIRE (dépense)', 0.00, 65.00, 0.00, '2025-04-29', '2024-2025'),
(501, 'CARBURANT GESTIONNAIRE DGI MADAME ESTHER ET DGAF (dépense)', 0.00, 45.00, 0.00, '2025-04-29', '2024-2025'),
(502, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-02', '2024-2025'),
(503, 'paiement frais scolaire', 45.00, 0.00, 0.00, '2025-05-02', '2024-2025'),
(504, 'paiement frais scolaire', 45.00, 0.00, 0.00, '2025-05-03', '2024-2025'),
(505, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-03', '2024-2025'),
(506, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(507, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(508, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(509, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(510, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(511, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(512, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(513, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(514, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(515, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(516, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(517, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(518, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(519, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(520, 'paiement frais scolaire', 300.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(521, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(522, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(523, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(524, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(525, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(526, 'ACHAT 6 PAQUETS STULOS (BLUE, NOIR, ROUGE) BUREAU ET ACHAT CLASSEUR BUREAU DG (dépense)', 0.00, 62.00, 0.00, '2025-05-05', '2024-2025'),
(527, 'COLLATION PERSONNEL DE LA JOURNEE DU 30 AVRIL 2025 ET ENTRETIENT GROUP ELECTROGENE (dépense)', 0.00, 154.00, 0.00, '2025-05-05', '2024-2025'),
(528, 'paiement frais scolaire', 35.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(529, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(530, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-05', '2024-2025'),
(531, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(532, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(533, 'paiement frais scolaire', 2.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(534, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(535, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(536, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(537, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(538, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(539, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(540, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(541, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(542, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(543, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(544, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(545, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(546, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(547, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(548, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(549, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(550, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(551, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(552, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(553, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(554, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(555, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(556, 'paiement frais scolaire', 300.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(558, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(559, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(560, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(562, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(563, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(565, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-06', '2024-2025'),
(566, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-07', '2024-2025'),
(567, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-07', '2024-2025'),
(568, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-07', '2024-2025'),
(569, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-07', '2024-2025'),
(570, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-07', '2024-2025'),
(571, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-07', '2024-2025'),
(572, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-05-07', '2024-2025'),
(573, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-08', '2024-2025'),
(574, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-08', '2024-2025'),
(575, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-05-08', '2024-2025'),
(576, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-08', '2024-2025'),
(577, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-09', '2024-2025'),
(578, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-09', '2024-2025'),
(579, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-09', '2024-2025'),
(580, 'paiement frais scolaire', 180.00, 0.00, 0.00, '2025-05-09', '2024-2025'),
(581, 'paiement frais scolaire', 55.00, 0.00, 0.00, '2025-05-10', '2024-2025'),
(582, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(583, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(584, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(585, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(586, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(587, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(588, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(589, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(590, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(591, 'paiement frais scolaire', 200.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(592, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(593, 'paiement frais scolaire', 45.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(594, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(595, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(596, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(597, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(598, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(599, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(600, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(601, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(602, 'paiement frais scolaire', 65.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(603, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(604, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(605, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(606, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(607, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-12', '2024-2025'),
(608, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(609, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(610, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(611, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(612, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(613, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(614, 'paiement frais scolaire', 25.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(615, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(616, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(617, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(618, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(619, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(620, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(621, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(622, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(623, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(624, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(625, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(626, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(627, 'paiement frais scolaire', 105.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(628, 'paiement frais scolaire', 110.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(629, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(630, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(631, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(632, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(633, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(634, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(635, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(636, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(637, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(638, 'paiement frais scolaire', 37.24, 0.00, 0.00, '2025-05-13', '2024-2025'),
(639, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(640, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(641, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(642, 'paiement frais scolaire', 135.00, 0.00, 0.00, '2025-05-13', '2024-2025'),
(643, 'ACHAT CARBURANT GROUPE (dépense)', 0.00, 6.00, 0.00, '2025-05-14', '2024-2025'),
(644, 'PAIEMENT FACTURE SNEL MOIS D-AVRIL 2025 (dépense)', 0.00, 12.00, 0.00, '2025-05-14', '2024-2025'),
(645, 'ENTRETIENT ¨REMIER BLOC DES 3 WC (dépense)', 0.00, 300.00, 0.00, '2025-05-14', '2024-2025'),
(646, 'PAIEMENT LOYER MOIS DE MAI 2025 (dépense)', 0.00, 3005.00, 0.00, '2025-05-14', '2024-2025'),
(647, 'PAIEMENT LOYER MOIS DE MAI CONTRIBUTION CNSS MOIS DE MARS 2025 (dépense)', 0.00, 275.00, 0.00, '2025-05-14', '2024-2025'),
(648, 'AVANCZ MAINS D-OEUVRE PLOMBIER (dépense)', 0.00, 43.00, 0.00, '2025-05-14', '2024-2025'),
(649, 'ACHAT JAVEL, DETERGENTS, CARBURANT ET TRANSPORT (dépense)', 0.00, 60.00, 0.00, '2025-05-14', '2024-2025'),
(650, 'PAIEMENT FACTURE REGIDESO MOIS D-AVRIL 2025 ET PALMARES TENASOSP SOUS-DIVISION (dépense)', 0.00, 25.00, 0.00, '2025-05-14', '2024-2025'),
(652, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(653, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(654, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(655, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(656, 'paiement frais scolaire', 35.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(657, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(658, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(659, 'paiement frais scolaire', 35.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(660, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(661, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(662, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(663, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(664, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(665, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(666, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(667, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(668, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(669, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(670, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(671, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(672, 'paiement frais scolaire', 70.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(673, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(674, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(675, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(676, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(677, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-14', '2024-2025'),
(678, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(679, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(680, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(681, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(682, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(683, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(684, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(685, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(686, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-15', '2024-2025'),
(687, 'ENTRETIENT ET INSTALLATION ORDINATEUR ORDINATEURS BUREAU (dépense)', 0.00, 70.00, 0.00, '2025-05-15', '2024-2025'),
(688, 'TRANSPORT POUBELLE, ACHAT 2 FUSIBLES + CARBURANT DU MARDI, MERCREDI ET JEUDI 15/05/2025 ET TRANSPORT DGAF CNSS (dépense)', 0.00, 40.00, 0.00, '2025-05-15', '2024-2025'),
(689, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(690, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(691, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(692, 'paiement frais scolaire', 110.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(693, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(694, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(695, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(696, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(697, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(698, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-19', '2024-2025'),
(699, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-20', '2024-2025'),
(700, 'paiement frais scolaire', 155.00, 0.00, 0.00, '2025-05-20', '2024-2025'),
(701, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-20', '2024-2025'),
(702, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-20', '2024-2025'),
(703, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-20', '2024-2025'),
(704, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-20', '2024-2025'),
(705, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-21', '2024-2025'),
(706, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-21', '2024-2025'),
(707, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-21', '2024-2025'),
(708, 'ENTRETIENT GROUPE (dépense)', 0.00, 30.00, 0.00, '2025-05-20', '2024-2025'),
(709, 'QUOTITE MADAME MARIA MOIS DE MAI 2025 (dépense)', 0.00, 520.00, 0.00, '2025-05-20', '2024-2025'),
(710, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-21', '2024-2025'),
(711, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-21', '2024-2025'),
(712, 'paiement frais scolaire', 450.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(713, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-21', '2024-2025'),
(714, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-22', '2024-2025'),
(715, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-22', '2024-2025'),
(716, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-22', '2024-2025'),
(717, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-23', '2024-2025'),
(718, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(719, 'paiement frais scolaire', 17.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(720, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(721, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(722, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(723, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(724, 'paiement frais scolaire', 45.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(725, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(726, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(727, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(728, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(729, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(730, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(731, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-26', '2024-2025'),
(732, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(733, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(734, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(735, 'paiement frais scolaire', 25.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(736, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(737, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(738, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(739, 'paiement frais scolaire', 25.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(740, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(741, 'NEGOCIATION DOSSIER DGI CONTROLE PENALITES (dépense)', 0.00, 400.00, 0.00, '2025-05-27', '2024-2025'),
(742, 'REABONNEMENT ROUTER ET ACHAT CARBURANT (dépense)', 0.00, 60.00, 0.00, '2025-05-27', '2024-2025'),
(743, 'paiement frais scolaire', 11.73, 0.00, 0.00, '2025-05-27', '2024-2025'),
(744, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(745, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-27', '2024-2025'),
(746, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(747, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(748, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(749, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(750, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(751, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(752, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(753, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(754, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(755, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(756, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(757, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(758, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(759, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(760, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-28', '2024-2025'),
(761, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-05-29', '2024-2025'),
(762, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-29', '2024-2025'),
(763, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-05-29', '2024-2025'),
(764, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-29', '2024-2025'),
(765, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-29', '2024-2025'),
(766, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-29', '2024-2025'),
(767, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-05-29', '2024-2025'),
(769, 'paiement frais scolaire', 22.00, 0.00, 0.00, '2025-05-30', '2024-2025'),
(770, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-30', '2024-2025'),
(771, 'paiement frais scolaire', 85.00, 0.00, 0.00, '2025-05-30', '2024-2025'),
(772, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-05-30', '2024-2025'),
(773, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-05-30', '2024-2025'),
(774, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(775, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(776, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(777, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(778, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(779, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(780, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(781, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(782, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(783, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(784, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(785, 'paiement frais scolaire', 320.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(786, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(787, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(788, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(789, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(790, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(791, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(792, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(793, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(794, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(795, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(796, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(797, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(798, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-02', '2024-2025'),
(799, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(800, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(801, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(802, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(803, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(804, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(805, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(806, 'paiement frais scolaire', 7.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(807, 'paiement frais scolaire', 110.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(808, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(809, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(810, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(811, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(812, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(813, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(814, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-03', '2024-2025'),
(815, 'PAIEMENT FACTURE SNEL MOIS DE MAI 2025 (dépense)', 0.00, 9.00, 0.00, '2025-06-03', '2024-2025'),
(816, 'PAIEMENT SALAIRE PERSONNEL MOIS DE MAI 2025 (dépense)', 0.00, 4010.00, 0.00, '2025-06-03', '2024-2025'),
(817, 'paiement frais scolaire', 25.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(818, 'paiement frais scolaire', 2.76, 0.00, 0.00, '2025-06-04', '2024-2025'),
(819, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(820, 'paiement frais scolaire', 35.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(821, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(822, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(823, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(824, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(825, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(826, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(827, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(828, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(829, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-04', '2024-2025'),
(830, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-05', '2024-2025'),
(831, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-05', '2024-2025'),
(832, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-06-05', '2024-2025'),
(833, 'paiement frais scolaire', 1.00, 0.00, 0.00, '2025-06-05', '2024-2025'),
(834, 'paiement frais scolaire', 80.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(835, 'paiement frais scolaire', 157.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(836, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(837, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(838, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(839, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(840, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(841, 'paiement frais scolaire', 0.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(842, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(843, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(844, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(845, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(846, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(847, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(848, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(849, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(850, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(851, 'paiement frais scolaire', 0.69, 0.00, 0.00, '2025-06-09', '2024-2025'),
(852, 'paiement frais scolaire', 6.21, 0.00, 0.00, '2025-06-09', '2024-2025'),
(853, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(854, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(855, 'paiement frais scolaire', 90.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(856, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-06-09', '2024-2025'),
(857, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-10', '2024-2025');
INSERT INTO `balance` (`id`, `typeReference`, `entre`, `sorti`, `reste`, `dateUpdate`, `anneeScolaire`) VALUES
(858, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-10', '2024-2025'),
(859, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-10', '2024-2025'),
(860, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-10', '2024-2025'),
(861, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-11', '2024-2025'),
(862, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-11', '2024-2025'),
(863, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-11', '2024-2025'),
(864, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-11', '2024-2025'),
(865, 'paiement frais scolaire', 40.00, 0.00, 0.00, '2025-06-11', '2024-2025'),
(866, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-11', '2024-2025'),
(867, 'paiement frais scolaire', 250.00, 0.00, 0.00, '2025-06-11', '2024-2025'),
(868, 'paiement frais scolaire', 35.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(869, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(870, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(871, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(872, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(873, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(874, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(875, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-06-16', '2024-2025'),
(876, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-06-17', '2024-2025'),
(877, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-17', '2024-2025'),
(878, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-17', '2024-2025'),
(879, 'paiement frais scolaire', 9.60, 0.00, 0.00, '2025-06-17', '2024-2025'),
(880, 'paiement frais scolaire', 15.00, 0.00, 0.00, '2025-06-17', '2024-2025'),
(881, 'paiement frais scolaire', 20.00, 0.00, 0.00, '2025-06-17', '2024-2025'),
(882, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-06-18', '2024-2025'),
(883, 'paiement frais scolaire', 5.00, 0.00, 0.00, '2025-06-18', '2024-2025'),
(884, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-19', '2024-2025'),
(885, 'paiement frais scolaire', 50.00, 0.00, 0.00, '2025-06-19', '2024-2025'),
(886, 'paiement frais scolaire', 30.00, 0.00, 0.00, '2025-06-19', '2024-2025'),
(887, 'paiement frais scolaire', 10.00, 0.00, 0.00, '2025-06-24', '2024-2025'),
(888, 'paiement frais scolaire', 65.00, 0.00, 0.00, '2025-06-26', '2024-2025'),
(889, 'paiement frais scolaire', 25.00, 0.00, 0.00, '2025-06-26', '2024-2025'),
(891, 'Loyer juillet 2024 + Transport (dépense)', 0.00, 1305.00, 0.00, '2024-07-24', '2024-2025'),
(892, 'Transport agent (dépense)', 0.00, 14.00, 0.00, '2024-07-24', '2024-2025'),
(893, 'manquant caisse (dépense)', 0.00, 20.61, 0.00, '2024-07-23', '2024-2025'),
(894, 'soins MADAME BUTSHI (dépense)', 0.00, 250.00, 0.00, '2024-07-24', '2024-2025'),
(895, 'reparation groupe electrogene (dépense)', 0.00, 30.35, 0.00, '2024-07-24', '2024-2025'),
(896, 'Avance sur salaire Mr FAYA ARTHUR (dépense)', 0.00, 150.00, 0.00, '2024-07-22', '2024-2025'),
(897, 'reglement facture REGIDESO (dépense)', 0.00, 57.00, 0.00, '2024-07-22', '2024-2025'),
(898, 'Frais couverture PALMARES (dépense)', 0.00, 18.00, 0.00, '2024-07-22', '2024-2025'),
(899, 'solde loyer juillet et transpot (dépense)', 0.00, 1710.00, 0.00, '2025-08-09', '2024-2025'),
(900, 'solde loyer juillet et transpot (dépense)', 0.00, 1710.00, 0.00, '2024-08-09', '2024-2025'),
(901, 'solde loyer juillet et transpot (dépense)', 0.00, 1710.00, 0.00, '2024-08-09', '2024-2025'),
(902, 'achat seaux  (dépense)', 0.00, 80.00, 0.00, '2024-08-10', '2024-2025'),
(903, 'salaire agent juin  (dépense)', 0.00, 1699.00, 0.00, '2024-08-24', '2024-2025'),
(904, 'honoraire DAF (dépense)', 0.00, 400.00, 0.00, '2024-08-24', '2024-2025'),
(905, 'honoraire DAF (dépense)', 0.00, 400.00, 0.00, '2024-08-24', '2024-2025'),
(906, 'frais funeraire Mme BITSHI (dépense)', 0.00, 500.00, 0.00, '2024-08-24', '2024-2025'),
(907, 'solde salaire juin (dépense)', 0.00, 1657.00, 0.00, '2024-08-24', '2024-2025'),
(908, 'achat imprimante et transport (dépense)', 0.00, 700.00, 0.00, '2024-08-24', '2024-2025'),
(909, 'supplement achat imprimante et transport (dépense)', 0.00, 70.00, 0.00, '2024-08-29', '2024-2025'),
(910, 'loyer aout 2024 (dépense)', 0.00, 3010.00, 0.00, '2024-08-29', '2024-2025'),
(911, 'solde salaire juillet et aout Mukendi (dépense)', 0.00, 238.00, 0.00, '2024-08-31', '2024-2025'),
(912, 'salaire juillet et aout DIMBA et MOYOYO (dépense)', 0.00, 486.00, 0.00, '2024-09-03', '2024-2025'),
(913, 'Avance sur salaire juillet 2024 (dépense)', 0.00, 1656.00, 0.00, '2024-09-04', '2024-2025'),
(914, 'versement caisse centrale  (dépense)', 0.00, 11700.00, 0.00, '2024-09-04', '2024-2025'),
(915, 'solde decomptes finaux DIMBA et MOYOYO (dépense)', 0.00, 1387.41, 0.00, '2024-09-07', '2024-2025'),
(916, 'envoi Mme MARIA (dépense)', 0.00, 305.00, 0.00, '2024-09-07', '2024-2025'),
(917, 'restitution FS SUDIKA KIMODO (dépense)', 0.00, 135.00, 0.00, '2024-09-09', '2024-2025'),
(918, 'frais d\'envoi Mme MARIA (dépense)', 0.00, 17.00, 0.00, '2024-09-10', '2024-2025'),
(919, 'loyer semptembre (dépense)', 0.00, 3010.00, 0.00, '2024-09-12', '2024-2025'),
(920, 'facture REGIDESO (dépense)', 0.00, 56.03, 0.00, '2024-09-16', '2024-2025'),
(921, 'quinzine sept 2024 (dépense)', 0.00, 980.00, 0.00, '2024-09-16', '2024-2025'),
(922, ' (dépense)', 0.00, 0.00, 0.00, '0000-00-00', '2024-2025'),
(923, 'achat fourniture  (dépense)', 0.00, 257.24, 0.00, '2024-09-21', '2024-2025'),
(924, 'AVANCE SALAIRE SEPTEMBRE 2024 QUINZAINE (dépense)', 0.00, 60.00, 0.00, '2024-09-17', '2024-2025'),
(925, 'QUOTITE MME MARIA (dépense)', 0.00, 250.00, 0.00, '2024-09-28', '2024-2025'),
(926, 'FRAIS ENVOI QUOTITE MME MARIA (dépense)', 0.00, 20.00, 0.00, '2024-09-28', '2024-2025'),
(927, 'FRAIS JUSTICE & ENGAGEMENT AVOCAT (dépense)', 0.00, 450.00, 0.00, '2024-09-28', '2024-2025'),
(928, 'ACHAT & FABRICATION PORTE & FENETRE (dépense)', 0.00, 850.00, 0.00, '2024-09-28', '2024-2025'),
(929, 'AVANCE SALAIRE SEPTEMBRE TSHISHIMBI THIERRY (dépense)', 0.00, 70.00, 0.00, '2024-09-28', '2024-2025'),
(930, 'ACHAT TS (8D7) (dépense)', 0.00, 320.00, 0.00, '2024-09-28', '2024-2025'),
(931, 'avance sur salaire setempbre tshishimbi (dépense)', 0.00, 30.00, 0.00, '2024-09-28', '2024-2025'),
(932, 'M.O Maçon bureau et achat ciment + sable (dépense)', 0.00, 100.00, 0.00, '2024-09-28', '2024-2025'),
(933, 'achat peinture + papier peint bureau (dépense)', 0.00, 145.00, 0.00, '2024-09-30', '2024-2025'),
(934, 'solde salaire kinkela & transport de séance samedi 02.11.2024 (dépense)', 0.00, 200.00, 0.00, '2024-11-04', '2024-2025'),
(935, 'solde salaire oct 2024 (dépense)', 0.00, 1000.00, 0.00, '2024-11-05', '2024-2025'),
(936, 'solde couturière  (dépense)', 0.00, 139.00, 0.00, '2024-11-05', '2024-2025'),
(937, 'reparution imprimante (dépense)', 0.00, 30.00, 0.00, '2024-11-06', '2024-2025'),
(938, 'achat table, transport loyer novembre 2024 (dépense)', 0.00, 3160.00, 0.00, '2024-11-07', '2024-2025'),
(939, 'Rbt FS mwalumba (dépense)', 0.00, 100.00, 0.00, '2024-11-07', '2024-2025'),
(940, 'frais me faustin (avocat) (dépense)', 0.00, 500.00, 0.00, '2024-11-11', '2024-2025'),
(941, 'achat carburant (dépense)', 0.00, 6.00, 0.00, '2024-11-11', '2024-2025'),
(942, 'transport ilunga (dépense)', 0.00, 6.00, 0.00, '2024-11-11', '2024-2025'),
(943, 'transport personnel (dépense)', 0.00, 18.00, 0.00, '2024-11-11', '2024-2025'),
(944, 'achat table bureau + 1 chaise (dépense)', 0.00, 380.00, 0.00, '2024-11-11', '2024-2025'),
(945, 'achat 1bt craie (dépense)', 0.00, 51.00, 0.00, '2024-11-11', '2024-2025'),
(946, 'achat matériel pour maternelle (dépense)', 0.00, 34.00, 0.00, '2024-11-13', '2024-2025'),
(947, 'Quinzaine novembre 2024 (dépense)', 0.00, 1120.00, 0.00, '2024-11-15', '2024-2025'),
(948, 'paiement CNSS novembre 2023 (dépense)', 0.00, 314.00, 0.00, '2024-11-15', '2024-2025'),
(949, 'paiement CNSS octobre 2024 (dépense)', 0.00, 136.00, 0.00, '2024-11-15', '2024-2025'),
(950, 'paiement IPR octobre 2023 (dépense)', 0.00, 103.00, 0.00, '2024-11-15', '2024-2025'),
(951, 'ajout quinzaine novembre 2024 (dépense)', 0.00, 100.00, 0.00, '2024-11-15', '2024-2025'),
(952, 'collation 16.11.2024 (dépense)', 0.00, 130.00, 0.00, '2024-11-16', '2024-2025'),
(953, 'Loyer (dépense)', 0.00, 2560.00, 0.00, '2024-12-10', '2024-2025'),
(954, 'avance salaire pret (dépense)', 0.00, 200.00, 0.00, '2024-12-12', '2024-2025'),
(955, 'salaire (dépense)', 0.00, 11712.00, 0.00, '2024-12-06', '2024-2025'),
(956, 'honoraires avocat (dépense)', 0.00, 500.00, 0.00, '2024-12-02', '2024-2025'),
(957, 'carburant (dépense)', 0.00, 6.00, 0.00, '2024-12-10', '2024-2025'),
(958, 'CNSS (dépense)', 0.00, 65.00, 0.00, '2024-12-13', '2024-2025'),
(959, 'IPR (dépense)', 0.00, 94.00, 0.00, '2024-12-13', '2024-2025'),
(960, 'IRL (dépense)', 0.00, 450.00, 0.00, '2024-12-11', '2024-2025'),
(961, 'regideso (dépense)', 0.00, 38.00, 0.00, '2024-12-16', '2024-2025'),
(962, 'achat divers (dépense)', 0.00, 2400.00, 0.00, '2024-12-26', '2024-2025'),
(963, 'transport (dépense)', 0.00, 130.00, 0.00, '2024-12-28', '2024-2025'),
(964, 'autres (dépense)', 0.00, 1141.00, 0.00, '2024-12-26', '2024-2025'),
(965, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-07-08', '2025-2026'),
(966, 'Loyer  (dépense)', 0.00, 3.00, 0.00, '2025-01-09', '2024-2025'),
(967, 'Quinzaine  (dépense)', 0.00, 1.00, 0.00, '2025-01-14', '2024-2025'),
(968, 'Avance sur salaire/prêt  (dépense)', 0.00, 40.00, 0.00, '2025-01-13', '2024-2025'),
(969, 'Salaire  (dépense)', 0.00, 6.00, 0.00, '2025-01-31', '2024-2025'),
(970, 'Quotité Mme Maria et Mme Flora  (dépense)', 0.00, 1.00, 0.00, '2025-01-21', '2024-2025'),
(971, 'Frais de facturation  (dépense)', 0.00, 41.00, 0.00, '2025-01-14', '2024-2025'),
(972, 'Fournitures de bureau  (dépense)', 0.00, 6.00, 0.00, '2025-01-31', '2024-2025'),
(973, 'Carburant  (dépense)', 0.00, 25.00, 0.00, '2025-01-23', '2024-2025'),
(974, 'Entretiens  (dépense)', 0.00, 50.00, 0.00, '2025-01-13', '2024-2025'),
(975, 'CNSS (dépense)', 0.00, 57.00, 0.00, '2025-01-13', '2024-2025'),
(976, 'Décompte final  (dépense)', 0.00, 2000.00, 0.00, '2025-01-20', '2024-2025'),
(977, 'Regideso (dépense)', 0.00, 21.00, 0.00, '2025-01-14', '2024-2025'),
(978, 'Dossier inspection du travail  (dépense)', 0.00, 390.00, 0.00, '2025-01-22', '2024-2025'),
(979, 'Autres dépenses  (dépense)', 0.00, 1.00, 0.00, '2025-01-09', '2024-2025'),
(980, 'Transport  (dépense)', 0.00, 26.00, 0.00, '2025-01-02', '2024-2025'),
(981, 'Assistante sociale  (dépense)', 0.00, 160.00, 0.00, '2025-01-21', '2024-2025'),
(982, 'Collation CG (comité de gestion) (dépense)', 0.00, 60.00, 0.00, '2025-01-21', '2024-2025'),
(983, 'Transport avocat et informaticien  (dépense)', 0.00, 20.00, 0.00, '2025-01-02', '2024-2025'),
(984, 'Loyer janvier 2025 (dépense)', 0.00, 3.00, 0.00, '2025-01-09', '2024-2025'),
(985, 'Solde salaire personnel décembre 2024 (dépense)', 0.00, 1000.00, 0.00, '2025-01-09', '2024-2025'),
(986, 'Solde dette JC  (dépense)', 0.00, 1.00, 0.00, '2025-01-09', '2024-2025'),
(987, 'Collation CG (comité de gestion) 11.01.2025 (dépense)', 0.00, 60.00, 0.00, '2025-01-13', '2024-2025'),
(988, 'Prêt finga  (dépense)', 0.00, 40.00, 0.00, '2025-01-13', '2024-2025'),
(989, 'Achat pièce et réparation imprimante  (dépense)', 0.00, 50.00, 0.00, '2025-01-13', '2024-2025'),
(990, 'Paie ipr(101,3$) et CNSS (58,6$) (dépense)', 0.00, 159.00, 0.00, '2025-01-13', '2024-2025'),
(991, 'Ff David  (dépense)', 0.00, 20.00, 0.00, '2025-01-14', '2024-2025'),
(992, 'Ff Huguette mitongo (dépense)', 0.00, 20.00, 0.00, '2025-01-14', '2024-2025'),
(993, 'Transport Delphine dgi et CNSS  (dépense)', 0.00, 6.00, 0.00, '2025-01-14', '2024-2025'),
(994, 'Facture regideso  (dépense)', 0.00, 21.00, 0.00, '2025-01-14', '2024-2025'),
(995, 'Quinzaine janvier 2025 (dépense)', 0.00, 1.00, 0.00, '2025-01-14', '2024-2025'),
(996, 'Décompte final personnel fin contrat  (dépense)', 0.00, 2000.00, 0.00, '2025-01-20', '2024-2025'),
(997, 'Assistance David décès enfant  (dépense)', 0.00, 100.00, 0.00, '2025-01-21', '2024-2025'),
(998, 'Quotité Mme Maria janvier 2025 (dépense)', 0.00, 520.00, 0.00, '2025-01-20', '2024-2025'),
(999, 'Dossier inspection du travail  (dépense)', 0.00, 390.00, 0.00, '2025-01-22', '2024-2025'),
(1000, 'Solde salaire janvier personnel sortant  (dépense)', 0.00, 572.00, 0.00, '2025-01-23', '2024-2025'),
(1001, 'Achat carburant et vidange poubelle  (dépense)', 0.00, 25.00, 0.00, '2025-01-23', '2024-2025'),
(1002, 'Quotité Mme Elena janvier 2025 (dépense)', 0.00, 500.00, 0.00, '2025-01-27', '2024-2025'),
(1003, 'Assistance sœur Irène décès papa  (dépense)', 0.00, 60.00, 0.00, '2025-01-27', '2024-2025'),
(1004, 'Solde salaire personnel janvier 2025 (dépense)', 0.00, 4.00, 0.00, '2025-01-30', '2024-2025'),
(1005, 'Solde salaire Ilunga juillet 2024 (dépense)', 0.00, 80.00, 0.00, '2025-01-30', '2024-2025'),
(1006, 'Achat fournitures  (dépense)', 0.00, 69.00, 0.00, '2025-01-31', '2024-2025'),
(1007, 'collation nombre cg (dépense)', 0.00, 100.00, 0.00, '2025-02-03', '2024-2025'),
(1008, 'paiement frais scolaire', 120.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(1009, 'paiement frais scolaire', 60.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(1010, 'paiement frais scolaire', 195.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(1011, 'paiement frais scolaire', 150.00, 0.00, 0.00, '2025-03-22', '2024-2025'),
(1012, 'paiement frais scolaire', 117.58, 0.00, 0.00, '2025-03-22', '2024-2025'),
(1013, 'Ajout arrière Juillet & Août 2024 personnel sortant (dépense)', 0.00, 400.00, 0.00, '2024-02-25', '2024-2025'),
(1014, 'Ajout arrière Juillet & Août 2024 personnel sortant (dépense)', 0.00, 400.00, 0.00, '2025-02-27', '2024-2025'),
(1015, 'Solde personnel mois février 2025 (dépense)', 0.00, 5370.00, 0.00, '2025-02-28', '2024-2025'),
(1016, 'Frais Inspection du travail (dépense)', 0.00, 50.00, 0.00, '2025-02-28', '2024-2025'),
(1017, 'Salaire février Mme Lubau (dépense)', 0.00, 200.00, 0.00, '2025-02-28', '2024-2025'),
(1018, 'Paiement facture SNEL Janvier 2025 (dépense)', 0.00, 27.50, 0.00, '2025-02-03', '2024-2025'),
(1019, 'impôt /loyer février (dépense)', 0.00, 600.00, 0.00, '2025-02-04', '2024-2025'),
(1020, 'F/Rebecca appl. de la gestion (dépense)', 0.00, 150.00, 0.00, '2025-02-04', '2024-2025'),
(1021, 'légalisation statut école (dépense)', 0.00, 200.00, 0.00, '2025-02-06', '2024-2025'),
(1022, 'Loyer février 2025 (dépense)', 0.00, 2410.00, 0.00, '2025-02-10', '2024-2025'),
(1023, 'FF Tshibuabua & Mitongo (dépense)', 0.00, 62.00, 0.00, '2025-02-10', '2024-2025'),
(1024, 'Solde salaire juillet 2024 Tshibuabua & Finga, Mputu (dépense)', 0.00, 360.00, 0.00, '2025-02-11', '2024-2025'),
(1025, 'paiement CNSS & IPR Janvier 2025 (dépense)', 0.00, 169.90, 0.00, '2025-02-11', '2024-2025'),
(1026, 'Avance sur salaire prof mputu héritier  (dépense)', 0.00, 80.00, 0.00, '2025-02-12', '2024-2025'),
(1027, 'Solde arrièré juillet & août 2024 parfait (dépense)', 0.00, 1950.00, 0.00, '2025-02-17', '2024-2025'),
(1028, 'Quotité Mme Maria février 2025 (dépense)', 0.00, 520.00, 0.00, '2025-02-17', '2024-2025'),
(1029, 'Quotité Mme Elena février 2025 (dépense)', 0.00, 500.00, 0.00, '2025-02-17', '2024-2025'),
(1030, 'Décompte final Mme REBECCA (dépense)', 0.00, 863.00, 0.00, '2025-02-17', '2024-2025'),
(1031, 'facture REGIDESO janvier 2025 (dépense)', 0.00, 27.50, 0.00, '2025-02-17', '2024-2025'),
(1032, 'formation enseignant & educ (dépense)', 0.00, 150.00, 0.00, '2025-02-17', '2024-2025'),
(1033, 'Quinzaine Février 2025 (dépense)', 0.00, 1150.00, 0.00, '2025-02-17', '2024-2025'),
(1034, 'Acompte Mr Mputu prêt à l\'école (dépense)', 0.00, 500.00, 0.00, '2025-02-17', '2024-2025'),
(1035, 'Ajout frais arriéré Juillet & Août 2024 (dépense)', 0.00, 270.00, 0.00, '2025-02-20', '2024-2025'),
(1036, 'Achat carburant (dépense)', 0.00, 6.00, 0.00, '2025-02-20', '2024-2025'),
(1037, 'paiement frais scolaire', 10.52, 0.00, 0.00, '2025-05-16', '2024-2025'),
(1038, 'solde salaire personnel sept 2024 (dépense)', 0.00, 5800.00, 0.00, '2024-10-03', '2024-2025'),
(1039, 'Facture SNEL septembre (dépense)', 0.00, 17.00, 0.00, '2024-10-01', '2024-2025'),
(1040, 'Facture SNEL le 29.10.2024 (dépense)', 0.00, 14.00, 0.00, '2024-10-29', '2024-2025'),
(1041, 'Paiement salaire personnel octobre 2024 (dépense)', 0.00, 4700.00, 0.00, '2024-10-30', '2024-2025'),
(1042, 'Loyer octobre 2024 (dépense)', 0.00, 3005.00, 0.00, '2024-10-08', '2024-2025'),
(1043, 'Avance salaire ilunga octobre 2024 (dépense)', 0.00, 50.00, 0.00, '2024-10-09', '2024-2025'),
(1044, 'Entretient Groupe Électrogène (20$), Transport DGI (40$) & Transport personnel (15$) (dépense)', 0.00, 75.00, 0.00, '2024-10-14', '2024-2025'),
(1045, 'Paiement CNSS novembre & décembre 2023 (dépense)', 0.00, 645.00, 0.00, '2024-10-14', '2024-2025'),
(1046, 'Acompte seau claude impression (dépense)', 0.00, 1000.00, 0.00, '2024-10-11', '2024-2025'),
(1047, 'Avance salaire Cibangu Kabibi (dépense)', 0.00, 10.00, 0.00, '2024-10-11', '2024-2025'),
(1048, 'Achat 3 mousses de 8cm (dépense)', 0.00, 49.00, 0.00, '2024-10-10', '2024-2025'),
(1049, 'Solde chemise Turquie  (dépense)', 0.00, 403.00, 0.00, '2024-10-10', '2024-2025'),
(1050, 'Quotité Mme Maria + frais (dépense)', 0.00, 475.00, 0.00, '2024-10-15', '2024-2025'),
(1051, 'Ajout sur CNSS novembre & Décembre (dépense)', 0.00, 11.00, 0.00, '2024-10-15', '2024-2025'),
(1052, 'IPR septembre 2024 (dépense)', 0.00, 99.00, 0.00, '2024-10-15', '2024-2025'),
(1053, 'Quinzaine personnel octobre 2024 (dépense)', 0.00, 1000.00, 0.00, '2024-10-15', '2024-2025'),
(1054, 'Entretient et installation toutes les salles (dépense)', 0.00, 400.00, 0.00, '2024-10-17', '2024-2025'),
(1055, 'Achat fourniture bureaux (dépense)', 0.00, 76.00, 0.00, '2024-10-17', '2024-2025'),
(1056, 'Paiement couturier M.O avance (dépense)', 0.00, 93.00, 0.00, '2024-10-18', '2024-2025'),
(1057, 'facture REGIDESO septembre 2024 (dépense)', 0.00, 56.00, 0.00, '2024-10-28', '2024-2025'),
(1058, 'Transport avocat + sceaux DG (dépense)', 0.00, 170.00, 0.00, '2024-10-28', '2024-2025'),
(1059, 'Entretient porte & fenêtre bureau avocat (dépense)', 0.00, 106.00, 0.00, '2024-10-28', '2024-2025'),
(1060, 'Dossier DGRK (200$), Remise KUMESO (50$) et Finition bureau (60$) (dépense)', 0.00, 310.00, 0.00, '2024-10-10', '2024-2025'),
(1061, 'Achat segment, piston, bougie, réparation Groupe Électrogène (dépense)', 0.00, 45.00, 0.00, '2024-10-11', '2024-2025'),
(1062, 'Quotité MR MPUTU MOIS DE MAI 2025 (dépense)', 0.00, 500.00, 0.00, '2025-06-16', '2024-2025'),
(1063, 'Paiement facture regideso mois de mai 2025 et achat carburant (dépense)', 0.00, 35.00, 0.00, '2025-06-11', '2024-2025'),
(1064, 'Achat carburant (dépense)', 0.00, 7.00, 0.00, '2025-06-09', '2024-2025'),
(1065, 'Achat carburant (dépense)', 0.00, 7.00, 0.00, '2025-06-03', '2024-2025'),
(1066, 'Retenue sur loyer trimestre 2025 (dépense)', 0.00, 455.00, 0.00, '2025-06-09', '2024-2025'),
(1067, 'Declaration IPR/DGI DE MAI (dépense)', 0.00, 104.00, 0.00, '2025-06-09', '2024-2025'),
(1068, 'declaration cotisation cnss mois d\'avril (dépense)', 0.00, 265.00, 0.00, '2025-06-09', '2024-2025'),
(1069, 'solde loyer mois de juin (dépense)', 0.00, 2555.00, 0.00, '2025-06-09', '2024-2025'),
(1070, 'facture REGIDESO mois de mai et acaht carburant (dépense)', 0.00, 35.00, 0.00, '2025-06-11', '2024-2025'),
(1071, 'paiement frais scolaire', 100.00, 0.00, 0.00, '2025-07-11', '2025-2026');

-- --------------------------------------------------------

--
-- Structure de la table `classe`
--

CREATE TABLE `classe` (
  `id` int(11) NOT NULL,
  `description` varchar(20) NOT NULL,
  `cycle` int(11) NOT NULL,
  `dateCreaty` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createdby` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `classe`
--

INSERT INTO `classe` (`id`, `description`, `cycle`, `dateCreaty`, `dateUpdate`, `createdby`) VALUES
(1, '1ère ', 1, '2025-02-24', '2025-02-24', 'Administrateur(trice)'),
(2, '2è', 1, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(3, '3è', 1, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(4, '1er', 2, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(5, '2è', 2, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(6, '3è', 2, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(7, '4è', 2, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(8, '5è', 2, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(9, '6è', 2, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(10, '7è', 3, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(11, '8è', 3, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(12, '1er', 4, '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(13, '2è', 4, '2025-03-07', '2025-03-07', 'Administrateur(trice)');

-- --------------------------------------------------------

--
-- Structure de la table `correction_devoir_ensignant`
--

CREATE TABLE `correction_devoir_ensignant` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `assignment_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `correction_devoir_ensignant`
--

INSERT INTO `correction_devoir_ensignant` (`id`, `user_id`, `question_id`, `assignment_id`, `answer_text`, `submission_date`) VALUES
(1, 1, 1, 1, '800', '2025-06-05 09:12:10'),
(2, 1, 2, 1, '200000', '2025-06-05 09:12:10'),
(3, 1, 3, 1, '1', '2025-06-05 09:12:10');

-- --------------------------------------------------------

--
-- Structure de la table `cycle`
--

CREATE TABLE `cycle` (
  `id` int(11) NOT NULL,
  `description` varchar(20) NOT NULL,
  `dateCreaty` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createby` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `cycle`
--

INSERT INTO `cycle` (`id`, `description`, `dateCreaty`, `dateUpdate`, `createby`) VALUES
(1, 'MATERNELLE', '2025-02-24', '2025-02-24', 'Administrateur(trice)'),
(2, 'PRIMAIRE ', '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(3, 'E.B (SECONDAIRE)', '2025-03-07', '2025-03-07', 'Administrateur(trice)'),
(4, 'HUMANITÉ ', '2025-03-07', '2025-03-07', 'Administrateur(trice)');

-- --------------------------------------------------------

--
-- Structure de la table `depenses`
--

CREATE TABLE `depenses` (
  `id` int(11) NOT NULL,
  `description` text NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `dateCreaty` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createdby` text NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `depenses`
--

INSERT INTO `depenses` (`id`, `description`, `montant`, `dateCreaty`, `dateUpdate`, `createdby`, `anneeScolaire`) VALUES
(1, 'ACHAT CARBURANT', 6.00, '2025-03-28', '2025-03-28', 'Administrateur(trice)', '2024-2025'),
(2, 'COLLATION CARBURANT CONSEIL D\'ADMINISTRATION', 45.00, '2025-03-31', '2025-03-31', 'Administrateur(trice)', '2024-2025'),
(3, 'SOLDE SALAIRE PERSONNEL ENSEIGNANT MOIS DE MARS 2025', 4100.00, '2025-04-01', '2025-04-01', 'Administrateur(trice)', '2024-2025'),
(4, 'PAIEMENT FACTURE SNEL MARS 2025 + ACHAT CARBURANT MERCREDI ET JEUDI', 22.30, '2025-04-03', '2025-04-03', 'Administrateur(trice)', '2024-2025'),
(5, 'QUINZAINE (AVANCE SUR SALAIRE) MOIS D\'AVRIL 2025', 1070.00, '2025-04-24', '2025-04-24', 'Administrateur(trice)', '2024-2025'),
(6, 'PAIEMENT SALAIRE DGAF ET AVOCAT MOIS DE MARS 2025', 1000.00, '2025-04-23', '2025-04-23', 'Administrateur(trice)', '2024-2025'),
(7, 'PAIEMENT LOYER MOIS D\'AVRIL 2025', 3005.00, '2025-04-23', '2025-04-23', 'Administrateur(trice)', '2024-2025'),
(8, 'TRANSPORT DGAF CNSS ET ASSONEPA BANQUE; TRANSPORT AVOCAT DOSSIER DIMBA INSPECTION DU TRAVAIL.', 70.00, '2025-04-23', '2025-04-23', 'Administrateur(trice)', '2024-2025'),
(9, 'QUOTITE MADAME MARIA MOIS D\'AVRIL 2025', 520.00, '2025-04-23', '2025-04-23', 'Administrateur(trice)', '2024-2025'),
(10, 'REABONNEMENT ROUTER AIRTEL ECOLE', 52.00, '2025-04-23', '2025-04-23', 'Administrateur(trice)', '2024-2025'),
(11, 'ENTRETIEN ET REPARATION IMPRIMANTE', 37.00, '2025-04-23', '2025-04-23', 'Administrateur(trice)', '2024-2025'),
(12, 'ACHAT CARBURANT DU MERCREDI 16 MARS 2025 ET TRANSPORT NGOMA CHRIS DGRK DEPOT DECLARATION 1er TRIMESTRE 2025', 12.00, '2025-04-16', '2025-04-16', 'Administrateur(trice)', '2024-2025'),
(13, 'PAIEMENT FACTURE REGIDESO MARS 2025', 34.00, '2025-04-11', '2025-04-11', 'Administrateur(trice)', '2024-2025'),
(14, 'ACHAT CARBURANT DU MARDI 08 AVRIL 2025', 6.00, '2025-04-08', '2025-04-08', 'Administrateur(trice)', '2024-2025'),
(15, 'REPARATION GROUPE ET ACHAT TUYAU CARBURATEUR', 10.00, '2025-04-04', '2025-04-04', 'Administrateur(trice)', '2024-2025'),
(16, 'ACHAT CARBURANT DU LUNDI 28 AVRIL 2025', 10.00, '2025-04-29', '2025-04-29', 'Administrateur(trice)', '2024-2025'),
(17, 'SOLDE SALAIRE PERSONNEL MOIS D_AVRIL 2025', 5550.00, '2025-04-29', '2025-04-29', 'Administrateur(trice)', '2024-2025'),
(18, 'SOLDE COMPTE MONSIEUR MPUTU ACAHT UNIFORME ET FRAIS ALBUM PHOTOS SORTIE SCOLAIRE', 430.00, '2025-04-29', '2025-04-29', 'Administrateur(trice)', '2024-2025'),
(19, 'FRAIS DE FONCTIONNEMENT DIRECTIONS MATERNELLE ET PRIMAIRE', 65.00, '2025-04-29', '2025-04-29', 'Administrateur(trice)', '2024-2025'),
(20, 'CARBURANT GESTIONNAIRE DGI MADAME ESTHER ET DGAF', 45.00, '2025-04-29', '2025-04-29', 'Administrateur(trice)', '2024-2025'),
(21, 'ACHAT 6 PAQUETS STULOS (BLUE, NOIR, ROUGE) BUREAU ET ACHAT CLASSEUR BUREAU DG', 62.00, '2025-05-05', '2025-05-05', 'Administrateur(trice)', '2024-2025'),
(22, 'COLLATION PERSONNEL DE LA JOURNEE DU 30 AVRIL 2025 ET ENTRETIENT GROUP ELECTROGENE', 154.00, '2025-05-05', '2025-05-05', 'Administrateur(trice)', '2024-2025'),
(23, 'ACHAT CARBURANT GROUPE', 6.00, '2025-05-08', '2025-05-11', 'Administrateur(trice)', '2024-2025'),
(24, 'PAIEMENT FACTURE SNEL MOIS D\'AVRIL 2025', 12.00, '2025-05-06', '2025-05-08', 'Administrateur(trice)', '2024-2025'),
(25, 'ENTRETIENT ¨REMIER BLOC DES 3 WC', 300.00, '2025-05-06', '2025-05-08', 'Administrateur(trice)', '2024-2025'),
(26, 'PAIEMENT LOYER MOIS DE MAI 2025', 3005.00, '2025-05-06', '2025-05-08', 'Administrateur(trice)', '2024-2025'),
(27, 'PAIEMENT LOYER MOIS DE MAI CONTRIBUTION CNSS MOIS DE MARS 2025', 275.00, '2025-05-09', '2025-05-12', 'Administrateur(trice)', '2024-2025'),
(28, 'AVANCE MAINS D\'OEUVRE PLOMBIER', 43.00, '2025-05-09', '2025-05-12', 'Administrateur(trice)', '2024-2025'),
(29, 'ACHAT JAVEL, DETERGENTS, CARBURANT ET TRANSPORT', 60.00, '2025-05-09', '2025-05-12', 'Administrateur(trice)', '2024-2025'),
(30, 'PAIEMENT FACTURE REGIDESO MOIS D\'AVRIL 2025 ET PALMARES TENASOSP SOUS-DIVISION', 25.00, '2025-05-09', '2025-05-09', 'Administrateur(trice)', '2024-2025'),
(31, 'ENTRETIENT ET INSTALLATION ORDINATEUR ORDINATEURS BUREAU', 70.00, '2025-05-15', '2025-05-15', 'Administrateur(trice)', '2024-2025'),
(32, 'TRANSPORT POUBELLE, ACHAT 2 FUSIBLES + CARBURANT DU MARDI, MERCREDI ET JEUDI 15/05/2025 ET TRANSPORT DGAF CNSS', 40.00, '2025-05-15', '2025-05-15', 'Administrateur(trice)', '2024-2025'),
(33, 'ENTRETIENT GROUPE', 30.00, '2025-05-20', '2025-05-21', 'Administrateur(trice)', '2024-2025'),
(34, 'QUOTITE MADAME MARIA MOIS DE MAI 2025', 520.00, '2025-05-20', '2025-05-21', 'Administrateur(trice)', '2024-2025'),
(35, 'NEGOCIATION DOSSIER DGI CONTROLE PENALITES', 400.00, '2025-05-27', '2025-05-27', 'Administrateur(trice)', '2024-2025'),
(36, 'REABONNEMENT ROUTER ET ACHAT CARBURANT', 60.00, '2025-05-27', '2025-05-27', 'Administrateur(trice)', '2024-2025'),
(37, 'PAIEMENT FACTURE SNEL MOIS DE MAI 2025', 9.00, '2025-06-03', '2025-06-03', 'Administrateur(trice)', '2024-2025'),
(38, 'PAIEMENT SALAIRE PERSONNEL MOIS DE MAI 2025', 4010.00, '2025-06-03', '2025-06-03', 'Administrateur(trice)', '2024-2025'),
(39, 'Loyer juillet 2024 + Transport', 1305.00, '2024-07-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(40, 'Transport agent', 14.00, '2024-07-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(41, 'manquant caisse', 20.61, '2024-07-23', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(42, 'soins MADAME BUTSHI', 250.00, '2024-07-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(43, 'reparation groupe electrogene', 30.35, '2024-07-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(44, 'Avance sur salaire Mr FAYA ARTHUR', 150.00, '2024-07-22', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(45, 'reglement facture REGIDESO', 57.00, '2024-07-22', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(46, 'Frais couverture PALMARES', 18.00, '2024-07-22', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(49, 'solde loyer juillet et transpot', 1710.00, '2024-08-09', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(50, 'achat seaux ', 80.00, '2024-08-10', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(51, 'salaire agent juin ', 1699.00, '2024-08-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(52, 'honoraire DAF', 400.00, '2024-08-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(53, 'honoraire DAF', 400.00, '2024-08-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(54, 'frais funeraire Mme BITSHI', 500.00, '2024-08-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(55, 'solde salaire juin', 1657.00, '2024-08-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(56, 'achat imprimante et transport', 700.00, '2024-08-24', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(57, 'supplement achat imprimante et transport', 70.00, '2024-08-29', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(58, 'loyer aout 2024', 3010.00, '2024-08-29', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(59, 'solde salaire juillet et aout Mukendi', 238.00, '2024-08-31', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(60, 'salaire juillet et aout DIMBA et MOYOYO', 486.00, '2024-09-03', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(61, 'Avance sur salaire juillet 2024', 1656.00, '2024-09-04', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(62, 'versement caisse centrale ', 11700.00, '2024-09-04', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(63, 'solde decomptes finaux DIMBA et MOYOYO', 1387.41, '2024-09-07', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(64, 'envoi Mme MARIA', 305.00, '2024-09-07', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(65, 'restitution FS SUDIKA KIMODO', 135.00, '2024-09-09', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(66, 'frais d\'envoi Mme MARIA', 17.00, '2024-09-10', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(67, 'loyer semptembre', 3010.00, '2024-09-12', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(68, 'facture REGIDESO', 56.03, '2024-09-16', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(69, 'quinzine sept 2024', 980.00, '2024-09-16', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(70, '', 0.00, '0000-00-00', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(71, 'achat fourniture ', 257.24, '2024-09-21', '2025-07-02', 'Administrateur(trice)', '2024-2025'),
(72, 'AVANCE SALAIRE SEPTEMBRE 2024 QUINZAINE', 60.00, '2024-09-17', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(73, 'QUOTITE MME MARIA', 250.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(74, 'FRAIS ENVOI QUOTITE MME MARIA', 20.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(75, 'FRAIS JUSTICE & ENGAGEMENT AVOCAT', 450.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(76, 'ACHAT & FABRICATION PORTE & FENETRE', 850.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(77, 'AVANCE SALAIRE SEPTEMBRE TSHISHIMBI THIERRY', 70.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(78, 'ACHAT TS (8D7)', 320.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(79, 'avance sur salaire setempbre tshishimbi', 30.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(80, 'M.O Maçon bureau et achat ciment + sable', 100.00, '2024-09-28', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(81, 'achat peinture + papier peint bureau', 145.00, '2024-09-30', '2025-07-03', 'Administrateur(trice)', '2024-2025'),
(82, 'solde salaire kinkela & transport de séance samedi 02.11.2024', 200.00, '2024-11-04', '2025-07-07', 'Administrateur(trice)', '2024-2025'),
(83, 'solde salaire oct 2024', 1000.00, '2024-11-05', '2025-07-07', 'Administrateur(trice)', '2024-2025'),
(84, 'solde couturière ', 139.00, '2024-11-05', '2025-07-07', 'Administrateur(trice)', '2024-2025'),
(85, 'reparution imprimante', 30.00, '2024-11-06', '2025-07-07', 'Administrateur(trice)', '2024-2025'),
(86, 'achat table, transport loyer novembre 2024', 3160.00, '2024-11-07', '2025-07-07', 'Administrateur(trice)', '2024-2025'),
(87, 'Rbt FS mwalumba', 100.00, '2024-11-07', '2025-07-07', 'Administrateur(trice)', '2024-2025'),
(88, 'frais me faustin (avocat)', 500.00, '2024-11-11', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(89, 'achat carburant', 6.00, '2024-11-11', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(90, 'transport ilunga', 6.00, '2024-11-11', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(91, 'transport personnel', 18.00, '2024-11-11', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(92, 'achat table bureau + 1 chaise', 380.00, '2024-11-11', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(93, 'achat 1bt craie', 51.00, '2024-11-11', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(94, 'achat matériel pour maternelle', 34.00, '2024-11-13', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(95, 'Quinzaine novembre 2024', 1120.00, '2024-11-15', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(96, 'paiement CNSS novembre 2023', 314.00, '2024-11-15', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(97, 'paiement CNSS octobre 2024', 136.00, '2024-11-15', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(98, 'paiement IPR octobre 2023', 103.00, '2024-11-15', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(99, 'ajout quinzaine novembre 2024', 100.00, '2024-11-15', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(100, 'collation 16.11.2024', 130.00, '2024-11-16', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(101, 'Loyer', 2560.00, '2024-12-10', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(102, 'avance salaire pret', 200.00, '2024-12-12', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(103, 'salaire', 11712.00, '2024-12-06', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(104, 'honoraires avocat', 500.00, '2024-12-02', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(105, 'carburant', 6.00, '2024-12-10', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(106, 'CNSS', 65.00, '2024-12-13', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(107, 'IPR', 94.00, '2024-12-13', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(108, 'IRL', 450.00, '2024-12-11', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(109, 'regideso', 38.00, '2024-12-16', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(110, 'achat divers', 2400.00, '2024-12-26', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(111, 'transport', 130.00, '2024-12-28', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(112, 'autres', 1141.00, '2024-12-26', '2025-07-08', 'Administrateur(trice)', '2024-2025'),
(113, 'Loyer ', 3.00, '2025-01-09', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(114, 'Quinzaine ', 1.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(115, 'Avance sur salaire/prêt ', 40.00, '2025-01-13', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(116, 'Salaire ', 6.00, '2025-01-31', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(117, 'Quotité Mme Maria et Mme Flora ', 1.00, '2025-01-21', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(118, 'Frais de facturation ', 41.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(119, 'Fournitures de bureau ', 6.00, '2025-01-31', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(120, 'Carburant ', 25.00, '2025-01-23', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(121, 'Entretiens ', 50.00, '2025-01-13', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(122, 'CNSS', 57.00, '2025-01-13', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(123, 'Décompte final ', 2000.00, '2025-01-20', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(124, 'Regideso', 21.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(125, 'Dossier inspection du travail ', 390.00, '2025-01-22', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(126, 'Autres dépenses ', 1.00, '2025-01-09', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(127, 'Transport ', 26.00, '2025-01-02', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(128, 'Assistante sociale ', 160.00, '2025-01-21', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(129, 'Collation CG (comité de gestion)', 60.00, '2025-01-21', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(130, 'Transport avocat et informaticien ', 20.00, '2025-01-02', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(131, 'Loyer janvier 2025', 3.00, '2025-01-09', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(132, 'Solde salaire personnel décembre 2024', 1000.00, '2025-01-09', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(133, 'Solde dette JC ', 1.00, '2025-01-09', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(134, 'Collation CG (comité de gestion) 11.01.2025', 60.00, '2025-01-13', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(135, 'Prêt finga ', 40.00, '2025-01-13', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(136, 'Achat pièce et réparation imprimante ', 50.00, '2025-01-13', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(137, 'Paie ipr(101,3$) et CNSS (58,6$)', 159.00, '2025-01-13', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(138, 'Ff David ', 20.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(139, 'Ff Huguette mitongo', 20.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(140, 'Transport Delphine dgi et CNSS ', 6.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(141, 'Facture regideso ', 21.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(142, 'Quinzaine janvier 2025', 1.00, '2025-01-14', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(143, 'Décompte final personnel fin contrat ', 2000.00, '2025-01-20', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(144, 'Assistance David décès enfant ', 100.00, '2025-01-21', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(145, 'Quotité Mme Maria janvier 2025', 520.00, '2025-01-20', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(146, 'Dossier inspection du travail ', 390.00, '2025-01-22', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(147, 'Solde salaire janvier personnel sortant ', 572.00, '2025-01-23', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(148, 'Achat carburant et vidange poubelle ', 25.00, '2025-01-23', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(149, 'Quotité Mme Elena janvier 2025', 500.00, '2025-01-27', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(150, 'Assistance sœur Irène décès papa ', 60.00, '2025-01-27', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(151, 'Solde salaire personnel janvier 2025', 4.00, '2025-01-30', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(152, 'Solde salaire Ilunga juillet 2024', 80.00, '2025-01-30', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(153, 'Achat fournitures ', 69.00, '2025-01-31', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(154, 'collation nombre cg', 100.00, '2025-02-03', '2025-07-09', 'Administrateur(trice)', '2024-2025'),
(155, 'Ajout arrière Juillet & Août 2024 personnel sortant', 400.00, '2025-02-25', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(156, 'Ajout arrière Juillet & Août 2024 personnel sortant', 400.00, '2025-02-27', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(157, 'Solde personnel mois février 2025', 5370.00, '2025-02-28', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(158, 'Frais Inspection du travail', 50.00, '2025-02-28', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(159, 'Salaire février Mme Lubau', 200.00, '2025-02-28', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(160, 'Paiement facture SNEL Janvier 2025', 27.50, '2025-02-03', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(161, 'impôt /loyer février', 600.00, '2025-02-04', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(162, 'F/Rebecca appl. de la gestion', 150.00, '2025-02-04', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(163, 'légalisation statut école', 200.00, '2025-02-06', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(164, 'Loyer février 2025', 2410.00, '2025-02-10', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(165, 'FF Tshibuabua & Mitongo', 62.00, '2025-02-10', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(166, 'Solde salaire juillet 2024 Tshibuabua & Finga, Mputu', 360.00, '2025-02-11', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(167, 'paiement CNSS & IPR Janvier 2025', 169.90, '2025-02-11', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(168, 'Avance sur salaire prof mputu héritier ', 80.00, '2025-02-12', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(169, 'Solde arrièré juillet & août 2024 parfait', 1950.00, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(170, 'Quotité Mme Maria février 2025', 520.00, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(171, 'Quotité Mme Elena février 2025', 500.00, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(172, 'Décompte final Mme REBECCA', 863.00, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(173, 'facture REGIDESO janvier 2025', 27.50, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(174, 'formation enseignant & educ', 150.00, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(175, 'Quinzaine Février 2025', 1150.00, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(176, 'Acompte Mr Mputu prêt à l\'école', 500.00, '2025-02-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(177, 'Ajout frais arriéré Juillet & Août 2024', 270.00, '2025-02-20', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(178, 'Achat carburant', 6.00, '2025-02-20', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(179, 'solde salaire personnel sept 2024', 5800.00, '2024-10-03', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(180, 'Facture SNEL septembre', 17.00, '2024-10-01', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(181, 'Facture SNEL le 29.10.2024', 14.00, '2024-10-29', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(182, 'Paiement salaire personnel octobre 2024', 4700.00, '2024-10-30', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(183, 'Loyer octobre 2024', 3005.00, '2024-10-08', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(184, 'Avance salaire ilunga octobre 2024', 50.00, '2024-10-09', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(185, 'Entretient Groupe Électrogène (20$), Transport DGI (40$) & Transport personnel (15$)', 75.00, '2024-10-14', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(186, 'Paiement CNSS novembre & décembre 2023', 645.00, '2024-10-14', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(187, 'Acompte seau claude impression', 1000.00, '2024-10-11', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(188, 'Avance salaire Cibangu Kabibi', 10.00, '2024-10-11', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(189, 'Achat 3 mousses de 8cm', 49.00, '2024-10-10', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(190, 'Solde chemise Turquie ', 403.00, '2024-10-10', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(191, 'Quotité Mme Maria + frais', 475.00, '2024-10-15', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(192, 'Ajout sur CNSS novembre & Décembre', 11.00, '2024-10-15', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(193, 'IPR septembre 2024', 99.00, '2024-10-15', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(194, 'Quinzaine personnel octobre 2024', 1000.00, '2024-10-15', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(195, 'Entretient et installation toutes les salles', 400.00, '2024-10-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(196, 'Achat fourniture bureaux', 76.00, '2024-10-17', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(197, 'Paiement couturier M.O avance', 93.00, '2024-10-18', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(198, 'facture REGIDESO septembre 2024', 56.00, '2024-10-28', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(199, 'Transport avocat + sceaux DG', 170.00, '2024-10-28', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(200, 'Entretient porte & fenêtre bureau avocat', 106.00, '2024-10-28', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(201, 'Dossier DGRK (200$), Remise KUMESO (50$) et Finition bureau (60$)', 310.00, '2024-10-10', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(202, 'Achat segment, piston, bougie, réparation Groupe Électrogène', 45.00, '2024-10-11', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(203, 'Quotité MR MPUTU MOIS DE MAI 2025', 500.00, '2025-06-16', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(204, 'Paiement facture regideso mois de mai 2025 et achat carburant', 35.00, '2025-06-11', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(205, 'Achat carburant', 7.00, '2025-06-09', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(206, 'Achat carburant', 7.00, '2025-06-03', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(207, 'Retenue sur loyer trimestre 2025', 455.00, '2025-06-09', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(208, 'Declaration IPR/DGI DE MAI', 104.00, '2025-06-09', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(209, 'declaration cotisation cnss mois d\'avril', 265.00, '2025-06-09', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(210, 'solde loyer mois de juin', 2555.00, '2025-06-09', '2025-07-10', 'Administrateur(trice)', '2024-2025'),
(211, 'facture REGIDESO mois de mai et acaht carburant', 35.00, '2025-06-11', '2025-07-10', 'Administrateur(trice)', '2024-2025');

-- --------------------------------------------------------

--
-- Structure de la table `eleve`
--

CREATE TABLE `eleve` (
  `id` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `postnom` varchar(20) NOT NULL,
  `prenom` varchar(20) NOT NULL,
  `genre` varchar(1) NOT NULL,
  `lieu` varchar(20) NOT NULL,
  `dateDeNaissance` date NOT NULL,
  `classe` int(11) NOT NULL,
  `menage` int(11) NOT NULL,
  `dateCreated` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL,
  `createdby` varchar(25) NOT NULL,
  `montant_a_payer` double(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `eleve`
--

INSERT INTO `eleve` (`id`, `nom`, `postnom`, `prenom`, `genre`, `lieu`, `dateDeNaissance`, `classe`, `menage`, `dateCreated`, `dateUpdate`, `anneeScolaire`, `createdby`, `montant_a_payer`) VALUES
(2, 'PETSHI', '', '', '', '', '0000-00-00', 3, 3, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(3, 'BAKA ', '', '', '', '', '0000-00-00', 3, 12, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(4, 'BAKOLA ', '', '', '', '', '0000-00-00', 3, 13, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(5, 'KABAKEBA ', 'BALUME', '', '', '', '0000-00-00', 1, 15, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(6, 'BASELE ', '', '', '', '', '0000-00-00', 2, 19, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(7, 'SIAMPO', '', '', '', '', '0000-00-00', 1, 24, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(8, 'BASEKONDA ', '', '', '', '', '0000-00-00', 2, 30, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(9, 'BOTUNGA ', '', '', '', '', '0000-00-00', 3, 32, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(10, 'LUKEBADIO', 'NDOMBE', '', '', '', '0000-00-00', 3, 36, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(11, 'DJUMA ', '', '', '', '', '0000-00-00', 3, 38, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(12, 'ELUMBU ', '', '', '', '', '0000-00-00', 1, 43, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(13, 'GENE', 'EBUO', '', '', '', '0000-00-00', 1, 47, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(14, 'MUKUBITO', '', '', '', '', '0000-00-00', 2, 49, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(15, 'LUZOLOTSASA', '', '', '', '', '0000-00-00', 3, 50, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(16, 'IDI ', '', '', '', '', '0000-00-00', 1, 51, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(17, 'NGOYI ', '', '', '', '', '0000-00-00', 3, 56, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(18, 'MASIKA', '', '', '', '', '0000-00-00', 3, 73, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(19, 'KAMBALE ', '', '', '', '', '0000-00-00', 3, 77, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(20, 'NTUMBA', '', '', '', '', '0000-00-00', 3, 79, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(21, 'MAPENZI', '', '', '', '', '0000-00-00', 3, 91, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(22, 'KATEMBO ', '', '', '', '', '0000-00-00', 3, 92, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(23, 'KAYEMBE ', 'LOKONGI', '', '', '', '0000-00-00', 3, 97, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(24, 'KINZANZA ', '', '', '', '', '0000-00-00', 2, 105, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(25, 'KISIATA ', '', '', '', '', '0000-00-00', 2, 107, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(26, 'KITOKO ', '', '', '', '', '0000-00-00', 2, 108, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(27, 'KONGA ', '', '', '', '', '0000-00-00', 1, 112, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(28, 'MASANKA', '', '', '', '', '0000-00-00', 3, 113, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(29, 'LOMBOMBA ', '', '', '', '', '0000-00-00', 2, 119, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(30, 'AZIZA', '', '', '', '', '0000-00-00', 3, 245, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(31, 'LUTUTA ', '', '', '', '', '0000-00-00', 3, 127, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(32, 'MABANGA ', '', '', '', '', '0000-00-00', 2, 129, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(33, 'DELVO', '', '', '', '', '0000-00-00', 3, 130, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(34, 'MALONGA ', '', '', '', '', '0000-00-00', 3, 134, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(35, 'BULE', '', '', '', '', '0000-00-00', 2, 135, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(36, 'MANGILA ', '', '', '', '', '0000-00-00', 3, 139, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(37, 'MALABA', 'MATANDA', '', '', '', '0000-00-00', 2, 144, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(38, 'MAKULU', '', '', '', '', '0000-00-00', 2, 145, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(39, 'MAVAMBOU ', '', '', '', '', '0000-00-00', 3, 146, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(40, 'MAWELU ', '', '', '', '', '0000-00-00', 2, 246, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(41, 'MAYEKO ', '', '', '', '', '0000-00-00', 3, 148, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(42, 'NDAYA', '', '', '', '', '0000-00-00', 2, 150, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(43, 'BUIMA', '', '', '', '', '0000-00-00', 3, 168, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(44, 'TSHIBOLA', '', '', '', '', '0000-00-00', 3, 170, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(45, 'BOKATUKA', '', '', '', '', '0000-00-00', 2, 171, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(46, 'MUANYO ', '', '', '', '', '0000-00-00', 3, 172, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(47, 'MBALA', '', '', '', '', '0000-00-00', 3, 177, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(48, 'MULAND ', '', '', '', '', '0000-00-00', 3, 181, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(49, 'MULOBO ', '', '', '', '', '0000-00-00', 3, 182, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(50, 'MULOBO ', '', '', '', '', '0000-00-00', 2, 182, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(51, 'NZELO', '', '', '', '', '0000-00-00', 3, 188, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(52, 'NGANDU', '', '', '', '', '0000-00-00', 3, 189, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(53, 'MWAMBA ', '', '', '', '', '0000-00-00', 3, 194, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(54, 'NGAWARA ', '', '', '', '', '0000-00-00', 3, 197, '2025-03-07', '2025-03-07', '2025-2026', 'Administrateur(trice)', 400.00),
(55, 'NTAMBWE ', '', '', '', '', '0000-00-00', 3, 204, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(56, 'SELEMANI ', '', '', '', '', '0000-00-00', 3, 218, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(57, 'SHUNGU ', '', '', '', '', '0000-00-00', 1, 222, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(58, 'KAYEMBE ', '', '', '', '', '0000-00-00', 3, 226, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(59, 'BEYA', '', '', '', '', '0000-00-00', 3, 227, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(60, 'BILONDA ', '', '', '', '', '0000-00-00', 3, 239, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(61, 'URWODI ', '', '', '', '', '0000-00-00', 3, 242, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(62, 'KONDE ', '', '', '', '', '0000-00-00', 2, 243, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(63, 'ADJANGA ', 'GODOBIA', '', '', '', '0000-00-00', 7, 2, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(64, 'ADJANGA ', 'MASAMBI ', '', '', '', '0000-00-00', 5, 2, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(65, 'ENYELA', 'EGOLA', '', '', '', '0000-00-00', 8, 4, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(66, 'ASSAF', 'AMIRE', '', '', '', '0000-00-00', 6, 5, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(67, 'ATAMA ', 'ATOBI ', '', '', '', '0000-00-00', 5, 6, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(68, 'ILUMBE ', '', '', '', '', '0000-00-00', 8, 7, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(69, 'TSHIAMANINA', '', '', '', '', '0000-00-00', 6, 8, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(70, 'NGALULA', '', '', '', '', '0000-00-00', 8, 9, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(71, 'MALU', '', '', '', '', '0000-00-00', 5, 9, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(72, 'NTUMBA', '', '', '', '', '0000-00-00', 7, 10, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(73, 'KATENDE', '', '', '', '', '0000-00-00', 7, 11, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(74, 'KATUMBAYI', '', '', '', '', '0000-00-00', 6, 11, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(75, 'BAKOLA', '', '', '', '', '0000-00-00', 7, 13, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(76, 'BAKONGO', '', '', '', '', '0000-00-00', 6, 14, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(77, 'BAMBA', '', '', '', '', '0000-00-00', 7, 16, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(78, 'BAMBALA', '', '', '', '', '0000-00-00', 8, 17, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(79, 'KABAMBA', '', '', '', '', '0000-00-00', 6, 18, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(80, 'BASANGA', '', '', '', '', '0000-00-00', 8, 20, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(81, 'BASSALWA', '', '', '', '', '0000-00-00', 8, 22, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(82, 'BASSALWA', '', '', '', '', '0000-00-00', 7, 22, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(83, 'BENDERA', '', 'EXAUCE', '', '', '0000-00-00', 9, 23, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(84, 'BANDERA', 'BIZURI', '', '', '', '0000-00-00', 7, 23, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(85, 'LUMBALA', '', '', '', '', '0000-00-00', 4, 25, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(86, 'MUJINGA', '', '', '', '', '0000-00-00', 7, 25, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(87, 'ILUNGA', '', '', '', '', '0000-00-00', 4, 26, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(88, 'BOBUTAKA', '', '', '', '', '0000-00-00', 9, 28, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(89, 'BOSEKOTA', '', '', '', '', '0000-00-00', 6, 31, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(90, 'BUKASA', '', '', '', '', '0000-00-00', 8, 33, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(91, 'BUKASA', '', '', '', '', '0000-00-00', 6, 33, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(92, 'NKANKOLONGO', '', '', '', '', '0000-00-00', 3, 34, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(93, 'MBUYA', '', '', '', '', '0000-00-00', 7, 34, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(94, 'MITONGO', '', '', '', '', '0000-00-00', 8, 35, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(95, 'SHAKO', '', '', '', '', '0000-00-00', 5, 35, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(96, 'DIKISI', '', '', '', '', '0000-00-00', 4, 37, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(97, 'EKABA', '', '', '', '', '0000-00-00', 9, 39, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(98, 'EKOFO', '', '', '', '', '0000-00-00', 4, 41, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(99, 'MUNGUANGUA', '', '', '', '', '0000-00-00', 4, 44, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(100, 'FUKINSIA', '', '', '', '', '0000-00-00', 8, 46, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(101, 'GENE', '', 'EBEN', '', '', '0000-00-00', 2, 47, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(102, 'GENE', '', '', '', '', '0000-00-00', 5, 47, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(103, 'GULUNGA', '', '', '', '', '0000-00-00', 6, 48, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(104, 'GULUNGA', '', '', '', '', '0000-00-00', 5, 48, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(105, 'GULUNGA', '', '', '', '', '0000-00-00', 4, 48, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(106, 'IKIYO', '', '', '', '', '0000-00-00', 8, 52, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(107, 'DISANKA', '', '', '', '', '0000-00-00', 8, 53, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(108, 'BUKUMBA', '', '', '', '', '0000-00-00', 4, 53, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(109, 'ILUMBE', '', '', '', '', '0000-00-00', 6, 54, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(110, 'ILUNGA', '', '', '', '', '0000-00-00', 4, 55, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(111, 'MUTUALE', '', '', '', '', '0000-00-00', 9, 56, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(112, 'KABAMBI', '', '', '', '', '0000-00-00', 6, 57, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(113, 'KASONGO', '', '', '', '', '0000-00-00', 5, 58, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(114, 'BUENDE', '', '', '', '', '0000-00-00', 8, 59, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(115, 'MUKENDI', '', '', '', '', '0000-00-00', 6, 59, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(116, 'KABEYA', '', '', '', '', '0000-00-00', 5, 60, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(117, 'NGALAMULUME', '', '', '', '', '0000-00-00', 7, 61, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(118, 'BANGAMBE', '', '', '', '', '0000-00-00', 8, 61, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(119, 'MPUTU', '', '', '', '', '0000-00-00', 9, 62, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(120, 'NGOYA', '', '', '', '', '0000-00-00', 9, 62, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(121, 'DIBANZILUA', '', '', '', '', '0000-00-00', 7, 62, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(122, 'NZEBA', '', '', '', '', '0000-00-00', 7, 63, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(123, 'TSHIKURU', '', '', '', '', '0000-00-00', 9, 64, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(124, 'LUBULA', '', '', '', '', '0000-00-00', 7, 64, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(125, 'KABUYA', '', '', '', '', '0000-00-00', 4, 65, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(126, 'LUHONGO', '', '', '', '', '0000-00-00', 6, 66, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(127, 'NGALULA', '', '', '', '', '0000-00-00', 6, 67, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(128, 'KALALA', '', '', '', '', '0000-00-00', 7, 68, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(129, 'NTUMBA', '', '', '', '', '0000-00-00', 6, 69, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(130, 'KALAMBAY', '', '', '', '', '0000-00-00', 7, 69, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(131, 'NDAYA', '', '', '', '', '0000-00-00', 8, 69, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(132, 'KALETA', '', '', '', '', '0000-00-00', 8, 70, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(133, 'BADIBANGA', '', '', '', '', '0000-00-00', 7, 71, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(134, 'NTUMBA', '', '', '', '', '0000-00-00', 6, 71, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(135, 'KALUNGA', '', '', '', '', '0000-00-00', 7, 72, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(136, 'NTUMBA', '', '', '', '', '0000-00-00', 8, 74, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(137, 'KAMAMBA', '', '', '', '', '0000-00-00', 7, 76, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(138, 'KAMAMBA', '', '', '', '', '0000-00-00', 9, 76, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(139, 'KASWERA', '', '', '', '', '0000-00-00', 6, 77, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(140, 'KENMOE', '', '', '', '', '0000-00-00', 9, 78, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(141, 'MUAMBA', '', '', '', '', '0000-00-00', 8, 78, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(142, 'UMBA', '', '', '', '', '0000-00-00', 8, 79, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(143, 'NGALULA', '', '', '', '', '0000-00-00', 6, 79, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(144, 'BULELA', '', '', '', '', '0000-00-00', 5, 79, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(145, 'LUKANDA', '', '', '', '', '0000-00-00', 7, 80, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(146, 'NYEMBWA', '', '', '', '', '0000-00-00', 8, 82, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(147, 'MITONGO', '', '', '', '', '0000-00-00', 6, 82, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(148, 'KAPENGA ', '', '', '', '', '0000-00-00', 9, 83, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(149, 'KAPITA ', '', '', '', '', '0000-00-00', 6, 85, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(150, 'KAPINGA ', '', '', '', '', '0000-00-00', 5, 86, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(151, 'PALABA', '', '', '', '', '0000-00-00', 4, 86, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(152, 'MISHIKA', '', '', '', '', '0000-00-00', 6, 87, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(153, 'TSHIMBALANGA ', '', '', '', '', '0000-00-00', 4, 89, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(154, 'TSHIALU', '', '', '', '', '0000-00-00', 4, 90, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(155, 'YASUMINI', '', '', '', '', '0000-00-00', 6, 91, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(156, 'KATEMBO ', '', '', '', '', '0000-00-00', 9, 92, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(157, 'KATEMBO ', '', '', '', '', '0000-00-00', 7, 92, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(158, 'NZINGA', '', '', '', '', '0000-00-00', 7, 94, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(159, 'ALIMASI', '', '', '', '', '0000-00-00', 5, 94, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(160, 'META', '', '', '', '', '0000-00-00', 8, 95, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(161, 'TENDAY', '', '', '', '', '0000-00-00', 7, 95, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(162, 'TSHIKUAKA', '', '', '', '', '0000-00-00', 4, 95, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(163, 'KAYEMBE ', '', '', '', '', '0000-00-00', 4, 96, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(164, 'KAYEMBE ', 'KABUE', '', '', '', '0000-00-00', 7, 97, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(165, 'NAWEGE', '', '', '', '', '0000-00-00', 6, 98, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(166, 'MUKEBA', '', '', '', '', '0000-00-00', 4, 99, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(167, 'TSHIKALA', '', '', '', '', '0000-00-00', 7, 100, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(168, 'TSHIANYI', '', '', '', '', '0000-00-00', 4, 100, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(169, 'MANSINGA', '', '', '', '', '0000-00-00', 5, 101, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(170, 'FUNDANGA', '', '', '', '', '0000-00-00', 7, 102, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(171, 'KIKWATA ', '', '', '', '', '0000-00-00', 8, 103, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(172, 'KIKWATA ', '', '', '', '', '0000-00-00', 6, 103, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(173, 'NEHEMA', '', '', '', '', '0000-00-00', 9, 104, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(174, 'KIPALAMOTO ', '', '', '', '', '0000-00-00', 5, 106, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(175, 'KITOKO ', '', '', '', '', '0000-00-00', 4, 108, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(176, 'PUMBA', '', '', '', '', '0000-00-00', 5, 109, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(177, 'DIBUE', '', '', '', '', '0000-00-00', 6, 110, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(178, 'ONYUMBE', '', '', '', '', '0000-00-00', 4, 111, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(179, 'KONGA ', '', '', '', '', '0000-00-00', 5, 112, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(180, 'OLEKIYA', '', '', '', '', '0000-00-00', 6, 113, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(181, 'KPANYO', '', '', '', '', '0000-00-00', 7, 114, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(182, 'LEMA ', '', '', '', '', '0000-00-00', 8, 116, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(183, 'LILOKU ', '', '', '', '', '0000-00-00', 4, 117, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(184, 'LOKOTO ', '', '', '', '', '0000-00-00', 8, 118, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(185, 'ASHA', '', '', '', '', '0000-00-00', 8, 245, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(186, 'LOMBE ', '', '', '', '', '0000-00-00', 5, 245, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(187, 'LONDOLA ', '', '', '', '', '0000-00-00', 8, 120, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(188, 'LONDOLA ', '', '', '', '', '0000-00-00', 5, 120, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(189, 'LONSALA ', '', '', '', '', '0000-00-00', 8, 121, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(190, 'LUBAKI ', '', '', '', '', '0000-00-00', 9, 122, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(191, 'LUBAKI ', '', '', '', '', '0000-00-00', 6, 122, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(192, 'KUNUNGA', '', '', '', '', '0000-00-00', 4, 123, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(193, 'LUKUNYI ', '', '', '', '', '0000-00-00', 6, 124, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(194, 'LUTUMBA ', '', '', '', '', '0000-00-00', 4, 126, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(195, 'LUYOYO ', '', '', '', '', '0000-00-00', 9, 128, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(196, 'LUYOYO ', '', '', '', '', '0000-00-00', 8, 128, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(197, 'KAYOWA', '', '', '', '', '0000-00-00', 5, 129, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(198, 'MAFUTA ', '', '', '', '', '0000-00-00', 9, 131, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(199, 'MAKA ', '', '', '', '', '0000-00-00', 7, 132, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(200, 'MALONGA ', '', '', '', '', '0000-00-00', 7, 134, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(201, 'MAKPOLO ', '', '', '', '', '0000-00-00', 9, 136, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(202, 'MAMBO', '', '', '', '', '0000-00-00', 7, 137, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(203, 'LIDEKI', '', '', '', '', '0000-00-00', 8, 140, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(204, 'MASHALA', '', '', '', '', '0000-00-00', 9, 142, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(205, 'MATANDA', 'MPUTU', '', '', '', '0000-00-00', 6, 144, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(206, 'MATALA', '', '', '', '', '0000-00-00', 6, 145, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(207, 'MAWANGA', '', '', '', '', '0000-00-00', 4, 147, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(208, 'MAWELU', '', '', '', '', '0000-00-00', 7, 246, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(209, 'MAWELU ', '', '', '', '', '0000-00-00', 5, 246, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(210, 'MAYO', '', '', '', '', '0000-00-00', 5, 149, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(211, 'NDELELA', '', '', '', '', '0000-00-00', 7, 150, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(212, 'SONGE', '', '', '', '', '0000-00-00', 6, 151, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(213, 'MBEMBA ', '', '', '', '', '0000-00-00', 5, 152, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(214, 'MBUA', 'LITETE', 'ROSE', 'F', '', '0000-00-00', 8, 153, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(215, 'MAKAYA', '', '', '', '', '0000-00-00', 6, 154, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(216, 'MBUYAMBA ', '', '', '', '', '0000-00-00', 6, 155, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(217, 'MBUYAMBA ', '', '', '', '', '0000-00-00', 4, 155, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(218, 'NSIMBA', '', '', '', '', '0000-00-00', 9, 156, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(219, 'MILALA', '', '', '', '', '0000-00-00', 5, 157, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(220, 'KALELE', '', '', '', '', '0000-00-00', 4, 158, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(221, 'NDAYA ', '', '', '', '', '0000-00-00', 4, 159, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(222, 'TSHITENGE', '', '', '', '', '0000-00-00', 7, 160, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(223, 'MOKE', '', '', '', '', '0000-00-00', 6, 160, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(224, 'AMBA', '', '', '', '', '0000-00-00', 8, 161, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(225, 'MITONGO', '', '', '', '', '0000-00-00', 6, 82, '2025-03-07', '2025-03-07', '2024-2025', 'Administrateur(trice)', 0.00),
(226, 'LIBELA', '', '', '', '', '0000-00-00', 8, 162, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(227, 'KILO', '', '', '', '', '0000-00-00', 5, 163, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(228, 'MONGAY', '', '', '', '', '0000-00-00', 7, 164, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(229, 'MONGAY ', '', '', '', '', '0000-00-00', 4, 164, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(230, 'MOPAYA ', '', '', '', '', '0000-00-00', 8, 165, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(231, 'MOPAYA ', '', '', '', '', '0000-00-00', 7, 165, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(232, 'MOPAYA ', '', '', '', '', '0000-00-00', 4, 165, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(233, 'LUKOJI', '', '', '', '', '0000-00-00', 4, 167, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(234, 'MBWAYA', '', '', '', '', '0000-00-00', 5, 169, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(235, 'MPINDA', '', '', '', '', '0000-00-00', 9, 170, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(236, 'NGOMBE', '', '', '', '', '0000-00-00', 8, 170, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(237, 'TSHILANDA ', '', '', '', '', '0000-00-00', 5, 170, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(238, 'MUAMBA ', '', '', '', '', '0000-00-00', 5, 171, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(239, 'SAFI', '', '', '', '', '0000-00-00', 9, 173, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(240, 'KAKULE', '', '', '', '', '0000-00-00', 6, 174, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(241, 'NGALULA ', '', '', '', '', '0000-00-00', 7, 175, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(242, 'NGELEKA', '', '', '', '', '0000-00-00', 5, 176, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(243, 'MUKENA ', '', '', '', '', '0000-00-00', 6, 178, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(244, 'MUKENA ', '', '', '', '', '0000-00-00', 4, 178, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(245, 'NANA ', '', '', '', '', '0000-00-00', 5, 179, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(246, 'MASHAKA ', '', '', '', '', '0000-00-00', 6, 180, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(247, 'KAPENDA ', '', '', '', '', '0000-00-00', 4, 180, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(248, 'MULUKIDI ', '', '', '', '', '0000-00-00', 6, 183, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(249, 'MABRUCK', '', '', '', '', '0000-00-00', 5, 184, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(250, 'MUSUNGAYI ', '', '', '', '', '0000-00-00', 9, 186, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(251, 'MUSUNGAYI ', '', '', '', '', '0000-00-00', 6, 186, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(252, 'MUTEBA ', '', '', '', '', '0000-00-00', 7, 187, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(253, 'KAPINGA ', '', '', '', '', '0000-00-00', 8, 188, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(254, 'NSAMBA', '', '', '', '', '0000-00-00', 8, 190, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(255, 'TSHIKUAKA ', '', '', '', '', '0000-00-00', 6, 190, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(256, 'BUKUMBA', '', '', '', '', '0000-00-00', 5, 190, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(257, 'BUKASA ', '', '', '', '', '0000-00-00', 5, 191, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(258, 'MWANA KASONGO ', '', '', '', '', '0000-00-00', 7, 192, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(259, 'KALONDA ', '', '', '', '', '0000-00-00', 5, 192, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(260, 'LUMBALA ', '', '', '', '', '0000-00-00', 8, 192, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(261, 'NAMA ', '', '', '', '', '0000-00-00', 9, 195, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(263, 'NAMA ', '', '', '', '', '0000-00-00', 7, 195, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(264, 'NDAMBA ', '', '', '', '', '0000-00-00', 5, 196, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(265, 'MULANGA', '', '', '', '', '0000-00-00', 8, 198, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(266, 'BITAMARA', '', '', '', '', '0000-00-00', 4, 201, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(267, 'NGUYA ', '', '', '', '', '0000-00-00', 8, 202, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(268, 'MOMBEMBE ', '', '', '', '', '0000-00-00', 6, 203, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(269, 'NTAMBWE ', '', '', '', '', '0000-00-00', 5, 204, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(270, 'NGOMA ', '', '', '', '', '0000-00-00', 9, 205, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(271, 'NSIMBA ', '', '', '', '', '0000-00-00', 6, 205, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(272, 'NDIDI', '', '', '', '', '0000-00-00', 5, 205, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(273, 'OKITO ', '', '', '', '', '0000-00-00', 4, 206, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(274, 'OKOTO ', '', '', '', '', '0000-00-00', 4, 207, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(275, 'OMALOHEMBE ', '', '', '', '', '0000-00-00', 7, 208, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(276, 'OTANI ', '', '', '', '', '0000-00-00', 6, 210, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(277, 'MPOYI', '', '', '', '', '0000-00-00', 4, 211, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(278, 'LOHADJE', '', '', '', '', '0000-00-00', 8, 212, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(279, 'SACKO ', '', '', '', '', '0000-00-00', 7, 213, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(280, 'SACKO ', '', '', '', '', '0000-00-00', 5, 213, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(281, 'LINDEZA', '', '', '', '', '0000-00-00', 4, 214, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(282, 'SALUMU ', '', '', '', '', '0000-00-00', 7, 215, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(283, 'SAMINE ', '', '', '', '', '0000-00-00', 9, 216, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(284, 'SAMINE ', '', '', '', '', '0000-00-00', 7, 216, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(285, 'TOKONDJO', '', '', '', '', '0000-00-00', 8, 217, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(286, 'MAMADOU', '', '', '', '', '0000-00-00', 9, 219, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(287, 'SIKU ', '', '', '', '', '0000-00-00', 4, 219, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(288, 'SONDI ', '', '', '', '', '0000-00-00', 5, 221, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(289, 'SHUNGU ', '', '', '', '', '0000-00-00', 7, 222, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(290, 'ELONGA', '', '', '', '', '0000-00-00', 9, 223, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(291, 'TANGULU ', '', '', '', '', '0000-00-00', 5, 224, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(292, 'NKEMBO', '', '', '', '', '0000-00-00', 6, 226, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(293, 'TSHIBANDA ', '', '', '', '', '0000-00-00', 8, 229, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(294, 'TSHIBUABUA ', '', '', '', '', '0000-00-00', 5, 230, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(295, 'MUKENDI ', '', '', '', '', '0000-00-00', 8, 231, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(296, 'MUAMBA ', '', '', '', '', '0000-00-00', 6, 231, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(297, 'SWABANZA', '', '', '', '', '0000-00-00', 7, 232, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(298, 'TSHILIUMBA ', '', '', '', '', '0000-00-00', 8, 233, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(299, 'NTUMBA', '', '', '', '', '0000-00-00', 9, 233, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(300, 'MBUYI ', '', '', '', '', '0000-00-00', 7, 233, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(301, 'TSHILUMBAY ', '', '', '', '', '0000-00-00', 8, 234, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(302, 'MIANDA', '', '', '', '', '0000-00-00', 8, 235, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(303, 'MBUYI ', '', '', '', '', '0000-00-00', 6, 235, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(304, 'TSHISHIKU', '', '', '', '', '0000-00-00', 4, 237, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(305, 'BINTU', '', '', '', '', '0000-00-00', 7, 237, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(306, 'KABENGELE', '', '', '', '', '0000-00-00', 5, 238, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(307, 'MASENGU', '', '', '', '', '0000-00-00', 5, 238, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(308, 'MBALA', '', '', '', '', '0000-00-00', 6, 239, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(309, 'ANKOKWA', '', '', '', '', '0000-00-00', 9, 240, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(310, 'MUHANANO', '', '', '', '', '0000-00-00', 7, 240, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(311, 'TUKUKA ', '', '', '', '', '0000-00-00', 8, 241, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(312, 'TUKUKA ', '', '', '', '', '0000-00-00', 6, 241, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(313, 'TAKOY', '', '', '', '', '0000-00-00', 4, 243, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(314, 'MBUYA', '', '', '', '', '0000-00-00', 4, 244, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(315, 'ADJANGA ', 'BAYINZA', '', '', '', '0000-00-00', 11, 2, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(316, 'NZEBA', '', '', '', '', '0000-00-00', 11, 11, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(317, 'BASILUA ', '', '', '', '', '0000-00-00', 11, 21, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(318, 'BILUNZA ', '', '', '', '', '0000-00-00', 11, 27, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(319, 'BOKOPOLO ', '', '', '', '', '0000-00-00', 10, 29, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(320, 'NDJONGO', '', '', '', '', '0000-00-00', 10, 35, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(321, 'DIKISI ', '', '', '', '', '0000-00-00', 10, 37, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(322, 'EKABA ', 'EFOFO ', '', '', '', '0000-00-00', 10, 40, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(323, 'FAMA ', '', '', '', '', '0000-00-00', 11, 45, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(324, 'FUKINSIA ', '', '', '', '', '0000-00-00', 10, 46, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(325, 'BANZE', '', '', '', '', '0000-00-00', 11, 56, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(326, 'WABO', '', '', '', '', '0000-00-00', 11, 63, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(327, 'LUABEYA', '', '', '', '', '0000-00-00', 11, 69, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(328, 'LUSAMBA', '', '', '', '', '0000-00-00', 10, 74, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(329, 'KANIKI ', '', '', '', '', '0000-00-00', 11, 81, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(330, 'KANYINDA ', '', '', '', '', '0000-00-00', 11, 84, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(331, 'KASOKI ', '', '', '', '', '0000-00-00', 11, 88, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(332, 'KAJINGU', '', '', '', '', '0000-00-00', 10, 93, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(333, 'KIKWATA ', '', '', '', '', '0000-00-00', 11, 103, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(334, 'KONFIE ', '', '', '', '', '0000-00-00', 10, 111, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(335, 'KAYENGO', '', '', '', '', '0000-00-00', 10, 114, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(336, 'MUJANYI', '', '', '', '', '0000-00-00', 10, 115, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(337, 'MUNDONGA', '', '', '', '', '0000-00-00', 10, 117, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(338, 'LOKOTO ', '', '', '', '', '0000-00-00', 11, 118, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(339, 'MAKUKULA', '', '', '', '', '0000-00-00', 11, 133, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(340, 'KAMANGO', '', '', '', '', '0000-00-00', 10, 140, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(341, 'MASAMBA ', '', '', '', '', '0000-00-00', 11, 141, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(342, 'NLEMVO', '', '', '', '', '0000-00-00', 11, 143, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(343, 'NSIKU', '', '', '', '', '0000-00-00', 10, 143, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(344, 'KHONDE', '', '', '', '', '0000-00-00', 11, 143, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(345, 'KIANSUKA', '', '', '', '', '0000-00-00', 11, 154, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(346, 'MOMBEMBE ', '', '', '', '', '0000-00-00', 10, 162, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(347, 'MOZA ', '', '', '', '', '0000-00-00', 10, 166, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(348, 'KASEREKA', '', '', '', '', '0000-00-00', 10, 174, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(349, 'MUAMBA ', '', '', '', '', '0000-00-00', 10, 175, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(350, 'LANGO', '', '', '', '', '0000-00-00', 11, 185, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(351, 'MUSUNGAYI ', '', '', '', '', '0000-00-00', 11, 186, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(352, 'MWANA KASONGO ', '', '', '', '', '0000-00-00', 11, 192, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(353, 'MWANA KASONGO ', '', '', '', '', '0000-00-00', 10, 192, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(354, 'MVUTU ', '', '', '', '', '0000-00-00', 11, 193, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(355, 'RAYAN', '', '', '', '', '0000-00-00', 11, 213, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(356, 'SACKO ', '', '', '', '', '0000-00-00', 11, 213, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(357, 'TANZEY ', '', '', '', '', '0000-00-00', 11, 225, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(358, 'META', '', '', 'F', '', '0000-00-00', 11, 228, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(359, 'BOYAYI', '', '', '', '', '0000-00-00', 10, 231, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(360, 'MUSAMPA', '', '', '', '', '0000-00-00', 11, 236, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(361, 'URWODI ', '', '', '', '', '0000-00-00', 10, 242, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(362, 'BASILUA ', '', '', '', '', '0000-00-00', 12, 21, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(363, 'LUSAKUMUNU ', '', '', '', '', '0000-00-00', 12, 125, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(364, 'MANGALA ', '', '', '', '', '0000-00-00', 12, 138, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(365, 'MOMBEMBE ', '', '', 'M', '', '0000-00-00', 13, 162, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(366, 'OMARI ', '', '', 'F', '', '0000-00-00', 13, 209, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(367, 'EYAKANO', '', '', '', '', '0000-00-00', 12, 220, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(368, 'BAPANGIDI', '', '', '', '', '0000-00-00', 12, 229, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(369, 'MUBIAYI', '', '', '', '', '0000-00-00', 11, 59, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(370, 'KAMA ', '', '', '', '', '0000-00-00', 10, 75, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(371, 'KAMBALE ', '', '', '', '', '0000-00-00', 11, 174, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(372, 'MBUYI ', '', '', '', '', '0000-00-00', 10, 200, '2025-03-08', '2025-03-08', '2024-2025', 'Administrateur(trice)', 0.00),
(373, 'KABAMBI', '', '', '', '', '0000-00-00', 3, 57, '2025-03-16', '2025-03-16', '2024-2025', 'Administrateur(trice)', 0.00),
(374, 'KABAMBA', '', '', '', '', '0000-00-00', 9, 76, '2025-03-16', '2025-03-16', '2024-2025', 'Administrateur(trice)', 0.00),
(375, 'BOKANGU', '', 'MARIA', 'F', '', '0000-00-00', 8, 153, '2025-03-16', '2025-03-16', '2024-2025', 'Administrateur(trice)', 0.00),
(376, 'KANYEBA', '', '', '', '', '0000-00-00', 7, 227, '2025-03-16', '2025-03-16', '2024-2025', 'Administrateur(trice)', 0.00),
(377, 'NZOLA', 'MBOKOSO', 'BELVI', 'F', '', '0000-00-00', 13, 247, '2025-03-16', '2025-03-16', '2024-2025', 'Administrateur(trice)', 0.00),
(378, 'Kayembe', 'Lukusa', 'Eav-god', 'M', 'kinshasa', '2019-03-23', 4, 96, '2025-03-22', '2025-03-22', '2024-2025', 'Administrateur(trice)', 0.00),
(379, 'MABIKA ', 'BELENGO', 'Rock ', '', 'kinshasa', '2014-04-03', 7, 248, '2025-03-24', '2025-03-24', '2024-2025', 'Administrateur(trice)', 0.00),
(380, 'MPUTU', 'MPUTU', 'JOSEPH', 'M', 'TSHIKAPA', '2013-04-07', 10, 250, '2025-06-02', '2025-06-02', '2024-2025', 'Administrateur(trice)', 0.00),
(381, 'KABONGO', 'MPUTU', 'BENJAMIN', 'M', 'TSHIKAPA', '2014-08-18', 9, 250, '2025-06-02', '2025-06-02', '2024-2025', 'Administrateur(trice)', 0.00),
(382, 'BAKOTO', 'KOLOKOLO', 'DADOU JUNIOR', 'M', 'KINSHASA', '2014-05-10', 10, 251, '2025-07-08', '2025-07-08', '2025-2026', 'Administrateur(trice)', 450.00),
(383, 'PUKUTA', 'KUMESO', '', '', '', '0000-00-00', 9, 252, '2025-07-10', '2025-07-10', '2024-2025', 'Administrateur(trice)', 0.00),
(384, 'BAKAJI', 'MUTUMBAYI', '', '', '', '0000-00-00', 4, 253, '2025-07-10', '2025-07-10', '2024-2025', 'Administrateur(trice)', 0.00),
(385, 'MWANZA', '', '', '', '', '0000-00-00', 3, 254, '2025-07-10', '2025-07-10', '2024-2025', 'Administrateur(trice)', 0.00),
(386, 'NGALAMULUME', '', '', '', '', '0000-00-00', 8, 255, '2025-07-10', '2025-07-10', '2024-2025', 'Administrateur(trice)', 0.00),
(387, 'WALESA', '', '', '', '', '0000-00-00', 6, 255, '2025-07-10', '2025-07-10', '2024-2025', 'Administrateur(trice)', 0.00),
(388, 'NGAWARA', 'NGABUKANA', 'TERRY', 'M', 'KINSHASA', '2022-12-15', 1, 197, '2025-07-11', '2025-07-11', '2025-2026', 'Administrateur(trice)', 400.00),
(390, 'NTUMBA', 'MATULA', '', '', '', '0000-00-00', 3, 256, '2025-07-17', '2025-07-17', '2025-2026', 'Administrateur(trice)', 400.00);

-- --------------------------------------------------------

--
-- Structure de la table `fonction`
--

CREATE TABLE `fonction` (
  `id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `dateCreaty` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createdby` text NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `grade`
--

CREATE TABLE `grade` (
  `id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `dateCreaty` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createdby` text NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `menage`
--

CREATE TABLE `menage` (
  `id` int(11) NOT NULL,
  `noms` varchar(50) NOT NULL,
  `telephone` varchar(15) NOT NULL,
  `numero` varchar(5) NOT NULL,
  `avenue` varchar(20) NOT NULL,
  `quartier` varchar(20) NOT NULL,
  `commune` varchar(20) NOT NULL,
  `dateCreated` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createdby` varchar(25) NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL,
  `montantAPayer` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `menage`
--

INSERT INTO `menage` (`id`, `noms`, `telephone`, `numero`, `avenue`, `quartier`, `commune`, `dateCreated`, `dateUpdate`, `createdby`, `anneeScolaire`, `montantAPayer`) VALUES
(2, 'ADJANGA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 1250.00),
(3, 'AHUKA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(4, 'ANGBAKODOLO', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(5, 'ASSAF', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(6, 'ATAMA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(7, 'ATANYOI', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(8, 'ATUNDEZI', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(9, 'BABADI', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 800.00),
(10, 'BADIPABUADO', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(11, 'BADIBANGA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 1250.00),
(12, 'BAKA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(13, 'BAKOLA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 800.00),
(14, 'BAKONGO', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(15, 'BALUME', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(16, 'BAMBA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(17, 'BAMBALA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(18, 'BANZI', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(19, 'BASELE', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(20, 'BASANGA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(21, 'BASILUA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 900.00),
(22, 'BASSALWA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 800.00),
(23, 'BENDERA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 800.00),
(24, 'BETWELE', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(25, 'BEYA SAMUEL ', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 800.00),
(26, 'BEYA TSHILUMBA', '', '', '', '', '', '2025-03-06', '2025-03-06', 'Administrateur(trice)', '2024-2025', 400.00),
(27, 'BILUNZA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(28, 'BOBUTAKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(29, 'BOKOPOLO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(30, 'BASEKONDA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(31, 'BOSEKOTA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(32, 'BOTUNGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(33, 'BUKASA GABY', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(34, 'CISHIMBI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(35, 'DAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1250.00),
(36, 'DIMA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(37, 'DIKISI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(38, 'DJUMA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(39, 'EKABA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(40, 'EKABA NDOLO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(41, 'EKOFO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(42, 'EKOFO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 0.00),
(43, 'ELUMBU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(44, 'ESIMBI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(45, 'FAMA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(46, 'FUKINSIA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(47, 'GENE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(48, 'GULUNGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(49, 'HONDA ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(50, 'IBULA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(51, 'IDI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(52, 'IKIYO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(53, 'IKOLA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(54, 'ILUMBE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(55, 'ILUNGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(56, 'ILUNGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1250.00),
(57, 'KABAMBI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(58, 'KABANA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(59, 'KABEYA BAJIKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1250.00),
(60, 'KABEYA WETU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(61, 'KABONGO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(62, 'KABONGO SAMUEL ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(63, 'KABULU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(64, 'KABULO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(65, 'KABUYA KAYEMBE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(66, 'KABUYA ISRAËL ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(67, 'KALALA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(68, 'KALALA DEV', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(69, 'KALAMBAY', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1747.00),
(70, 'KALETA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 600.00),
(71, 'KALONGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(72, 'KALUNGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(73, 'KAKULE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(74, 'KALUIJI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(75, 'KAMA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(76, 'KAMAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(77, 'KAMBALE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(78, 'KAMUNVU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(79, 'KANDE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1600.00),
(80, 'KANKONDE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(81, 'KANIKI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(82, 'KANYANYA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(83, 'KAPENGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(84, 'KANYINDA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(85, 'KAPITA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(86, 'KASALA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(87, 'KASEMBWE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(88, 'KASOKI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(89, 'KASONGO TSHIMBALANGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(90, 'KASONGO KABAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(91, 'KATEBUA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(92, 'KATEMBO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(93, 'KATEMBWE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(94, 'KAYA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(95, 'KAYEMBE AUGUY', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(96, 'KAYEMBE KABUYA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(97, 'KAYEMBE BANZA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(98, 'KAZADI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(99, 'KAZADI MWENDELA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(100, 'KAZADI ZADIO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(101, 'KIALA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(102, 'KIHINDU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(103, 'KIKWATA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1250.00),
(104, 'KINKANI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(105, 'KINZANZA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(106, 'KIPALAMOTO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(107, 'KISIATA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(108, 'KITOKO ROBERT ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(109, 'KITOKO ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(110, 'KOMANA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(111, 'KONFIE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(112, 'KONGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(113, 'KONGO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(114, 'KPANYO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(115, 'KUPA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(116, 'LEMA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(117, 'LILOKU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(118, 'LOKOTO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(119, 'LOMBOMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(120, 'LONDOLA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(121, 'LONSALA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(122, 'LUBAKI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(123, 'LUHAKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(124, 'LUKUNYI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(125, 'LUSAKUMUNU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(126, 'LUTUMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(127, 'LUTUTA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(128, 'LUYOYO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(129, 'MABANGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(130, 'MAFUTA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(131, 'MAFUTA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(132, 'MAKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(133, 'MANKUKULA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(134, 'MALONGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(135, 'MALONGA NKAMBANI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(136, 'MAKPOLO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(137, 'MAMBO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(138, 'MANGALA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(139, 'MANGILA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(140, 'MANOKO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(141, 'MASAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(142, 'MASHALA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(143, 'MASWAMA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1350.00),
(144, 'MATANDA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(145, 'MATANDI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(146, 'MAVAMBOU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(147, 'MAWANGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(148, 'MAYEKO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(149, 'MAYO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(150, 'MBANGU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(151, 'MBAYO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(152, 'MBEMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(153, 'MBUA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(154, 'MBUNGU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(155, 'MBUYAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(156, 'MBEZI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(157, 'MBUYI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(158, 'MFUTA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(159, 'MILAMBU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(160, 'MOKE ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(161, 'MOLA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(162, 'MOMBEMBE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1300.00),
(163, 'MONKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(164, 'MONGAY', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(165, 'MOPAYA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(166, 'MOZA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(167, 'MPIANA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(168, 'MPIANA MICHEL', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(169, 'MPUTU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(170, 'MUAMBA JC', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1600.00),
(171, 'MUAMBA SERGE ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(172, 'MUANYO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(173, 'MUDIMBI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(174, 'MUHINDO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1300.00),
(175, 'MUKANDILA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(176, 'MUKEBA KABONGO ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(177, 'MUKEBA ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(178, 'MUKENA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(179, 'MUKUBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(180, 'MUKINA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(181, 'MULAND', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(182, 'MULOBO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(183, 'MULUKIDI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(184, 'MULUMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(185, 'MUMBERE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(186, 'MUSUNGAYI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1250.00),
(187, 'MUTEBA LIEVIN', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(188, 'MUTEBA ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(189, 'MUTOMBO KA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(190, 'MUTOMBO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(191, 'MUAMBA DAVID ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(192, 'MWANA KASONGO ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 2100.00),
(193, 'MVUTU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(194, 'MWAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(195, 'NAMA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(196, 'NDAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(197, 'NGAWARA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2025-2026', 800.00),
(198, 'NGEJA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(199, 'NGOYI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 0.00),
(200, 'NGOYI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(201, 'NGUMBI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(202, 'NGUYA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(203, 'NKIDIAKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(204, 'NTAMBWE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(205, 'NZUZI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(206, 'OKITO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(207, 'OKOTO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(208, 'OMALOHEMBE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(209, 'OMARI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(210, 'OTANI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(211, 'OTSHINGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(212, 'OWANDA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(213, 'SACKO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1700.00),
(214, 'SALAZAKU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(215, 'SALUMU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(216, 'SAMINE', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(217, 'SELEMANI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(218, 'SELEMANI ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(219, 'SIKU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(220, 'SHOKO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(221, 'SONDI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(222, 'SHUNGU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(223, 'TAMBI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(224, 'TANGULU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(225, 'TANZEY', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(226, 'TSHIMAKANDA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(227, 'TSHIAMALA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(228, 'TSHIBAMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(229, 'TSHIBANDA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(230, 'TSHIBUABUA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(231, 'TSHIKANGU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1250.00),
(232, 'TSHIKO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(233, 'TSHILUMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(234, 'TSHILUMBAY', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(235, 'TSHIMANGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(236, 'TSHIMWANGA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 450.00),
(237, 'TSHISHIKU', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(238, 'TSHISWAKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(239, 'TSHIUNZA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(240, 'TSHOKOLA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(241, 'TUKUKA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(242, 'URWODI', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 850.00),
(243, 'WELO', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 800.00),
(244, 'YUMBA', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 400.00),
(245, 'LOMBE ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(246, 'MAWELU ', '', '', '', '', '', '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025', 1200.00),
(247, 'NZOLA', '', '', '', '', '', '2025-03-16', '2025-03-16', 'Administrateur(trice)', '2024-2025', 450.00),
(248, 'MABIKA', '', '', '', '', '', '2025-03-24', '2025-03-24', 'Administrateur(trice)', '2024-2025', 400.00),
(250, 'MPUTU ALBIN', '0998842881', '05', 'MBELLE', 'SALONGO', 'LEMBA', '2025-06-02', '2025-06-02', 'Administrateur(trice)', '2024-2025', 850.00),
(251, 'BAKOTO BATUNGOLA DADOU ', '0903489443', '14', 'République', 'FUNA', 'LIMETE', '2025-07-08', '2025-07-08', 'Administrateur(trice)', '2025-2026', 450.00),
(252, 'KUMESO', '', '', '', '', '', '2025-07-10', '2025-07-10', 'Administrateur(trice)', '2024-2025', 400.00),
(253, 'MUTUMBAYI', '', '', '', '', '', '2025-07-10', '2025-07-10', 'Administrateur(trice)', '2024-2025', 400.00),
(254, 'MWANZA', '', '', '', '', '', '2025-07-10', '2025-07-10', 'Administrateur(trice)', '2024-2025', 400.00),
(255, 'NGALAMULUME', '', '', '', '', '', '2025-07-10', '2025-07-10', 'Administrateur(trice)', '2024-2025', 800.00),
(256, 'NTIMA TINA', '0895421780', '20', 'Kasanzi', 'Ngafani', 'Selembao', '2025-07-17', '2025-07-17', 'Administrateur(trice)', '2025-2026', 800.00);

-- --------------------------------------------------------

--
-- Structure de la table `option`
--

CREATE TABLE `option` (
  `id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `dateCreated` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `annee_scolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `option`
--

INSERT INTO `option` (`id`, `description`, `dateCreated`, `dateUpdate`, `annee_scolaire`) VALUES
(1, 'Commercial et Gestion', '2025-03-16', '2025-03-16', '2024-2025');

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

CREATE TABLE `paiement` (
  `id` int(11) NOT NULL,
  `menage` int(11) NOT NULL,
  `montantAPayer` decimal(10,2) NOT NULL,
  `montantPayer` decimal(10,2) NOT NULL,
  `resteAPayer` decimal(10,2) NOT NULL,
  `observation` text NOT NULL,
  `dateCreated` date NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`id`, `menage`, `montantAPayer`, `montantPayer`, `resteAPayer`, `observation`, `dateCreated`, `anneeScolaire`) VALUES
(1, 116, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(2, 117, 850.00, 645.00, 205.00, 'paiement encours', '2025-03-22', '2024-2025'),
(3, 118, 850.00, 675.00, 175.00, 'paiement encours', '2025-03-22', '2024-2025'),
(4, 245, 1200.00, 938.00, 262.00, 'paiement encours', '2025-03-22', '2024-2025'),
(5, 119, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(6, 120, 800.00, 700.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(7, 121, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(8, 122, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(9, 123, 400.00, 310.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(10, 124, 400.00, 170.00, 230.00, 'paiement encours', '2025-03-22', '2024-2025'),
(11, 125, 450.00, 210.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(12, 126, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(13, 127, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(14, 128, 800.00, 577.00, 223.00, 'paiement encours', '2025-03-22', '2024-2025'),
(15, 129, 800.00, 412.00, 388.00, 'paiement encours', '2025-03-22', '2024-2025'),
(16, 130, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(17, 131, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(18, 132, 400.00, 400.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(19, 133, 450.00, 350.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(20, 134, 800.00, 598.00, 202.00, 'paiement encours', '2025-03-22', '2024-2025'),
(21, 135, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(22, 136, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(23, 137, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(24, 138, 450.00, 355.00, 95.00, 'paiement encours', '2025-03-22', '2024-2025'),
(25, 139, 400.00, 314.48, 85.52, 'paiement encours', '2025-03-22', '2024-2025'),
(26, 140, 850.00, 680.00, 170.00, 'paiement encours', '2025-03-22', '2024-2025'),
(27, 141, 450.00, 450.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(28, 142, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(29, 143, 1350.00, 1080.00, 270.00, 'paiement encours', '2025-03-22', '2024-2025'),
(30, 144, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(31, 145, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(32, 146, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(33, 147, 400.00, 315.00, 85.00, 'paiement encours', '2025-03-22', '2024-2025'),
(34, 246, 1200.00, 960.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(35, 148, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(36, 149, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(37, 152, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(38, 153, 800.00, 700.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(39, 154, 850.00, 575.00, 275.00, 'paiement encours', '2025-03-22', '2024-2025'),
(40, 155, 800.00, 680.00, 120.00, 'paiement encours', '2025-03-22', '2024-2025'),
(41, 156, 400.00, 250.00, 150.00, 'paiement encours', '2025-03-22', '2024-2025'),
(42, 157, 400.00, 350.00, 50.00, 'paiement encours', '2025-03-22', '2024-2025'),
(43, 158, 400.00, 330.00, 70.00, 'paiement encours', '2025-03-22', '2024-2025'),
(44, 159, 400.00, 400.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(45, 160, 800.00, 680.00, 120.00, 'paiement encours', '2025-03-22', '2024-2025'),
(46, 161, 400.00, 350.00, 50.00, 'paiement encours', '2025-03-22', '2024-2025'),
(47, 162, 1300.00, 890.00, 410.00, 'paiement encours', '2025-03-22', '2024-2025'),
(48, 163, 400.00, 293.00, 107.00, 'paiement encours', '2025-03-22', '2024-2025'),
(49, 164, 800.00, 560.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(50, 165, 1200.00, 900.00, 300.00, 'paiement encours', '2025-03-22', '2024-2025'),
(51, 166, 450.00, 450.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(52, 167, 400.00, 260.00, 140.00, 'paiement encours', '2025-03-22', '2024-2025'),
(53, 168, 400.00, 310.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(54, 169, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(55, 170, 1600.00, 860.00, 740.00, 'paiement encours', '2025-03-22', '2024-2025'),
(56, 171, 800.00, 435.00, 365.00, 'paiement encours', '2025-03-22', '2024-2025'),
(57, 172, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(58, 173, 400.00, 280.00, 120.00, 'paiement encours', '2025-03-22', '2024-2025'),
(59, 174, 1300.00, 1040.00, 260.00, 'paiement encours', '2025-03-22', '2024-2025'),
(60, 175, 850.00, 350.00, 500.00, 'paiement encours', '2025-03-22', '2024-2025'),
(61, 176, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(62, 177, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(63, 178, 800.00, 290.00, 510.00, 'paiement encours', '2025-03-22', '2024-2025'),
(64, 179, 400.00, 200.00, 200.00, 'paiement encours', '2025-03-22', '2024-2025'),
(65, 180, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(66, 181, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(67, 182, 800.00, 800.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(68, 183, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(69, 184, 400.00, 318.00, 82.00, 'paiement encours', '2025-03-22', '2024-2025'),
(70, 185, 450.00, 270.00, 180.00, 'paiement encours', '2025-03-22', '2024-2025'),
(71, 186, 1250.00, 945.00, 305.00, 'paiement encours', '2025-03-22', '2024-2025'),
(72, 187, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(73, 188, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(74, 189, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(75, 190, 1200.00, 960.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(76, 191, 400.00, 280.00, 120.00, 'paiement encours', '2025-03-22', '2024-2025'),
(77, 192, 2100.00, 1495.00, 605.00, 'paiement encours', '2025-03-22', '2024-2025'),
(78, 193, 450.00, 320.00, 130.00, 'paiement encours', '2025-03-22', '2024-2025'),
(79, 194, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(80, 195, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(81, 196, 400.00, 263.00, 136.21, 'paiement encours', '2025-03-22', '2024-2025'),
(82, 197, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(83, 200, 450.00, 300.00, 150.00, 'paiement encours', '2025-03-22', '2024-2025'),
(84, 201, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(85, 202, 400.00, 170.00, 230.00, 'paiement encours', '2025-03-22', '2024-2025'),
(86, 203, 400.00, 330.00, 70.00, 'paiement encours', '2025-03-22', '2024-2025'),
(87, 204, 800.00, 800.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(88, 205, 1200.00, 950.00, 250.00, 'paiement encours', '2025-03-22', '2024-2025'),
(89, 206, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(90, 207, 400.00, 309.00, 91.00, 'paiement encours', '2025-03-22', '2024-2025'),
(91, 208, 400.00, 400.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(92, 209, 450.00, 365.00, 85.00, 'paiement encours', '2025-03-22', '2024-2025'),
(93, 210, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(94, 211, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(95, 212, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(96, 213, 1700.00, 1318.00, 382.00, 'paiement encours', '2025-03-22', '2024-2025'),
(97, 214, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(98, 215, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(99, 216, 800.00, 800.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(100, 217, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(101, 218, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(102, 219, 800.00, 578.00, 222.00, 'paiement encours', '2025-03-22', '2024-2025'),
(103, 220, 450.00, 300.00, 150.00, 'paiement encours', '2025-03-22', '2024-2025'),
(104, 221, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(105, 222, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(106, 223, 400.00, 310.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(107, 224, 400.00, 285.00, 115.00, 'paiement encours', '2025-03-22', '2024-2025'),
(108, 225, 450.00, 360.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(109, 226, 800.00, 619.00, 181.00, 'paiement encours', '2025-03-22', '2024-2025'),
(110, 227, 800.00, 680.00, 120.00, 'paiement encours', '2025-03-22', '2024-2025'),
(111, 228, 450.00, 320.00, 130.00, 'paiement encours', '2025-03-22', '2024-2025'),
(112, 229, 850.00, 700.00, 150.00, 'paiement encours', '2025-03-22', '2024-2025'),
(113, 230, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(114, 231, 1250.00, 925.00, 325.00, 'paiement encours', '2025-03-22', '2024-2025'),
(115, 232, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(116, 233, 1200.00, 960.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(117, 234, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(118, 235, 800.00, 600.00, 200.00, 'paiement encours', '2025-03-22', '2024-2025'),
(119, 236, 450.00, 360.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(120, 237, 800.00, 440.00, 360.00, 'paiement encours', '2025-03-22', '2024-2025'),
(121, 238, 800.00, 720.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(122, 239, 800.00, 630.00, 170.00, 'paiement encours', '2025-03-22', '2024-2025'),
(123, 240, 800.00, 800.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(124, 241, 800.00, 700.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(125, 242, 850.00, 600.00, 250.00, 'paiement encours', '2025-03-22', '2024-2025'),
(126, 243, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(127, 244, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(128, 56, 1250.00, 1125.00, 125.00, 'paiement encours', '2025-03-22', '2024-2025'),
(129, 55, 400.00, 190.00, 210.00, 'paiement encours', '2025-03-22', '2024-2025'),
(130, 54, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(131, 53, 800.00, 630.00, 170.00, 'paiement encours', '2025-03-22', '2024-2025'),
(132, 52, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(133, 51, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(134, 50, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(135, 49, 400.00, 290.00, 110.00, 'paiement encours', '2025-03-22', '2024-2025'),
(136, 48, 1200.00, 960.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(137, 47, 1200.00, 960.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(138, 46, 850.00, 620.00, 230.00, 'paiement encours', '2025-03-22', '2024-2025'),
(139, 45, 450.00, 360.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(140, 44, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(141, 43, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(142, 41, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(143, 40, 450.00, 345.00, 105.00, 'paiement encours', '2025-03-22', '2024-2025'),
(144, 39, 400.00, 160.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(145, 38, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(146, 37, 850.00, 625.00, 225.00, 'paiement encours', '2025-03-22', '2024-2025'),
(147, 36, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(148, 35, 1250.00, 950.00, 300.00, 'paiement encours', '2025-03-22', '2024-2025'),
(149, 34, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(150, 33, 800.00, 730.00, 70.00, 'paiement encours', '2025-03-22', '2024-2025'),
(151, 32, 400.00, 400.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(152, 31, 400.00, 160.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(153, 30, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(154, 29, 450.00, 360.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(155, 27, 450.00, 300.00, 150.00, 'paiement encours', '2025-03-22', '2024-2025'),
(156, 26, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(157, 25, 800.00, 600.00, 200.00, 'paiement encours', '2025-03-22', '2024-2025'),
(158, 24, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(159, 23, 800.00, 670.00, 130.00, 'paiement encours', '2025-03-22', '2024-2025'),
(160, 22, 800.00, 240.00, 560.00, 'paiement encours', '2025-03-22', '2024-2025'),
(161, 19, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(162, 20, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(163, 18, 400.00, 340.00, 60.00, 'paiement encours', '2025-03-22', '2024-2025'),
(164, 28, 400.00, 330.00, 70.00, 'paiement encours', '2025-03-22', '2024-2025'),
(165, 17, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(166, 16, 400.00, 310.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(167, 15, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(168, 14, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(169, 13, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(170, 12, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(171, 11, 1250.00, 1005.00, 245.00, 'paiement encours', '2025-03-22', '2024-2025'),
(172, 10, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(173, 9, 800.00, 720.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(174, 8, 400.00, 370.00, 30.00, 'paiement encours', '2025-03-22', '2024-2025'),
(175, 7, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(176, 6, 400.00, 160.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(177, 5, 400.00, 350.00, 50.00, 'paiement encours', '2025-03-22', '2024-2025'),
(178, 4, 400.00, 290.00, 110.00, 'paiement encours', '2025-03-22', '2024-2025'),
(179, 3, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(180, 2, 1250.00, 955.00, 295.00, 'paiement encours', '2025-03-22', '2024-2025'),
(181, 115, 450.00, 160.00, 290.00, 'paiement encours', '2025-03-22', '2024-2025'),
(182, 114, 850.00, 700.00, 150.00, 'paiement encours', '2025-03-22', '2024-2025'),
(183, 113, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(184, 112, 800.00, 630.00, 170.00, 'paiement encours', '2025-03-22', '2024-2025'),
(185, 111, 850.00, 675.00, 175.00, 'paiement encours', '2025-03-22', '2024-2025'),
(186, 110, 400.00, 370.00, 30.00, 'paiement encours', '2025-03-22', '2024-2025'),
(187, 109, 400.00, 130.00, 270.00, 'paiement encours', '2025-03-22', '2024-2025'),
(188, 108, 800.00, 800.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(189, 107, 400.00, 310.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(190, 106, 400.00, 400.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(191, 105, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(192, 104, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(193, 103, 1250.00, 1010.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(194, 102, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(195, 101, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(196, 100, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(197, 99, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(198, 98, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(199, 97, 800.00, 600.00, 200.00, 'paiement encours', '2025-03-22', '2024-2025'),
(200, 95, 1200.00, 890.00, 310.00, 'paiement encours', '2025-03-22', '2024-2025'),
(201, 94, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(202, 93, 450.00, 339.00, 111.00, 'paiement encours', '2025-03-22', '2024-2025'),
(203, 92, 1200.00, 1000.00, 200.00, 'paiement encours', '2025-03-22', '2024-2025'),
(204, 91, 800.00, 720.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(205, 90, 400.00, 240.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(206, 89, 400.00, 305.00, 95.00, 'paiement encours', '2025-03-22', '2024-2025'),
(207, 88, 450.00, 300.00, 150.00, 'paiement encours', '2025-03-22', '2024-2025'),
(208, 87, 400.00, 310.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(209, 86, 800.00, 320.00, 480.00, 'paiement encours', '2025-03-22', '2024-2025'),
(210, 85, 400.00, 290.00, 110.00, 'paiement encours', '2025-03-22', '2024-2025'),
(211, 84, 450.00, 360.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(212, 79, 1600.00, 150.00, 1450.00, 'paiement encours', '2025-03-22', '2024-2025'),
(213, 78, 800.00, 660.00, 140.00, 'paiement encours', '2025-03-22', '2024-2025'),
(214, 77, 800.00, 660.00, 140.00, 'paiement encours', '2025-03-22', '2024-2025'),
(215, 76, 1200.00, 960.00, 240.00, 'paiement encours', '2025-03-22', '2024-2025'),
(216, 75, 450.00, 310.00, 140.00, 'paiement encours', '2025-03-22', '2024-2025'),
(217, 80, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(218, 81, 450.00, 250.00, 200.00, 'paiement encours', '2025-03-22', '2024-2025'),
(219, 82, 800.00, 800.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(220, 73, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(221, 64, 800.00, 610.00, 190.00, 'paiement encours', '2025-03-22', '2024-2025'),
(222, 66, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(223, 65, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(224, 57, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(225, 83, 400.00, 275.00, 125.00, 'paiement encours', '2025-03-22', '2024-2025'),
(226, 63, 850.00, 600.00, 250.00, 'paiement encours', '2025-03-22', '2024-2025'),
(227, 67, 400.00, 400.00, 0.00, 'paiement soldé', '2025-03-22', '2024-2025'),
(228, 68, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(229, 69, 1747.00, 1090.00, 657.00, 'paiement encours', '2025-03-22', '2024-2025'),
(230, 70, 600.00, 510.00, 90.00, 'paiement encours', '2025-03-22', '2024-2025'),
(231, 96, 800.00, 280.00, 520.00, 'paiement encours', '2025-03-22', '2024-2025'),
(232, 205, 250.00, 20.00, 230.00, 'paiement encours', '2025-03-24', '2024-2025'),
(233, 239, 170.00, 20.00, 150.00, 'paiement encours', '2025-03-24', '2024-2025'),
(234, 41, 80.00, 30.00, 50.00, 'paiement encours', '2025-03-24', '2024-2025'),
(235, 213, 382.00, 35.00, 347.00, 'paiement encours', '2025-03-24', '2024-2025'),
(236, 123, 90.00, 10.00, 80.00, 'paiement encours', '2025-03-24', '2024-2025'),
(237, 53, 170.00, 10.00, 160.00, 'paiement encours', '2025-03-24', '2024-2025'),
(238, 17, 100.00, 15.00, 85.00, 'paiement encours', '2025-03-24', '2024-2025'),
(239, 248, 130.00, 30.00, 100.00, 'paiement encours', '2025-03-24', '2024-2025'),
(240, 248, 400.00, 270.00, 130.00, 'paiement encours', '2025-03-22', '2024-2025'),
(241, 6, 240.00, 40.00, 200.00, 'paiement encours', '2025-03-24', '2024-2025'),
(242, 168, 90.00, 10.00, 80.00, 'paiement encours', '2025-03-24', '2024-2025'),
(243, 211, 100.00, 20.00, 80.00, 'paiement encours', '2025-03-24', '2024-2025'),
(244, 163, 107.00, 25.00, 82.00, 'paiement encours', '2025-03-24', '2024-2025'),
(245, 128, 223.00, 23.11, 200.00, 'paiement encours', '2025-03-24', '2024-2025'),
(246, 83, 125.00, 40.00, 85.00, 'paiement encours', '2025-03-24', '2024-2025'),
(247, 16, 90.00, 10.00, 80.00, 'paiement encours', '2025-03-24', '2024-2025'),
(248, 117, 205.00, 20.00, 186.00, 'paiement encours', '2025-03-24', '2024-2025'),
(249, 4, 110.00, 30.00, 80.00, 'paiement encours', '2025-03-24', '2024-2025'),
(250, 37, 225.00, 35.00, 190.00, 'paiement encours', '2025-03-24', '2024-2025'),
(251, 173, 120.00, 40.00, 80.00, 'paiement encours', '2025-03-24', '2024-2025'),
(252, 191, 120.00, 40.00, 80.00, 'paiement encours', '2025-03-24', '2024-2025'),
(253, 64, 190.00, 30.00, 160.00, 'paiement encours', '2025-03-24', '2024-2025'),
(254, 89, 95.00, 10.00, 85.00, 'paiement encours', '2025-03-24', '2024-2025'),
(255, 97, 200.00, 20.00, 180.00, 'paiement encours', '2025-03-26', '2024-2025'),
(256, 226, 181.00, 20.00, 161.00, 'paiement encours', '2025-03-26', '2024-2025'),
(257, 224, 115.00, 10.00, 105.00, 'paiement encours', '2025-03-26', '2024-2025'),
(258, 178, 510.00, 40.00, 470.00, 'paiement encours', '2025-03-26', '2024-2025'),
(259, 95, 310.00, 40.00, 270.00, 'paiement encours', '2025-03-26', '2024-2025'),
(260, 162, 410.00, 80.00, 330.35, 'paiement encours', '2025-03-26', '2024-2025'),
(261, 219, 222.00, 40.00, 182.00, 'paiement encours', '2025-03-26', '2024-2025'),
(262, 231, 325.00, 60.00, 265.00, 'paiement encours', '2025-03-26', '2024-2025'),
(263, 85, 110.00, 50.00, 60.00, 'paiement encours', '2025-03-26', '2024-2025'),
(264, 90, 159.66, 57.93, 101.73, 'paiement encours', '2025-03-26', '2024-2025'),
(265, 207, 90.69, 10.00, 80.69, 'paiement encours', '2025-03-28', '2024-2025'),
(266, 138, 95.00, 5.00, 90.00, 'paiement encours', '2025-03-28', '2024-2025'),
(267, 228, 130.00, 20.00, 110.00, 'paiement encours', '2025-03-28', '2024-2025'),
(268, 171, 365.52, 100.00, 265.52, 'paiement encours', '2025-03-28', '2024-2025'),
(269, 94, 160.00, 80.00, 80.00, 'paiement encours', '2025-03-28', '2024-2025'),
(270, 111, 175.00, 5.00, 169.33, 'paiement encours', '2025-03-28', '2024-2025'),
(271, 36, 80.00, 50.00, 30.00, 'paiement encours', '2025-03-28', '2024-2025'),
(272, 178, 470.00, 20.00, 450.00, 'paiement encours', '2025-03-28', '2024-2025'),
(273, 117, 186.00, 16.00, 170.00, 'paiement encours', '2025-03-31', '2024-2025'),
(274, 167, 140.00, 20.00, 120.00, 'paiement encours', '2025-03-31', '2024-2025'),
(275, 87, 90.00, 10.00, 80.00, 'paiement encours', '2025-03-31', '2024-2025'),
(276, 11, 245.00, 70.00, 175.00, 'paiement encours', '2025-03-31', '2024-2025'),
(277, 144, 160.00, 40.00, 120.00, 'paiement encours', '2025-04-01', '2024-2025'),
(278, 118, 175.00, 5.00, 170.00, 'paiement encours', '2025-04-01', '2024-2025'),
(279, 66, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-03', '2024-2025'),
(280, 111, 169.33, 50.00, 119.33, 'paiement encours', '2025-04-03', '2024-2025'),
(281, 17, 85.00, 5.00, 80.00, 'paiement encours', '2025-04-03', '2024-2025'),
(282, 225, 90.00, 40.00, 50.00, 'paiement encours', '2025-04-07', '2024-2025'),
(283, 180, 160.00, 100.00, 60.00, 'paiement encours', '2025-04-10', '2024-2025'),
(284, 34, 160.00, 30.00, 130.00, 'paiement encours', '2025-04-10', '2024-2025'),
(285, 72, 400.00, 280.00, 120.00, 'paiement encours', '2025-03-22', '2024-2025'),
(286, 72, 120.00, 40.00, 80.00, 'paiement encours', '2025-04-10', '2024-2025'),
(287, 241, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-18', '2024-2025'),
(288, 50, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-18', '2024-2025'),
(289, 84, 90.00, 45.00, 45.00, 'paiement encours', '2025-04-18', '2024-2025'),
(290, 246, 240.00, 120.00, 120.00, 'paiement encours', '2025-04-18', '2024-2025'),
(291, 192, 605.00, 150.00, 455.00, 'paiement encours', '2025-04-18', '2024-2025'),
(292, 156, 150.00, 100.00, 50.00, 'paiement encours', '2025-04-18', '2024-2025'),
(293, 26, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-19', '2024-2025'),
(294, 34, 130.00, 50.00, 80.00, 'paiement encours', '2025-04-19', '2024-2025'),
(295, 122, 160.00, 80.00, 80.00, 'paiement encours', '2025-04-21', '2024-2025'),
(296, 155, 120.00, 40.00, 80.00, 'paiement encours', '2025-04-21', '2024-2025'),
(297, 57, 160.00, 80.00, 80.00, 'paiement encours', '2025-04-21', '2024-2025'),
(298, 11, 175.00, 30.00, 145.00, 'paiement encours', '2025-04-21', '2024-2025'),
(299, 235, 200.00, 200.00, 0.00, 'paiement soldé', '2025-04-21', '2024-2025'),
(300, 65, 80.00, 34.48, 45.52, 'paiement encours', '2025-04-21', '2024-2025'),
(301, 101, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-21', '2024-2025'),
(302, 3, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-21', '2024-2025'),
(303, 80, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-21', '2024-2025'),
(304, 212, 100.00, 50.00, 50.00, 'paiement encours', '2025-04-21', '2024-2025'),
(305, 69, 657.00, 200.00, 457.00, 'paiement encours', '2025-04-21', '2024-2025'),
(306, 120, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-21', '2024-2025'),
(307, 181, 80.00, 30.00, 50.00, 'paiement encours', '2025-04-21', '2024-2025'),
(308, 128, 200.00, 50.00, 150.00, 'paiement encours', '2025-04-21', '2024-2025'),
(309, 116, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-21', '2024-2025'),
(310, 131, 80.00, 20.00, 60.00, 'paiement encours', '2025-04-21', '2024-2025'),
(311, 72, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-21', '2024-2025'),
(312, 104, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-21', '2024-2025'),
(313, 222, 160.00, 80.00, 80.00, 'paiement encours', '2025-04-21', '2024-2025'),
(314, 88, 150.00, 100.00, 50.00, 'paiement encours', '2025-04-21', '2024-2025'),
(315, 20, 80.00, 50.00, 30.00, 'paiement encours', '2025-04-21', '2024-2025'),
(316, 24, 100.00, 20.00, 80.00, 'paiement encours', '2025-04-21', '2024-2025'),
(317, 113, 160.00, 80.00, 80.00, 'paiement encours', '2025-04-21', '2024-2025'),
(318, 200, 150.00, 150.00, 0.00, 'paiement soldé', '2025-04-21', '2024-2025'),
(319, 111, 119.33, 34.65, 84.68, 'paiement encours', '2025-04-21', '2024-2025'),
(320, 21, 300.00, 200.00, 100.00, 'paiement encours', '2025-04-22', '2024-2025'),
(321, 11, 145.00, 10.00, 135.00, 'paiement encours', '2025-04-22', '2024-2025'),
(322, 232, 80.00, 20.00, 60.00, 'paiement encours', '2025-04-22', '2024-2025'),
(323, 224, 105.00, 30.00, 75.00, 'paiement encours', '2025-04-22', '2024-2025'),
(324, 21, 900.00, 600.00, 300.00, 'paiement encours', '2025-03-22', '2024-2025'),
(325, 65, 45.52, 5.51, 40.01, 'paiement encours', '2025-04-22', '2024-2025'),
(326, 210, 80.00, 50.00, 30.00, 'paiement encours', '2025-04-22', '2024-2025'),
(327, 234, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(328, 53, 160.00, 40.00, 120.00, 'paiement encours', '2025-04-22', '2024-2025'),
(329, 174, 260.00, 130.00, 130.00, 'paiement encours', '2025-04-22', '2024-2025'),
(330, 112, 170.00, 30.00, 140.00, 'paiement encours', '2025-04-22', '2024-2025'),
(331, 43, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(332, 133, 100.00, 50.00, 50.00, 'paiement encours', '2025-04-22', '2024-2025'),
(333, 107, 90.00, 30.00, 60.00, 'paiement encours', '2025-04-22', '2024-2025'),
(334, 19, 80.00, 20.00, 60.00, 'paiement encours', '2025-04-22', '2024-2025'),
(335, 172, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(336, 62, 360.00, 120.00, 240.00, 'paiement encours', '2025-04-22', '2024-2025'),
(337, 98, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(338, 18, 60.00, 30.00, 30.00, 'paiement encours', '2025-04-22', '2024-2025'),
(339, 150, 210.00, 200.00, 10.00, 'paiement encours', '2025-04-22', '2024-2025'),
(340, 145, 160.00, 100.00, 60.00, 'paiement encours', '2025-04-22', '2024-2025'),
(341, 212, 50.00, 20.00, 30.00, 'paiement encours', '2025-04-22', '2024-2025'),
(342, 194, 80.00, 20.00, 60.00, 'paiement encours', '2025-04-22', '2024-2025'),
(343, 244, 80.00, 50.00, 30.00, 'paiement encours', '2025-04-22', '2024-2025'),
(344, 190, 240.00, 120.00, 120.00, 'paiement encours', '2025-04-22', '2024-2025'),
(345, 242, 250.00, 50.00, 200.00, 'paiement encours', '2025-04-22', '2024-2025'),
(346, 23, 130.00, 50.00, 80.00, 'paiement encours', '2025-04-22', '2024-2025'),
(347, 197, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(348, 136, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(349, 232, 60.00, 20.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(350, 162, 330.35, 80.34, 250.01, 'paiement encours', '2025-04-22', '2024-2025'),
(351, 77, 140.00, 70.00, 70.00, 'paiement encours', '2025-04-22', '2024-2025'),
(352, 75, 140.00, 140.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(353, 52, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(354, 62, 1200.00, 840.00, 360.00, 'paiement encours', '2025-03-22', '2024-2025'),
(355, 150, 800.00, 590.00, 210.00, 'paiement encours', '2025-03-22', '2024-2025'),
(356, 46, 230.00, 100.00, 130.00, 'paiement encours', '2025-04-22', '2024-2025'),
(357, 230, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(358, 186, 305.00, 100.00, 205.00, 'paiement encours', '2025-04-22', '2024-2025'),
(359, 35, 300.00, 300.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(360, 38, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(361, 121, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-22', '2024-2025'),
(362, 139, 85.52, 40.00, 45.52, 'paiement encours', '2025-04-22', '2024-2025'),
(363, 146, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(364, 147, 85.00, 30.00, 55.00, 'paiement encours', '2025-04-22', '2024-2025'),
(365, 88, 50.00, 50.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(366, 218, 80.00, 80.00, 0.00, 'paiement soldé', '2025-04-22', '2024-2025'),
(367, 245, 262.00, 100.00, 162.00, 'paiement encours', '2025-04-22', '2024-2025'),
(368, 73, 100.00, 50.00, 50.00, 'paiement encours', '2025-04-23', '2024-2025'),
(369, 16, 80.00, 20.00, 60.00, 'paiement encours', '2025-04-23', '2024-2025'),
(370, 207, 80.69, 40.00, 40.69, 'paiement encours', '2025-04-23', '2024-2025'),
(371, 227, 120.00, 80.00, 40.00, 'paiement encours', '2025-04-23', '2024-2025'),
(372, 103, 240.00, 150.00, 90.00, 'paiement encours', '2025-04-23', '2024-2025'),
(373, 144, 120.00, 40.00, 80.00, 'paiement encours', '2025-04-23', '2024-2025'),
(374, 169, 80.00, 10.00, 70.00, 'paiement encours', '2025-04-23', '2024-2025'),
(375, 63, 250.00, 70.00, 180.00, 'paiement encours', '2025-04-23', '2024-2025'),
(376, 233, 240.00, 50.00, 190.00, 'paiement encours', '2025-04-23', '2024-2025'),
(377, 13, 160.00, 40.00, 120.00, 'paiement encours', '2025-04-23', '2024-2025'),
(378, 102, 80.00, 30.00, 50.00, 'paiement encours', '2025-04-23', '2024-2025'),
(379, 74, 250.00, 250.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(380, 211, 80.00, 20.00, 60.00, 'paiement encours', '2025-04-23', '2024-2025'),
(381, 60, 80.00, 80.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(382, 28, 70.00, 20.00, 50.00, 'paiement encours', '2025-04-23', '2024-2025'),
(383, 24, 80.00, 80.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(384, 168, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-23', '2024-2025'),
(385, 83, 85.00, 40.00, 45.00, 'paiement encours', '2025-04-23', '2024-2025'),
(386, 19, 60.00, 20.00, 40.00, 'paiement encours', '2025-04-23', '2024-2025'),
(387, 181, 50.00, 10.00, 40.00, 'paiement encours', '2025-04-23', '2024-2025'),
(388, 226, 161.00, 80.00, 81.00, 'paiement encours', '2025-04-23', '2024-2025'),
(389, 231, 265.00, 60.00, 205.00, 'paiement encours', '2025-04-23', '2024-2025'),
(390, 74, 850.00, 600.00, 250.00, 'paiement encours', '2025-03-22', '2024-2025'),
(391, 60, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(392, 13, 120.00, 40.00, 80.00, 'paiement encours', '2025-04-23', '2024-2025'),
(393, 134, 202.00, 100.00, 102.00, 'paiement encours', '2025-04-23', '2024-2025'),
(394, 128, 150.00, 50.00, 100.00, 'paiement encours', '2025-04-23', '2024-2025'),
(395, 140, 170.00, 150.00, 20.00, 'paiement encours', '2025-04-23', '2024-2025'),
(396, 147, 55.00, 10.00, 45.00, 'paiement encours', '2025-04-23', '2024-2025'),
(397, 161, 50.00, 50.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(398, 242, 200.00, 200.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(399, 153, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(400, 187, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-23', '2024-2025'),
(401, 76, 240.00, 120.00, 120.00, 'paiement encours', '2025-04-23', '2024-2025'),
(402, 243, 160.00, 100.00, 60.00, 'paiement encours', '2025-04-23', '2024-2025'),
(403, 201, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(404, 93, 111.00, 30.00, 81.00, 'paiement encours', '2025-04-23', '2024-2025'),
(405, 15, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(406, 90, 101.73, 50.00, 51.73, 'paiement encours', '2025-04-23', '2024-2025'),
(407, 27, 150.00, 150.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(408, 81, 200.00, 50.00, 150.00, 'paiement encours', '2025-04-23', '2024-2025'),
(409, 228, 110.00, 20.00, 90.00, 'paiement encours', '2025-04-23', '2024-2025'),
(410, 40, 105.00, 30.00, 75.00, 'paiement encours', '2025-04-23', '2024-2025'),
(411, 130, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-23', '2024-2025'),
(412, 5, 50.00, 50.00, 0.00, 'paiement soldé', '2025-04-23', '2024-2025'),
(413, 189, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-24', '2024-2025'),
(414, 205, 230.00, 100.00, 130.00, 'paiement encours', '2025-04-24', '2024-2025'),
(415, 131, 60.00, 17.24, 42.76, 'paiement encours', '2025-04-24', '2024-2025'),
(416, 134, 102.00, 50.00, 52.00, 'paiement encours', '2025-04-24', '2024-2025'),
(417, 195, 160.00, 110.00, 50.00, 'paiement encours', '2025-04-24', '2024-2025'),
(418, 239, 150.00, 30.00, 120.00, 'paiement encours', '2025-04-24', '2024-2025'),
(419, 54, 80.00, 60.00, 20.00, 'paiement encours', '2025-04-24', '2024-2025'),
(420, 163, 82.00, 40.00, 42.00, 'paiement encours', '2025-04-24', '2024-2025'),
(421, 114, 150.00, 150.00, 0.00, 'paiement soldé', '2025-04-24', '2024-2025'),
(422, 229, 150.00, 150.00, 0.00, 'paiement soldé', '2025-04-24', '2024-2025'),
(423, 171, 265.52, 20.00, 245.52, 'paiement encours', '2025-04-24', '2024-2025'),
(424, 58, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-24', '2024-2025'),
(425, 8, 30.00, 30.00, 0.00, 'paiement soldé', '2025-04-24', '2024-2025'),
(426, 10, 80.00, 70.00, 10.00, 'paiement encours', '2025-04-24', '2024-2025'),
(427, 213, 347.00, 100.00, 247.00, 'paiement encours', '2025-04-24', '2024-2025'),
(428, 28, 50.00, 40.00, 10.00, 'paiement encours', '2025-04-24', '2024-2025'),
(429, 156, 50.00, 50.00, 0.00, 'paiement soldé', '2025-04-24', '2024-2025'),
(430, 4, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-24', '2024-2025'),
(431, 58, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(433, 41, 50.00, 50.00, 0.00, 'paiement soldé', '2025-04-25', '2024-2025'),
(434, 89, 85.00, 10.00, 75.00, 'paiement encours', '2025-04-25', '2024-2025'),
(435, 47, 240.00, 240.00, 0.00, 'paiement soldé', '2025-04-25', '2024-2025'),
(436, 56, 125.00, 80.00, 45.00, 'paiement encours', '2025-04-25', '2024-2025'),
(437, 78, 140.00, 100.00, 40.00, 'paiement encours', '2025-04-25', '2024-2025'),
(438, 142, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-25', '2024-2025'),
(439, 89, 75.00, 10.00, 65.00, 'paiement encours', '2025-04-25', '2024-2025'),
(440, 209, 85.00, 45.00, 40.00, 'paiement encours', '2025-04-25', '2024-2025'),
(441, 29, 90.00, 30.00, 60.00, 'paiement encours', '2025-04-26', '2024-2025'),
(442, 224, 75.00, 10.00, 65.00, 'paiement encours', '2025-04-26', '2024-2025'),
(443, 68, 80.00, 70.00, 10.00, 'paiement encours', '2025-04-26', '2024-2025'),
(444, 11, 135.00, 10.00, 125.00, 'paiement encours', '2025-04-26', '2024-2025'),
(445, 44, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-28', '2024-2025'),
(446, 158, 70.00, 60.00, 10.00, 'paiement encours', '2025-04-28', '2024-2025'),
(447, 191, 80.00, 50.00, 30.00, 'paiement encours', '2025-04-28', '2024-2025'),
(448, 239, 120.00, 10.00, 110.00, 'paiement encours', '2025-04-28', '2024-2025'),
(449, 167, 120.00, 10.00, 110.00, 'paiement encours', '2025-04-28', '2024-2025'),
(450, 57, 80.00, 80.00, 0.00, 'paiement soldé', '2025-04-28', '2024-2025'),
(451, 53, 120.00, 40.00, 80.00, 'paiement encours', '2025-04-28', '2024-2025'),
(452, 214, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-28', '2024-2025'),
(453, 165, 300.00, 200.00, 100.00, 'paiement encours', '2025-04-28', '2024-2025'),
(454, 119, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-28', '2024-2025'),
(455, 71, 160.00, 80.00, 80.00, 'paiement encours', '2025-04-28', '2024-2025'),
(456, 14, 100.00, 100.00, 0.00, 'paiement soldé', '2025-04-28', '2024-2025'),
(457, 221, 100.00, 40.00, 60.00, 'paiement encours', '2025-04-28', '2024-2025'),
(458, 37, 190.00, 30.00, 160.00, 'paiement encours', '2025-04-28', '2024-2025'),
(459, 12, 80.00, 50.00, 30.00, 'paiement encours', '2025-04-28', '2024-2025'),
(460, 149, 80.00, 40.00, 40.00, 'paiement encours', '2025-04-28', '2024-2025'),
(461, 59, 200.00, 140.00, 60.00, 'paiement encours', '2025-04-28', '2024-2025'),
(462, 59, 1250.00, 1050.00, 200.00, 'paiement encours', '2025-03-22', '2024-2025'),
(463, 71, 800.00, 640.00, 160.00, 'paiement encours', '2025-03-22', '2024-2025'),
(464, 236, 90.00, 40.00, 50.00, 'paiement encours', '2025-04-28', '2024-2025'),
(465, 45, 90.00, 40.00, 50.00, 'paiement encours', '2025-04-28', '2024-2025'),
(466, 143, 270.00, 135.00, 135.00, 'paiement encours', '2025-04-28', '2024-2025'),
(467, 220, 150.00, 150.00, 0.00, 'paiement soldé', '2025-04-28', '2024-2025'),
(468, 154, 275.00, 50.00, 225.00, 'paiement encours', '2025-04-28', '2024-2025'),
(469, 152, 80.00, 50.00, 30.00, 'paiement encours', '2025-04-29', '2024-2025'),
(470, 243, 60.00, 30.00, 30.00, 'paiement encours', '2025-04-29', '2024-2025'),
(471, 135, 100.00, 70.00, 30.00, 'paiement encours', '2025-04-29', '2024-2025'),
(472, 184, 82.00, 80.00, 2.00, 'paiement encours', '2025-04-29', '2024-2025'),
(473, 169, 70.00, 15.00, 55.00, 'paiement encours', '2025-04-29', '2024-2025'),
(474, 203, 70.00, 40.00, 30.00, 'paiement encours', '2025-04-29', '2024-2025'),
(475, 175, 500.00, 150.00, 350.00, 'paiement encours', '2025-04-29', '2024-2025'),
(476, 95, 270.00, 80.00, 190.00, 'paiement encours', '2025-04-29', '2024-2025'),
(477, 118, 170.00, 50.00, 120.00, 'paiement encours', '2025-04-29', '2024-2025'),
(478, 138, 90.00, 20.00, 70.00, 'paiement encours', '2025-04-29', '2024-2025'),
(479, 154, 225.00, 30.00, 195.00, 'paiement encours', '2025-04-29', '2024-2025'),
(480, 20, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-02', '2024-2025'),
(481, 56, 45.00, 45.00, 0.00, 'paiement soldé', '2025-05-02', '2024-2025'),
(482, 84, 45.00, 45.00, 0.00, 'paiement soldé', '2025-05-03', '2024-2025'),
(483, 239, 110.00, 30.00, 80.00, 'paiement encours', '2025-05-03', '2024-2025'),
(484, 122, 80.00, 60.00, 20.00, 'paiement encours', '2025-05-05', '2024-2025'),
(485, 134, 52.00, 50.00, 2.00, 'paiement encours', '2025-05-05', '2024-2025'),
(486, 121, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-05', '2024-2025'),
(487, 126, 80.00, 40.00, 40.00, 'paiement encours', '2025-05-05', '2024-2025'),
(488, 188, 160.00, 40.00, 120.00, 'paiement encours', '2025-05-05', '2024-2025'),
(489, 33, 70.00, 70.00, 0.00, 'paiement soldé', '2025-05-05', '2024-2025'),
(490, 30, 80.00, 40.00, 40.00, 'paiement encours', '2025-05-05', '2024-2025'),
(491, 212, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-05', '2024-2025'),
(492, 195, 50.00, 50.00, 0.00, 'paiement soldé', '2025-05-05', '2024-2025'),
(493, 77, 70.00, 70.00, 0.00, 'paiement soldé', '2025-05-05', '2024-2025'),
(494, 44, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-05', '2024-2025'),
(495, 89, 65.00, 10.00, 55.00, 'paiement encours', '2025-05-05', '2024-2025'),
(496, 171, 245.52, 20.00, 225.52, 'paiement encours', '2025-05-05', '2024-2025'),
(497, 127, 80.00, 20.00, 60.00, 'paiement encours', '2025-05-05', '2024-2025'),
(498, 198, 100.00, 50.00, 50.00, 'paiement encours', '2025-05-05', '2024-2025'),
(499, 198, 400.00, 300.00, 100.00, 'paiement encours', '2025-03-22', '2024-2025'),
(500, 112, 140.00, 80.00, 60.00, 'paiement encours', '2025-05-05', '2024-2025'),
(501, 174, 130.00, 60.00, 70.00, 'paiement encours', '2025-05-05', '2024-2025'),
(502, 170, 740.00, 100.00, 640.00, 'paiement encours', '2025-05-05', '2024-2025'),
(503, 16, 60.00, 30.00, 30.00, 'paiement encours', '2025-05-05', '2024-2025'),
(504, 225, 50.00, 35.00, 15.00, 'paiement encours', '2025-05-05', '2024-2025'),
(505, 193, 130.00, 40.00, 90.00, 'paiement encours', '2025-05-05', '2024-2025'),
(506, 213, 247.00, 100.00, 147.00, 'paiement encours', '2025-05-05', '2024-2025'),
(507, 133, 50.00, 50.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(508, 244, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(509, 134, 2.00, 2.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(510, 131, 42.76, 40.00, 2.76, 'paiement encours', '2025-05-06', '2024-2025'),
(511, 217, 80.00, 40.00, 40.00, 'paiement encours', '2025-05-06', '2024-2025'),
(512, 197, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(513, 19, 40.00, 10.00, 30.00, 'paiement encours', '2025-05-06', '2024-2025'),
(514, 92, 200.00, 30.00, 170.00, 'paiement encours', '2025-05-06', '2024-2025'),
(515, 105, 80.00, 60.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(516, 72, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(517, 176, 80.00, 20.00, 60.00, 'paiement encours', '2025-05-06', '2024-2025'),
(518, 230, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(519, 196, 136.21, 50.00, 86.21, 'paiement encours', '2025-05-06', '2024-2025'),
(520, 239, 80.00, 40.00, 40.00, 'paiement encours', '2025-05-06', '2024-2025'),
(521, 219, 182.00, 10.00, 172.00, 'paiement encours', '2025-05-06', '2024-2025'),
(522, 158, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(523, 48, 240.00, 100.00, 140.00, 'paiement encours', '2025-05-06', '2024-2025'),
(524, 144, 80.00, 40.00, 40.00, 'paiement encours', '2025-05-06', '2024-2025'),
(525, 107, 60.00, 30.00, 30.00, 'paiement encours', '2025-05-06', '2024-2025'),
(526, 222, 80.00, 60.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(527, 248, 100.00, 50.00, 50.00, 'paiement encours', '2025-05-06', '2024-2025'),
(528, 119, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(529, 85, 60.00, 40.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(530, 17, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(531, 160, 120.00, 100.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(532, 192, 455.00, 300.00, 155.00, 'paiement encours', '2025-05-06', '2024-2025'),
(534, 122, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(535, 232, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(536, 238, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-06', '2024-2025'),
(537, 111, 84.68, 50.00, 34.68, 'paiement encours', '2025-05-06', '2024-2025'),
(538, 138, 70.00, 20.00, 50.00, 'paiement encours', '2025-05-06', '2024-2025'),
(539, 94, 80.00, 60.00, 20.00, 'paiement encours', '2025-05-06', '2024-2025'),
(540, 215, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-07', '2024-2025'),
(541, 145, 60.00, 60.00, 0.00, 'paiement soldé', '2025-05-07', '2024-2025'),
(542, 80, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-07', '2024-2025'),
(543, 64, 160.00, 100.00, 60.00, 'paiement encours', '2025-05-07', '2024-2025'),
(544, 224, 65.00, 20.00, 45.00, 'paiement encours', '2025-05-07', '2024-2025'),
(545, 19, 30.00, 10.00, 20.00, 'paiement encours', '2025-05-07', '2024-2025'),
(546, 95, 190.00, 70.00, 120.00, 'paiement encours', '2025-05-07', '2024-2025'),
(547, 30, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-08', '2024-2025'),
(548, 243, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-08', '2024-2025'),
(549, 117, 170.00, 70.00, 100.00, 'paiement encours', '2025-05-08', '2024-2025'),
(550, 222, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-08', '2024-2025'),
(551, 164, 240.00, 100.00, 140.00, 'paiement encours', '2025-05-09', '2024-2025'),
(552, 177, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-09', '2024-2025'),
(553, 3, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-09', '2024-2025'),
(554, 185, 180.00, 180.00, 0.00, 'paiement soldé', '2025-05-09', '2024-2025'),
(555, 169, 55.00, 55.00, 0.00, 'paiement soldé', '2025-05-10', '2024-2025'),
(556, 123, 80.00, 30.00, 50.00, 'paiement encours', '2025-05-12', '2024-2025'),
(557, 174, 70.00, 70.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(558, 91, 80.00, 50.00, 30.00, 'paiement encours', '2025-05-12', '2024-2025'),
(559, 206, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(560, 107, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(561, 111, 34.68, 15.00, 19.68, 'paiement encours', '2025-05-12', '2024-2025'),
(562, 63, 180.00, 50.00, 130.00, 'paiement encours', '2025-05-12', '2024-2025'),
(563, 135, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(564, 127, 60.00, 15.00, 45.00, 'paiement encours', '2025-05-12', '2024-2025'),
(565, 69, 457.00, 200.00, 257.00, 'paiement encours', '2025-05-12', '2024-2025'),
(566, 165, 100.00, 100.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(567, 83, 45.00, 45.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(568, 227, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(569, 101, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(570, 245, 162.00, 120.00, 42.00, 'paiement encours', '2025-05-12', '2024-2025'),
(571, 64, 60.00, 30.00, 30.00, 'paiement encours', '2025-05-12', '2024-2025'),
(572, 162, 250.01, 150.00, 100.01, 'paiement encours', '2025-05-12', '2024-2025'),
(573, 226, 81.00, 80.00, 1.00, 'paiement encours', '2025-05-12', '2024-2025'),
(574, 210, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(575, 225, 15.00, 15.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(576, 71, 80.00, 65.00, 15.00, 'paiement encours', '2025-05-12', '2024-2025'),
(577, 105, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(578, 40, 75.00, 30.00, 45.00, 'paiement encours', '2025-05-12', '2024-2025'),
(579, 9, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-12', '2024-2025'),
(580, 128, 100.00, 20.00, 80.00, 'paiement encours', '2025-05-12', '2024-2025'),
(581, 233, 190.00, 50.00, 140.00, 'paiement encours', '2025-05-12', '2024-2025'),
(582, 224, 45.00, 30.00, 15.00, 'paiement encours', '2025-05-13', '2024-2025'),
(583, 168, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(584, 62, 240.00, 120.00, 120.00, 'paiement encours', '2025-05-13', '2024-2025'),
(585, 119, 20.00, 10.00, 10.00, 'paiement encours', '2025-05-13', '2024-2025'),
(586, 188, 120.00, 40.00, 80.00, 'paiement encours', '2025-05-13', '2024-2025'),
(587, 43, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(588, 180, 60.00, 25.00, 35.00, 'paiement encours', '2025-05-13', '2024-2025'),
(589, 26, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(590, 13, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(591, 73, 50.00, 50.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(592, 219, 172.00, 10.00, 162.00, 'paiement encours', '2025-05-13', '2024-2025'),
(593, 91, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(594, 136, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(595, 38, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(596, 196, 86.21, 20.00, 66.21, 'paiement encours', '2025-05-13', '2024-2025'),
(597, 87, 80.00, 50.00, 30.00, 'paiement encours', '2025-05-13', '2024-2025'),
(598, 148, 80.00, 20.00, 60.00, 'paiement encours', '2025-05-13', '2024-2025'),
(599, 12, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(600, 187, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-13', '2024-2025'),
(601, 186, 205.00, 105.00, 100.00, 'paiement encours', '2025-05-13', '2024-2025'),
(602, 76, 120.00, 110.00, 10.00, 'paiement encours', '2025-05-13', '2024-2025'),
(603, 231, 205.00, 60.00, 145.00, 'paiement encours', '2025-05-13', '2024-2025'),
(604, 233, 140.00, 50.00, 90.00, 'paiement encours', '2025-05-13', '2024-2025'),
(605, 4, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(606, 246, 120.00, 100.00, 20.00, 'paiement encours', '2025-05-13', '2024-2025'),
(607, 230, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(608, 207, 40.69, 40.00, 0.69, 'paiement encours', '2025-05-13', '2024-2025'),
(609, 54, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(610, 10, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(611, 154, 195.00, 50.00, 145.00, 'paiement encours', '2025-05-13', '2024-2025'),
(612, 236, 50.00, 50.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(613, 173, 80.00, 37.24, 42.76, 'paiement encours', '2025-05-13', '2024-2025'),
(614, 102, 50.00, 50.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(615, 25, 200.00, 100.00, 100.00, 'paiement encours', '2025-05-13', '2024-2025'),
(616, 138, 50.00, 20.00, 30.00, 'paiement encours', '2025-05-13', '2024-2025'),
(617, 143, 135.00, 135.00, 0.00, 'paiement soldé', '2025-05-13', '2024-2025'),
(618, 36, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(619, 183, 100.00, 80.00, 20.00, 'paiement encours', '2025-05-14', '2024-2025'),
(620, 213, 147.00, 50.00, 97.00, 'paiement encours', '2025-05-14', '2024-2025'),
(621, 219, 162.00, 10.00, 152.00, 'paiement encours', '2025-05-14', '2024-2025'),
(622, 149, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(623, 180, 35.00, 35.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(624, 205, 130.00, 50.00, 80.00, 'paiement encours', '2025-05-14', '2024-2025'),
(625, 140, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(626, 139, 45.52, 35.00, 10.52, 'paiement encours', '2025-05-14', '2024-2025'),
(627, 98, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(628, 18, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(629, 110, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(630, 189, 40.00, 30.00, 10.00, 'paiement encours', '2025-05-14', '2024-2025'),
(631, 117, 100.00, 15.00, 85.00, 'paiement encours', '2025-05-14', '2024-2025'),
(632, 113, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(633, 160, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(634, 144, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-14', '2024-2025'),
(635, 194, 60.00, 10.00, 50.00, 'paiement encours', '2025-05-14', '2024-2025'),
(636, 116, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(637, 19, 20.00, 10.00, 10.00, 'paiement encours', '2025-05-14', '2024-2025');
INSERT INTO `paiement` (`id`, `menage`, `montantAPayer`, `montantPayer`, `resteAPayer`, `observation`, `dateCreated`, `anneeScolaire`) VALUES
(638, 118, 120.00, 70.00, 50.00, 'paiement encours', '2025-05-14', '2024-2025'),
(639, 155, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-14', '2024-2025'),
(640, 90, 51.73, 40.00, 11.73, 'paiement encours', '2025-05-14', '2024-2025'),
(641, 128, 80.00, 20.00, 60.00, 'paiement encours', '2025-05-14', '2024-2025'),
(642, 16, 30.00, 10.00, 20.00, 'paiement encours', '2025-05-14', '2024-2025'),
(643, 7, 100.00, 40.00, 60.00, 'paiement encours', '2025-05-14', '2024-2025'),
(644, 34, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-15', '2024-2025'),
(645, 203, 30.00, 20.00, 10.00, 'paiement encours', '2025-05-15', '2024-2025'),
(646, 76, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-15', '2024-2025'),
(647, 211, 60.00, 20.00, 40.00, 'paiement encours', '2025-05-15', '2024-2025'),
(648, 173, 42.76, 20.00, 22.76, 'paiement encours', '2025-05-15', '2024-2025'),
(649, 176, 60.00, 20.00, 40.00, 'paiement encours', '2025-05-15', '2024-2025'),
(650, 78, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-15', '2024-2025'),
(651, 11, 125.00, 100.00, 25.00, 'paiement encours', '2025-05-15', '2024-2025'),
(652, 63, 130.00, 50.00, 80.00, 'paiement encours', '2025-05-15', '2024-2025'),
(653, 246, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-19', '2024-2025'),
(654, 219, 152.00, 10.00, 142.00, 'paiement encours', '2025-05-19', '2024-2025'),
(655, 19, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-19', '2024-2025'),
(656, 190, 120.00, 110.00, 10.00, 'paiement encours', '2025-05-19', '2024-2025'),
(657, 148, 60.00, 10.00, 50.00, 'paiement encours', '2025-05-19', '2024-2025'),
(658, 130, 40.00, 30.00, 10.00, 'paiement encours', '2025-05-19', '2024-2025'),
(659, 59, 60.00, 60.00, 0.00, 'paiement soldé', '2025-05-19', '2024-2025'),
(660, 232, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-19', '2024-2025'),
(661, 68, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-19', '2024-2025'),
(662, 119, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-19', '2024-2025'),
(663, 37, 160.00, 50.00, 110.00, 'paiement encours', '2025-05-20', '2024-2025'),
(664, 192, 155.00, 155.00, 0.00, 'paiement soldé', '2025-05-20', '2024-2025'),
(665, 219, 142.00, 10.00, 132.00, 'paiement encours', '2025-05-20', '2024-2025'),
(666, 65, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-20', '2024-2025'),
(667, 157, 50.00, 50.00, 0.00, 'paiement soldé', '2025-05-20', '2024-2025'),
(668, 209, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-20', '2024-2025'),
(669, 181, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-21', '2024-2025'),
(670, 167, 110.00, 10.00, 100.00, 'paiement encours', '2025-05-21', '2024-2025'),
(671, 50, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-21', '2024-2025'),
(672, 213, 97.00, 80.00, 17.00, 'paiement encours', '2025-05-21', '2024-2025'),
(673, 130, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-21', '2024-2025'),
(674, 61, 800.00, 450.00, 350.00, 'paiement encours', '2025-03-22', '2024-2025'),
(675, 23, 80.00, 80.00, 0.00, 'paiement soldé', '2025-05-21', '2024-2025'),
(676, 189, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-22', '2024-2025'),
(677, 198, 50.00, 50.00, 0.00, 'paiement soldé', '2025-05-22', '2024-2025'),
(678, 93, 81.00, 40.00, 41.00, 'paiement encours', '2025-05-22', '2024-2025'),
(679, 46, 130.00, 100.00, 30.00, 'paiement encours', '2025-05-23', '2024-2025'),
(680, 64, 30.00, 20.00, 10.00, 'paiement encours', '2025-05-26', '2024-2025'),
(681, 213, 17.00, 17.00, 0.00, 'paiement soldé', '2025-05-26', '2024-2025'),
(682, 21, 100.00, 100.00, 0.00, 'paiement soldé', '2025-05-26', '2024-2025'),
(683, 63, 80.00, 50.00, 30.00, 'paiement encours', '2025-05-26', '2024-2025'),
(684, 245, 42.00, 20.00, 22.00, 'paiement encours', '2025-05-26', '2024-2025'),
(685, 191, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-26', '2024-2025'),
(686, 53, 80.00, 45.00, 35.00, 'paiement encours', '2025-05-26', '2024-2025'),
(687, 148, 50.00, 20.00, 30.00, 'paiement encours', '2025-05-26', '2024-2025'),
(688, 144, 20.00, 10.00, 10.00, 'paiement encours', '2025-05-26', '2024-2025'),
(689, 11, 25.00, 20.00, 5.00, 'paiement encours', '2025-05-26', '2024-2025'),
(690, 233, 90.00, 50.00, 40.00, 'paiement encours', '2025-05-26', '2024-2025'),
(691, 181, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-26', '2024-2025'),
(692, 40, 45.00, 20.00, 25.00, 'paiement encours', '2025-05-26', '2024-2025'),
(693, 162, 100.01, 100.00, 0.01, 'paiement encours', '2025-05-26', '2024-2025'),
(694, 126, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-27', '2024-2025'),
(695, 167, 100.00, 10.00, 90.00, 'paiement encours', '2025-05-27', '2024-2025'),
(696, 112, 60.00, 15.00, 45.00, 'paiement encours', '2025-05-27', '2024-2025'),
(697, 147, 45.00, 25.00, 20.00, 'paiement encours', '2025-05-27', '2024-2025'),
(698, 104, 40.00, 30.00, 10.00, 'paiement encours', '2025-05-27', '2024-2025'),
(699, 219, 132.00, 10.00, 122.00, 'paiement encours', '2025-05-27', '2024-2025'),
(700, 95, 120.00, 100.00, 20.00, 'paiement encours', '2025-05-27', '2024-2025'),
(701, 176, 40.00, 25.00, 15.00, 'paiement encours', '2025-05-27', '2024-2025'),
(702, 117, 85.00, 30.00, 55.00, 'paiement encours', '2025-05-27', '2024-2025'),
(703, 90, 11.73, 11.73, 0.00, 'paiement soldé', '2025-05-27', '2024-2025'),
(704, 138, 30.00, 20.00, 10.00, 'paiement encours', '2025-05-27', '2024-2025'),
(705, 188, 80.00, 30.00, 50.00, 'paiement encours', '2025-05-27', '2024-2025'),
(706, 97, 180.00, 80.00, 100.00, 'paiement encours', '2025-05-28', '2024-2025'),
(707, 46, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-28', '2024-2025'),
(708, 100, 160.00, 100.00, 60.00, 'paiement encours', '2025-05-28', '2024-2025'),
(709, 183, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-28', '2024-2025'),
(710, 248, 50.00, 30.00, 20.00, 'paiement encours', '2025-05-28', '2024-2025'),
(711, 219, 122.00, 10.00, 112.00, 'paiement encours', '2025-05-28', '2024-2025'),
(712, 205, 80.00, 50.00, 30.00, 'paiement encours', '2025-05-28', '2024-2025'),
(713, 214, 40.00, 40.00, 0.00, 'paiement soldé', '2025-05-28', '2024-2025'),
(714, 203, 10.00, 10.00, 0.00, 'paiement soldé', '2025-05-28', '2024-2025'),
(715, 223, 90.00, 50.00, 40.00, 'paiement encours', '2025-05-28', '2024-2025'),
(716, 187, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-28', '2024-2025'),
(717, 30, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-28', '2024-2025'),
(718, 231, 145.00, 60.00, 85.00, 'paiement encours', '2025-05-28', '2024-2025'),
(719, 223, 40.00, 20.00, 20.00, 'paiement encours', '2025-05-28', '2024-2025'),
(720, 93, 41.00, 30.00, 11.00, 'paiement encours', '2025-05-28', '2024-2025'),
(721, 100, 60.00, 60.00, 0.00, 'paiement soldé', '2025-05-29', '2024-2025'),
(722, 16, 20.00, 10.00, 10.00, 'paiement encours', '2025-05-29', '2024-2025'),
(723, 224, 15.00, 10.00, 5.00, 'paiement encours', '2025-05-29', '2024-2025'),
(724, 118, 50.00, 20.00, 30.00, 'paiement encours', '2025-05-29', '2024-2025'),
(725, 170, 640.00, 20.00, 620.00, 'paiement encours', '2025-05-29', '2024-2025'),
(726, 111, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-29', '2024-2025'),
(727, 29, 60.00, 40.00, 20.00, 'paiement encours', '2025-05-29', '2024-2025'),
(729, 245, 22.00, 22.00, 0.00, 'paiement soldé', '2025-05-30', '2024-2025'),
(730, 221, 60.00, 20.00, 40.00, 'paiement encours', '2025-05-30', '2024-2025'),
(731, 231, 85.00, 85.00, 0.00, 'paiement soldé', '2025-05-30', '2024-2025'),
(732, 63, 30.00, 30.00, 0.00, 'paiement soldé', '2025-05-30', '2024-2025'),
(733, 94, 20.00, 20.00, 0.00, 'paiement soldé', '2025-05-30', '2024-2025'),
(734, 16, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(735, 164, 140.00, 100.00, 40.00, 'paiement encours', '2025-06-02', '2024-2025'),
(736, 112, 45.00, 20.00, 25.00, 'paiement encours', '2025-06-02', '2024-2025'),
(737, 127, 45.00, 40.00, 5.00, 'paiement encours', '2025-06-02', '2024-2025'),
(738, 221, 40.00, 10.00, 30.00, 'paiement encours', '2025-06-02', '2024-2025'),
(739, 62, 120.00, 120.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(740, 69, 257.00, 100.00, 157.00, 'paiement encours', '2025-06-02', '2024-2025'),
(741, 196, 66.21, 50.00, 16.21, 'paiement encours', '2025-06-02', '2024-2025'),
(742, 144, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(743, 167, 90.00, 50.00, 40.00, 'paiement encours', '2025-06-02', '2024-2025'),
(744, 118, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(745, 151, 400.00, 320.00, 80.00, 'paiement encours', '2025-03-22', '2024-2025'),
(746, 151, 80.00, 50.00, 30.00, 'paiement encours', '2025-06-02', '2024-2025'),
(747, 186, 100.00, 100.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(748, 217, 40.00, 40.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(749, 7, 60.00, 60.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(750, 61, 350.00, 100.00, 250.00, 'paiement encours', '2025-06-02', '2024-2025'),
(751, 93, 11.00, 10.00, 1.00, 'paiement encours', '2025-06-02', '2024-2025'),
(752, 45, 50.00, 50.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(753, 138, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(754, 78, 20.00, 20.00, 0.00, 'paiement soldé', '2025-06-02', '2024-2025'),
(755, 128, 60.00, 20.00, 40.00, 'paiement encours', '2025-06-02', '2024-2025'),
(756, 193, 90.00, 40.00, 50.00, 'paiement encours', '2025-06-02', '2024-2025'),
(757, 228, 90.00, 40.00, 50.00, 'paiement encours', '2025-06-02', '2024-2025'),
(758, 228, 50.00, 20.00, 30.00, 'paiement encours', '2025-06-02', '2024-2025'),
(759, 196, 16.21, 10.00, 6.21, 'paiement encours', '2025-06-03', '2024-2025'),
(760, 117, 55.00, 30.00, 25.00, 'paiement encours', '2025-06-03', '2024-2025'),
(761, 123, 50.00, 40.00, 10.00, 'paiement encours', '2025-06-03', '2024-2025'),
(762, 53, 35.00, 30.00, 5.00, 'paiement encours', '2025-06-03', '2024-2025'),
(763, 37, 110.00, 50.00, 60.00, 'paiement encours', '2025-06-03', '2024-2025'),
(764, 173, 22.76, 10.00, 12.76, 'paiement encours', '2025-06-03', '2024-2025'),
(765, 6, 200.00, 20.00, 180.00, 'paiement encours', '2025-06-03', '2024-2025'),
(766, 163, 42.00, 7.00, 35.00, 'paiement encours', '2025-06-03', '2024-2025'),
(767, 48, 140.00, 110.00, 30.00, 'paiement encours', '2025-06-03', '2024-2025'),
(768, 85, 20.00, 10.00, 10.00, 'paiement encours', '2025-06-03', '2024-2025'),
(769, 211, 40.00, 40.00, 0.00, 'paiement soldé', '2025-06-03', '2024-2025'),
(770, 71, 15.00, 15.00, 0.00, 'paiement soldé', '2025-06-03', '2024-2025'),
(771, 112, 25.00, 15.00, 10.00, 'paiement encours', '2025-06-03', '2024-2025'),
(772, 205, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-03', '2024-2025'),
(773, 176, 15.00, 15.00, 0.00, 'paiement soldé', '2025-06-03', '2024-2025'),
(774, 87, 30.00, 20.00, 10.00, 'paiement encours', '2025-06-03', '2024-2025'),
(775, 40, 25.00, 25.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(776, 131, 2.76, 2.76, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(777, 128, 40.00, 40.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(778, 163, 35.00, 35.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(779, 221, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(780, 103, 90.00, 50.00, 40.00, 'paiement encours', '2025-06-04', '2024-2025'),
(781, 95, 20.00, 20.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(782, 97, 100.00, 30.00, 70.00, 'paiement encours', '2025-06-04', '2024-2025'),
(783, 25, 100.00, 100.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(784, 228, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(785, 126, 20.00, 20.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(786, 193, 50.00, 20.00, 30.00, 'paiement encours', '2025-06-04', '2024-2025'),
(787, 188, 50.00, 50.00, 0.00, 'paiement soldé', '2025-06-04', '2024-2025'),
(788, 223, 20.00, 20.00, 0.00, 'paiement soldé', '2025-06-05', '2024-2025'),
(789, 29, 20.00, 20.00, 0.00, 'paiement soldé', '2025-06-05', '2024-2025'),
(790, 115, 290.00, 100.00, 190.00, 'paiement encours', '2025-06-05', '2024-2025'),
(791, 93, 1.00, 1.00, 0.00, 'paiement soldé', '2025-06-05', '2024-2025'),
(792, 178, 450.00, 80.00, 370.00, 'paiement encours', '2025-06-09', '2024-2025'),
(793, 69, 157.00, 157.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(794, 48, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(795, 85, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(796, 175, 350.00, 150.00, 200.00, 'paiement encours', '2025-06-09', '2024-2025'),
(797, 53, 5.00, 5.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(798, 171, 225.52, 50.00, 175.52, 'paiement encours', '2025-06-09', '2024-2025'),
(799, 0, 0.00, 0.00, 0.00, 'paiement encours', '2025-06-09', '2024-2025'),
(800, 190, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(801, 154, 145.00, 20.00, 125.00, 'paiement encours', '2025-06-09', '2024-2025'),
(802, 2, 295.00, 100.00, 195.00, 'paiement encours', '2025-06-09', '2024-2025'),
(803, 92, 170.00, 30.00, 140.00, 'paiement encours', '2025-06-09', '2024-2025'),
(804, 194, 50.00, 20.00, 30.00, 'paiement encours', '2025-06-09', '2024-2025'),
(805, 148, 30.00, 20.00, 10.00, 'paiement encours', '2025-06-09', '2024-2025'),
(806, 99, 80.00, 40.00, 40.00, 'paiement encours', '2025-06-09', '2024-2025'),
(807, 233, 40.00, 40.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(808, 104, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(809, 207, 0.69, 0.69, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(810, 196, 6.21, 6.21, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(811, 147, 20.00, 20.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(812, 193, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(813, 6, 180.00, 90.00, 90.00, 'paiement encours', '2025-06-09', '2024-2025'),
(814, 11, 5.00, 5.00, 0.00, 'paiement soldé', '2025-06-09', '2024-2025'),
(815, 87, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-10', '2024-2025'),
(816, 117, 25.00, 10.00, 15.00, 'paiement encours', '2025-06-10', '2024-2025'),
(817, 194, 30.00, 20.00, 10.00, 'paiement encours', '2025-06-10', '2024-2025'),
(818, 151, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-10', '2024-2025'),
(819, 117, 15.00, 10.00, 5.00, 'paiement encours', '2025-06-11', '2024-2025'),
(820, 164, 40.00, 40.00, 0.00, 'paiement soldé', '2025-06-11', '2024-2025'),
(821, 2, 195.00, 30.00, 165.00, 'paiement encours', '2025-06-11', '2024-2025'),
(822, 167, 40.00, 10.00, 30.00, 'paiement encours', '2025-06-11', '2024-2025'),
(823, 97, 70.00, 40.00, 30.00, 'paiement encours', '2025-06-11', '2024-2025'),
(824, 99, 40.00, 20.00, 20.00, 'paiement encours', '2025-06-11', '2024-2025'),
(825, 61, 250.00, 250.00, 0.00, 'paiement soldé', '2025-06-11', '2024-2025'),
(826, 37, 60.00, 35.00, 25.00, 'paiement encours', '2025-06-16', '2024-2025'),
(827, 175, 200.00, 120.00, 80.00, 'paiement encours', '2025-06-16', '2024-2025'),
(828, 97, 30.00, 30.00, 0.00, 'paiement soldé', '2025-06-16', '2024-2025'),
(829, 167, 30.00, 15.00, 15.00, 'paiement encours', '2025-06-16', '2024-2025'),
(830, 194, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-16', '2024-2025'),
(831, 28, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-16', '2024-2025'),
(832, 154, 125.00, 30.00, 95.00, 'paiement encours', '2025-06-16', '2024-2025'),
(833, 117, 5.00, 5.00, 0.00, 'paiement soldé', '2025-06-16', '2024-2025'),
(834, 99, 20.00, 15.00, 5.00, 'paiement encours', '2025-06-17', '2024-2025'),
(835, 6, 90.00, 20.00, 70.00, 'paiement encours', '2025-06-17', '2024-2025'),
(836, 148, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-17', '2024-2025'),
(837, 173, 12.76, 9.60, 3.16, 'paiement encours', '2025-06-17', '2024-2025'),
(838, 167, 15.00, 15.00, 0.00, 'paiement soldé', '2025-06-17', '2024-2025'),
(839, 178, 370.00, 20.00, 350.00, 'paiement encours', '2025-06-17', '2024-2025'),
(840, 224, 5.00, 5.00, 0.00, 'paiement soldé', '2025-06-18', '2024-2025'),
(841, 99, 5.00, 5.00, 0.00, 'paiement soldé', '2025-06-18', '2024-2025'),
(842, 170, 620.00, 50.00, 570.00, 'paiement encours', '2025-06-19', '2024-2025'),
(843, 2, 165.00, 50.00, 115.00, 'paiement encours', '2025-06-19', '2024-2025'),
(844, 2, 115.00, 30.00, 85.00, 'paiement encours', '2025-06-19', '2024-2025'),
(845, 64, 10.00, 10.00, 0.00, 'paiement soldé', '2025-06-24', '2024-2025'),
(846, 125, 240.00, 65.00, 175.00, 'paiement encours', '2025-06-26', '2024-2025'),
(847, 37, 25.00, 25.00, 0.00, 'paiement soldé', '2025-06-26', '2024-2025'),
(849, 251, 450.00, 100.00, 350.00, 'paiement encours', '2025-07-08', '2025-2026'),
(850, 252, 400.00, 120.00, 280.00, 'paiement encours', '2025-03-22', '2024-2025'),
(851, 253, 400.00, 60.00, 340.00, 'paiement encours', '2025-03-22', '2024-2025'),
(852, 254, 400.00, 195.00, 205.00, 'paiement encours', '2025-03-22', '2024-2025'),
(853, 255, 800.00, 150.00, 650.00, 'paiement encours', '2025-05-22', '2024-2025'),
(854, 247, 450.00, 117.58, 332.42, 'paiement encours', '2025-03-22', '2024-2025'),
(855, 139, 10.52, 10.52, 0.00, 'paiement soldé', '2025-05-16', '2024-2025'),
(856, 197, 800.00, 100.00, 700.00, 'paiement encours', '2025-07-11', '2025-2026');

-- --------------------------------------------------------

--
-- Structure de la table `paiement_detail`
--

CREATE TABLE `paiement_detail` (
  `id` int(11) NOT NULL,
  `paiement_id` int(11) NOT NULL,
  `menage_id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `tranche_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_created` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `prestation`
--

CREATE TABLE `prestation` (
  `id` int(11) NOT NULL,
  `agent` int(11) NOT NULL,
  `HA` time NOT NULL,
  `HD` time NOT NULL,
  `remarque` text NOT NULL,
  `dateCreated` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`id`, `assignment_id`, `question_text`) VALUES
(1, 1, '80X10'),
(2, 1, '4514X551'),
(3, 1, '5-4');

-- --------------------------------------------------------

--
-- Structure de la table `scolarite`
--

CREATE TABLE `scolarite` (
  `id` int(11) NOT NULL,
  `description` varchar(20) NOT NULL,
  `cycle` int(11) NOT NULL,
  `montant` decimal(10,0) NOT NULL,
  `dateCreated` date NOT NULL,
  `dateUpdate` date NOT NULL,
  `createdby` text NOT NULL,
  `anneeScolaire` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `scolarite`
--

INSERT INTO `scolarite` (`id`, `description`, `cycle`, `montant`, `dateCreated`, `dateUpdate`, `createdby`, `anneeScolaire`) VALUES
(1, 'Frais scolaires', 1, 400, '2025-02-24', '2025-02-24', 'Administrateur(trice)', '2024-2025'),
(2, 'Frais scolaire', 2, 400, '2025-03-07', '2025-03-07', 'Administrateur(trice)', '2024-2025'),
(3, 'Frais scolaire', 3, 450, '2025-03-08', '2025-03-08', 'Administrateur(trice)', '2024-2025'),
(4, 'Frais scolaire', 4, 450, '2025-03-08', '2025-03-08', 'Administrateur(trice)', '2024-2025');

-- --------------------------------------------------------

--
-- Structure de la table `systeme`
--

CREATE TABLE `systeme` (
  `id` int(11) NOT NULL,
  `devise` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `systeme`
--

INSERT INTO `systeme` (`id`, `devise`) VALUES
(1, '$');

-- --------------------------------------------------------

--
-- Structure de la table `tranche`
--

CREATE TABLE `tranche` (
  `id` int(11) NOT NULL,
  `frais_id` int(11) DEFAULT NULL,
  `numero_tranche` int(11) DEFAULT NULL,
  `montant` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Déchargement des données de la table `tranche`
--

INSERT INTO `tranche` (`id`, `frais_id`, `numero_tranche`, `montant`) VALUES
(1, 1, 1, 150.00),
(2, 1, 2, 100.00),
(3, 1, 3, 100.00),
(4, 1, 4, 50.00),
(5, 2, 1, 150.00),
(6, 2, 2, 100.00),
(7, 2, 3, 100.00),
(8, 2, 4, 50.00),
(9, 3, 1, 200.00),
(10, 3, 2, 100.00),
(11, 3, 3, 100.00),
(12, 3, 4, 50.00),
(13, 4, 1, 200.00),
(14, 4, 2, 100.00),
(15, 4, 3, 100.00),
(16, 4, 4, 50.00);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `role` varchar(25) NOT NULL,
  `dateCreation` date NOT NULL,
  `dateModification` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `dateCreation`, `dateModification`) VALUES
(1, 'cs elma', '63982e54a7aeb0d89910475ba6dbd3ca6dd4e5a1', 'Administrateur(trice)', '2024-08-02', '2024-11-01');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `affectation_prof_classe`
--
ALTER TABLE `affectation_prof_classe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agent` (`agent`),
  ADD KEY `classe` (`classe`);

--
-- Index pour la table `agent`
--
ALTER TABLE `agent`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `annee_scolaire`
--
ALTER TABLE `annee_scolaire`
  ADD PRIMARY KEY (`id`,`annee_scolaire`);

--
-- Index pour la table `annonce`
--
ALTER TABLE `annonce`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Index pour la table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Index pour la table `assignment_corrections`
--
ALTER TABLE `assignment_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`,`eleve_id`) USING BTREE;

--
-- Index pour la table `balance`
--
ALTER TABLE `balance`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `classe`
--
ALTER TABLE `classe`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `correction_devoir_ensignant`
--
ALTER TABLE `correction_devoir_ensignant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Index pour la table `cycle`
--
ALTER TABLE `cycle`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `depenses`
--
ALTER TABLE `depenses`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `eleve`
--
ALTER TABLE `eleve`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `fonction`
--
ALTER TABLE `fonction`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `menage`
--
ALTER TABLE `menage`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `option`
--
ALTER TABLE `option`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `paiement_detail`
--
ALTER TABLE `paiement_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paiement_id` (`paiement_id`),
  ADD KEY `menage_id` (`menage_id`),
  ADD KEY `eleve_id` (`eleve_id`),
  ADD KEY `tranche_id` (`tranche_id`);

--
-- Index pour la table `prestation`
--
ALTER TABLE `prestation`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Index pour la table `scolarite`
--
ALTER TABLE `scolarite`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `systeme`
--
ALTER TABLE `systeme`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tranche`
--
ALTER TABLE `tranche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frais_id` (`frais_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `affectation_prof_classe`
--
ALTER TABLE `affectation_prof_classe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `agent`
--
ALTER TABLE `agent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `annee_scolaire`
--
ALTER TABLE `annee_scolaire`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `annonce`
--
ALTER TABLE `annonce`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `assignment_corrections`
--
ALTER TABLE `assignment_corrections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `balance`
--
ALTER TABLE `balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1072;

--
-- AUTO_INCREMENT pour la table `classe`
--
ALTER TABLE `classe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `correction_devoir_ensignant`
--
ALTER TABLE `correction_devoir_ensignant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `cycle`
--
ALTER TABLE `cycle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `depenses`
--
ALTER TABLE `depenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT pour la table `eleve`
--
ALTER TABLE `eleve`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=391;

--
-- AUTO_INCREMENT pour la table `fonction`
--
ALTER TABLE `fonction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `grade`
--
ALTER TABLE `grade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `menage`
--
ALTER TABLE `menage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=257;

--
-- AUTO_INCREMENT pour la table `option`
--
ALTER TABLE `option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=857;

--
-- AUTO_INCREMENT pour la table `paiement_detail`
--
ALTER TABLE `paiement_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `prestation`
--
ALTER TABLE `prestation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `scolarite`
--
ALTER TABLE `scolarite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `systeme`
--
ALTER TABLE `systeme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `tranche`
--
ALTER TABLE `tranche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `paiement_detail`
--
ALTER TABLE `paiement_detail`
  ADD CONSTRAINT `paiement_detail_ibfk_1` FOREIGN KEY (`paiement_id`) REFERENCES `paiement` (`id`),
  ADD CONSTRAINT `paiement_detail_ibfk_2` FOREIGN KEY (`menage_id`) REFERENCES `menage` (`id`),
  ADD CONSTRAINT `paiement_detail_ibfk_3` FOREIGN KEY (`eleve_id`) REFERENCES `eleve` (`id`),
  ADD CONSTRAINT `paiement_detail_ibfk_4` FOREIGN KEY (`tranche_id`) REFERENCES `tranche` (`id`);

--
-- Contraintes pour la table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`);

--
-- Contraintes pour la table `tranche`
--
ALTER TABLE `tranche`
  ADD CONSTRAINT `tranche_ibfk_1` FOREIGN KEY (`frais_id`) REFERENCES `scolarite` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
