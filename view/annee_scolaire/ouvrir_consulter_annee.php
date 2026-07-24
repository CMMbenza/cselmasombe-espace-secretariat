<?php
session_start();
require_once('../../webapp/database/config.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$annee_id = (int)$_GET['id'];

// Vérifier le statut de l'année scolaire demandée
$query = "SELECT id, status, annee_scolaire FROM annee_scolaire WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $annee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "Année scolaire introuvable.";
    header("Location: index.php");
    exit;
}

$annee = mysqli_fetch_assoc($result);

if ($annee['status'] === 'encours') {
    // 🟢 L'année est active : On redirige vers votre espace de travail/gestion standard
    // (Ajustez le lien ci-dessous selon votre fichier de tableau de bord actif)
    header("Location: ../tableau_de_bord/index.php");
    exit;
} else {
    // 🔴 L'année est clôturée : On va chercher sa correspondance dans les archives
    $query_archive = "SELECT id FROM archive_session WHERE annee_scolaire_id = ? LIMIT 1";
    $stmt_arch = mysqli_prepare($con, $query_archive);
    mysqli_stmt_bind_param($stmt_arch, "i", $annee_id);
    mysqli_stmt_execute($stmt_arch);
    $res_arch = mysqli_stmt_get_result($stmt_arch);

    if ($row_arch = mysqli_fetch_assoc($res_arch)) {
        // Redirection vers la page de détails de l'archive que nous avons créée juste avant
        header("Location: ../archivage/details_archive.php?id=" . $row_arch['id']);
        exit;
    } else {
        $_SESSION['error'] = "Les registres physiques de cette archive n'ont pas pu être localisés.";
        header("Location: index.php");
        exit;
    }
}